<?php

namespace App\Services;

use App\Models\ApiSync;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mollie\Api\MollieApiClient;

class MollieService
{
    protected MollieApiClient $mollie;
    protected array $config;

    public function __construct()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey(config('mollie.key'));
        $this->config = config('mollie.sync');
    }

    /**
     * Sync payments from Mollie
     */
    public function syncPayments(): array
    {
        $sync = ApiSync::create([
            'service' => 'mollie',
            'type' => 'payments',
            'status' => 'success',
            'started_at' => now(),
        ]);

        try {
            $fromDate = Carbon::now()->subDays($this->config['lookback_days']);
            $stats = [
                'found' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];

            // Fetch payments from Mollie
            $payments = $this->mollie->payments->page(null, $this->config['limit']);

            foreach ($payments as $payment) {
                $stats['found']++;

                // Skip if payment is not paid
                if (!$payment->isPaid()) {
                    $stats['skipped']++;
                    continue;
                }

                // Skip if payment is too old
                if ($payment->paidAt && Carbon::parse($payment->paidAt)->lt($fromDate)) {
                    $stats['skipped']++;
                    continue;
                }

                try {
                    $result = $this->processPayment($payment);
                    if ($result === 'created') {
                        $stats['created']++;
                    } elseif ($result === 'updated') {
                        $stats['updated']++;
                    } else {
                        $stats['skipped']++;
                    }
                } catch (\Exception $e) {
                    $stats['failed']++;
                    Log::error("Mollie payment processing failed: {$payment->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update sync record
            $sync->update([
                'completed_at' => now(),
                'items_found' => $stats['found'],
                'items_processed' => $stats['found'] - $stats['failed'],
                'items_created' => $stats['created'],
                'items_updated' => $stats['updated'],
                'items_failed' => $stats['failed'],
                'metadata' => $stats,
            ]);

            return $stats;

        } catch (\Exception $e) {
            $sync->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a single payment
     */
    protected function processPayment($payment): string
    {
        // Check if invoice already exists
        $invoice = Invoice::where('mollie_payment_id', $payment->id)->first();

        if ($invoice) {
            // Update existing invoice if needed
            if ($invoice->status !== 'paid') {
                $invoice->update([
                    'status' => 'paid',
                    'payment_date' => $payment->paidAt,
                ]);
                return 'updated';
            }
            return 'skipped';
        }

        // Determine project based on description
        $projectId = $this->determineProject($payment->description);

        // Create new invoice
        Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'type' => 'income',
            'project_id' => $projectId,
            'customer_id' => null, // Anonymous for now
            'invoice_date' => $payment->paidAt ? Carbon::parse($payment->paidAt) : now(),
            'payment_date' => $payment->paidAt ? Carbon::parse($payment->paidAt) : now(),
            'description' => $payment->description ?? 'Mollie payment',
            'subtotal' => floatval($payment->amount->value),
            'vat_amount' => 0,
            'vat_percentage' => 0,
            'total' => floatval($payment->amount->value),
            'status' => 'paid',
            'payment_method' => $payment->method ?? 'mollie',
            'source' => 'mollie',
            'mollie_payment_id' => $payment->id,
        ]);

        return 'created';
    }

    /**
     * Determine project based on payment description
     */
    protected function determineProject(?string $description): ?int
    {
        if (!$description) {
            return null;
        }

        $description = strtolower($description);
        $mapping = config('mollie.project_mapping', []);

        foreach ($mapping as $keyword => $projectId) {
            if (str_contains($description, $keyword)) {
                return $projectId;
            }
        }

        return null;
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = Invoice::income()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $number = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $number);
    }

    /**
     * Get sync history
     */
    public function getSyncHistory(int $limit = 10)
    {
        return ApiSync::where('service', 'mollie')
            ->latest('started_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get last sync info
     */
    public function getLastSync(): ?ApiSync
    {
        return ApiSync::where('service', 'mollie')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();
    }
}
