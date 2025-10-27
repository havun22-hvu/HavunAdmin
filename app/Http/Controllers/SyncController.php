<?php

namespace App\Http\Controllers;

use App\Models\ApiSync;
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
            'lastMollieSync',
            'lastBunqSync',
            'lastGmailSync'
        ));
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
     * Sync Gmail invoices (placeholder for Phase 2)
     */
    public function syncGmail()
    {
        return redirect()->back()->with('info', 'Gmail integratie komt binnenkort!');
    }

    /**
     * Show sync details
     */
    public function show(ApiSync $sync)
    {
        return view('sync.show', compact('sync'));
    }
}
