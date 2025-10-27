<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\OAuthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes;

    public function __construct()
    {
        $this->clientId = config('services.gmail.client_id');
        $this->clientSecret = config('services.gmail.client_secret');
        $this->redirectUri = config('services.gmail.redirect_uri');
        $this->scopes = [
            'https://www.googleapis.com/auth/gmail.readonly',
            'https://www.googleapis.com/auth/gmail.modify',
        ];
    }

    /**
     * Get HTTP client with SSL verification disabled for local development
     */
    protected function http()
    {
        $http = Http::timeout(30);

        // Disable SSL verification for local development (Avast firewall issues)
        if (app()->environment('local')) {
            $http = $http->withOptions(['verify' => false]);
        }

        return $http;
    }

    /**
     * Get the OAuth authorization URL
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function handleCallback(string $code): array
    {
        $response = $this->http()->asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange code for token: ' . $response->body());
        }

        $data = $response->json();

        // Store tokens in database
        OAuthToken::updateOrCreate(
            ['service' => 'gmail'],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($data['expires_in']),
            ]
        );

        return $data;
    }

    /**
     * Get valid access token (refresh if expired)
     */
    protected function getAccessToken(): string
    {
        $token = OAuthToken::where('service', 'gmail')->first();

        if (!$token) {
            throw new \Exception('No Gmail OAuth token found. Please authenticate first.');
        }

        // Check if token is expired
        if ($token->expires_at && $token->expires_at->isPast()) {
            $this->refreshAccessToken($token);
            $token->refresh();
        }

        return $token->access_token;
    }

    /**
     * Refresh the access token using refresh token
     */
    protected function refreshAccessToken(OAuthToken $token): void
    {
        if (!$token->refresh_token) {
            throw new \Exception('No refresh token available. Please re-authenticate.');
        }

        $response = $this->http()->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        $data = $response->json();

        $token->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }

    /**
     * Scan Gmail for invoice emails with attachments
     *
     * @param string|null $afterDate Only scan emails after this date
     * @param string $type 'expense' or 'income'
     */
    public function scanForInvoices(?string $afterDate = null, string $type = 'expense'): array
    {
        $accessToken = $this->getAccessToken();

        // Build search query based on type
        // Gmail nested labels use hyphen in search (2025/expenses becomes 2025-expenses)
        if ($type === 'income') {
            // Search in receive folders (inkomsten)
            $query = 'label:2025-receive';
        } else {
            // Search in expenses folders (uitgaven)
            $query = 'label:2025-expenses';
        }

        if ($afterDate) {
            $query .= ' after:' . date('Y/m/d', strtotime($afterDate));
        }

        // Search for messages
        $response = $this->http()->withToken($accessToken)->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
            'q' => $query,
            'maxResults' => 50,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to search Gmail: ' . $response->body());
        }

        $data = $response->json();
        $messages = $data['messages'] ?? [];

        // Log the query and result count for debugging
        \Log::info('Gmail search query: ' . $query);
        \Log::info('Gmail search results: ' . count($messages) . ' messages found');

        $invoices = [];

        foreach ($messages as $message) {
            try {
                $invoice = $this->processMessage($message['id'], $accessToken, $type);
                if ($invoice) {
                    $invoices[] = $invoice;
                }
            } catch (\Exception $e) {
                Log::error('Failed to process Gmail message: ' . $e->getMessage(), [
                    'message_id' => $message['id'],
                ]);
            }
        }

        return $invoices;
    }

    /**
     * Process a single Gmail message
     */
    protected function processMessage(string $messageId, string $accessToken, string $type = 'expense'): ?array
    {
        // Get full message details
        $response = $this->http()->withToken($accessToken)->get(
            "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}",
            ['format' => 'full']
        );

        if (!$response->successful()) {
            return null;
        }

        $message = $response->json();

        // Extract email details
        $headers = $message['payload']['headers'] ?? [];
        $subject = $this->getHeader($headers, 'Subject');
        $from = $this->getHeader($headers, 'From');
        $date = $this->getHeader($headers, 'Date');

        // Extract email body for memorial reference extraction
        $emailBody = $this->getMessageBody($message['payload']);

        // Find PDF attachments
        $attachments = $this->findPdfAttachments($message['payload']);

        $filename = null;
        if (!empty($attachments)) {
            // Download first PDF attachment if available
            $attachment = $attachments[0];
            $filename = $this->downloadAttachment(
                $messageId,
                $attachment['attachmentId'],
                $attachment['filename'],
                $accessToken
            );
        }
        // Note: If no PDF, we still import the email (filename will be null)

        // Check if we already imported this email
        $existing = Invoice::where('gmail_message_id', $messageId)->first();
        if ($existing) {
            return null; // Already imported
        }

        // Extract memorial reference from subject and body
        $matchingService = app(\App\Services\TransactionMatchingService::class);
        $memorialRef = $matchingService->extractMemorialReference($subject . ' ' . $emailBody);

        // Create invoice record (as draft)
        $invoice = Invoice::create([
            'invoice_number' => 'GMAIL-' . substr($messageId, 0, 10),
            'type' => $type, // 'expense' or 'income'
            'user_id' => auth()->id(),
            'invoice_date' => date('Y-m-d', strtotime($date)),
            'description' => $subject,
            'subtotal' => 0, // User must fill in
            'vat_amount' => 0,
            'total' => 0,
            'status' => 'draft',
            'source' => 'gmail',
            'gmail_message_id' => $messageId,
            'memorial_reference' => $memorialRef,
            'file_path' => $filename,
            'notes' => "From: {$from}\nSubject: {$subject}",
        ]);

        // Check for and link duplicates
        if ($memorialRef) {
            $matchingService->findAndLinkDuplicates($invoice);
        }

        return [
            'id' => $invoice->id,
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
            'filename' => $attachment['filename'],
        ];
    }

    /**
     * Find PDF attachments in message payload
     */
    protected function findPdfAttachments(array $payload): array
    {
        $attachments = [];

        // Check if payload has parts
        if (isset($payload['parts'])) {
            foreach ($payload['parts'] as $part) {
                // Recursive check for nested parts
                if (isset($part['parts'])) {
                    $attachments = array_merge($attachments, $this->findPdfAttachments($part));
                }

                // Check if this part is a PDF attachment
                if (isset($part['filename']) &&
                    !empty($part['filename']) &&
                    isset($part['body']['attachmentId']) &&
                    str_ends_with(strtolower($part['filename']), '.pdf')) {

                    $attachments[] = [
                        'filename' => $part['filename'],
                        'attachmentId' => $part['body']['attachmentId'],
                        'mimeType' => $part['mimeType'] ?? 'application/pdf',
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Download attachment from Gmail
     */
    protected function downloadAttachment(
        string $messageId,
        string $attachmentId,
        string $filename,
        string $accessToken
    ): string {
        $response = $this->http()->withToken($accessToken)->get(
            "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}/attachments/{$attachmentId}"
        );

        if (!$response->successful()) {
            throw new \Exception('Failed to download attachment');
        }

        $data = $response->json();

        // Decode base64url data
        $fileData = str_replace(['-', '_'], ['+', '/'], $data['data']);
        $fileData = base64_decode($fileData);

        // Generate unique filename
        $uniqueFilename = date('Y-m-d') . '_' . uniqid() . '_' . $filename;
        $path = 'invoices/gmail/' . $uniqueFilename;

        // Save to storage
        Storage::disk('local')->put($path, $fileData);

        return $path;
    }

    /**
     * Get header value from headers array
     */
    protected function getHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if (strcasecmp($header['name'], $name) === 0) {
                return $header['value'];
            }
        }
        return null;
    }

    /**
     * Extract message body from payload
     */
    protected function getMessageBody(array $payload): string
    {
        $body = '';

        // Check if body is directly in payload
        if (isset($payload['body']['data'])) {
            $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload['body']['data']));
        }

        // Check parts for multipart messages
        if (isset($payload['parts'])) {
            foreach ($payload['parts'] as $part) {
                if (isset($part['mimeType']) && str_contains($part['mimeType'], 'text')) {
                    if (isset($part['body']['data'])) {
                        $body .= ' ' . base64_decode(str_replace(['-', '_'], ['+', '/'], $part['body']['data']));
                    }
                }

                // Recursively check nested parts
                if (isset($part['parts'])) {
                    $body .= ' ' . $this->getMessageBody($part);
                }
            }
        }

        return strip_tags($body); // Remove HTML tags
    }

    /**
     * Check if Gmail is authenticated
     */
    public function isAuthenticated(): bool
    {
        $token = OAuthToken::where('service', 'gmail')->first();
        return $token && $token->access_token;
    }

    /**
     * Get last sync timestamp for a specific type
     * Returns the invoice_date (email date) of the most recent Gmail import of that type
     * This ensures we only import emails with dates newer than what we already have
     *
     * @param string $type 'expense' or 'income'
     */
    public function getLastSyncDate(string $type = null): ?string
    {
        $query = Invoice::where('source', 'gmail');

        // Filter by type if specified
        if ($type) {
            $query->where('type', $type);
        }

        $lastInvoice = $query->orderBy('invoice_date', 'desc')->first();

        // Return the invoice_date (email date) not created_at (import date)
        // This way we only scan for emails newer than the newest email we already imported
        return $lastInvoice ? $lastInvoice->invoice_date->toDateTimeString() : null;
    }
}
