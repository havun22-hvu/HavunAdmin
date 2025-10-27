<?php

namespace App\Http\Controllers;

use App\Models\ApiSync;
use App\Services\GmailService;
use App\Services\HerdenkingsportaalService;
use App\Services\MollieService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Show sync dashboard
     */
    public function index()
    {
        $syncs = ApiSync::latest('started_at')
            ->paginate(20);

        $lastHerdenkingsportaalSync = ApiSync::where('service', 'herdenkingsportaal')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        $lastMollieSync = ApiSync::where('service', 'mollie')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        $lastBunqSync = ApiSync::where('service', 'bunq')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        $lastGmailSync = ApiSync::where('service', 'gmail')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        return view('sync.index', compact(
            'syncs',
            'lastHerdenkingsportaalSync',
            'lastMollieSync',
            'lastBunqSync',
            'lastGmailSync'
        ));
    }

    /**
     * Sync Herdenkingsportaal invoices
     */
    public function syncHerdenkingsportaal(HerdenkingsportaalService $service)
    {
        try {
            $stats = $service->syncInvoices();

            // Check if sync was skipped due to environment
            if (isset($stats['message'])) {
                return redirect()->back()->with('warning',
                    $stats['message'] . ' (Environment: ' . app()->environment() . ')'
                );
            }

            return redirect()->back()->with('success', sprintf(
                'Herdenkingsportaal sync voltooid! Gevonden: %d, Aangemaakt: %d, Bijgewerkt: %d',
                $stats['found'],
                $stats['created'],
                $stats['updated']
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Herdenkingsportaal sync mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Sync Mollie payments
     */
    public function syncMollie(MollieService $mollieService)
    {
        try {
            $stats = $mollieService->syncPayments();

            return redirect()->back()->with('success', sprintf(
                'Mollie sync voltooid! Gevonden: %d, Aangemaakt: %d, Bijgewerkt: %d',
                $stats['found'],
                $stats['created'],
                $stats['updated']
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Mollie sync mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Sync Bunq transactions (placeholder for Phase 2)
     */
    public function syncBunq()
    {
        return redirect()->back()->with('info', 'Bunq integratie komt binnenkort!');
    }

    /**
     * Gmail OAuth - Redirect to Google for authorization
     */
    public function gmailAuth(GmailService $gmailService)
    {
        $authUrl = $gmailService->getAuthUrl();
        return redirect($authUrl);
    }

    /**
     * Gmail OAuth - Handle callback from Google
     */
    public function gmailCallback(Request $request, GmailService $gmailService)
    {
        try {
            $code = $request->input('code');

            if (!$code) {
                return redirect()->route('sync.index')->with('error', 'Gmail authenticatie geannuleerd.');
            }

            $gmailService->handleCallback($code);

            return redirect()->route('sync.index')->with('success', 'Gmail succesvol gekoppeld! Je kunt nu emails scannen.');
        } catch (\Exception $e) {
            return redirect()->route('sync.index')->with('error', 'Gmail authenticatie mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Sync Gmail invoices
     */
    public function syncGmail(Request $request, GmailService $gmailService)
    {
        try {
            // Check if authenticated
            if (!$gmailService->isAuthenticated()) {
                return redirect()->back()->with('error', 'Gmail is nog niet gekoppeld. Klik eerst op "Gmail Koppelen".');
            }

            // Get type (expense or income)
            $type = $request->input('type', 'expense');

            // Get last sync date to only scan new emails (filtered by type)
            $lastSyncDate = $gmailService->getLastSyncDate($type);

            // Record sync start (we'll update status to success/failed later)
            $sync = ApiSync::create([
                'service' => 'gmail',
                'type' => $type === 'income' ? 'invoices' : 'expenses',
                'status' => 'partial', // Will be updated to 'success' or 'failed' later
                'started_at' => now(),
            ]);

            // Scan for invoices
            $invoices = $gmailService->scanForInvoices($lastSyncDate, $type);

            // Update sync record
            $sync->update([
                'status' => 'success',
                'completed_at' => now(),
                'items_found' => count($invoices),
                'items_processed' => count($invoices),
                'items_created' => count($invoices),
                'items_updated' => 0,
                'items_failed' => 0,
                'metadata' => ['invoices' => $invoices],
            ]);

            if (count($invoices) === 0) {
                $typeLabel = $type === 'income' ? 'inkomsten' : 'uitgaven';
                return redirect()->back()->with('info', "Geen nieuwe {$typeLabel} gevonden in Gmail.");
            }

            $typeLabel = $type === 'income' ? 'inkomsten' : 'uitgaven';
            return redirect()->back()->with('success', sprintf(
                'Gmail sync voltooid! %d nieuwe %s gevonden en als concept opgeslagen.',
                count($invoices),
                $typeLabel
            ));
        } catch (\Exception $e) {
            // Update sync record with error
            if (isset($sync)) {
                $sync->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }

            return redirect()->back()->with('error', 'Gmail sync mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Show sync details
     */
    public function show(ApiSync $sync)
    {
        return view('sync.show', compact('sync'));
    }
}
