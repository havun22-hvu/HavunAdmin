<?php

namespace App\Services;

use App\Models\ApiSync;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HerdenkingsportaalService
{
    protected string $connection = 'herdenkingsportaal';

    /**
     * Sync invoices from Herdenkingsportaal database
     */
    public function syncInvoices(int $lookbackDays = 30): array
    {
        $sync = ApiSync::create([
            'service' => 'herdenkingsportaal',
            'type' => 'invoices',
            'status' => 'success',
            'started_at' => now(),
        ]);

        try {
            $fromDate = Carbon::now()->subDays($lookbackDays);
            $stats = [
                'found' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];

            // Fetch paid invoices from Herdenkingsportaal
            $invoices = $this->getInvoices($fromDate);
            $stats['found'] = $invoices->count();

            foreach ($invoices as $invoice) {
                try {
                    $result = $this->processInvoice($invoice);
                    if ($result === 'created') {
                        $stats['created']++;
                    } elseif ($result === 'updated') {
                        $stats['updated']++;
                    } else {
                        $stats['skipped']++;
                    }
                } catch (\Exception $e) {
                    $stats['failed']++;
                    Log::error("Herdenkingsportaal invoice processing failed: {$invoice->invoice_number}", [
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
     * Get invoices from Herdenkingsportaal database
     */
    protected function getInvoices(?Carbon $fromDate = null)
    {
        $query = DB::connection($this->connection)
            ->table('invoices')
            ->join('payment_transactions', 'invoices.payment_transaction_id', '=', 'payment_transactions.id')
            ->leftJoin('memorials', 'invoices.memorial_id', '=', 'memorials.id')
            ->select([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.customer_name',
                'invoices.customer_email',
                'invoices.package_type',
                'invoices.description',
                'invoices.amount_excl_vat',
                'invoices.vat_rate',
                'invoices.vat_amount',
                'invoices.amount_incl_vat',
                'payment_transactions.mollie_payment_id',
                'payment_transactions.uuid as payment_uuid',
                'payment_transactions.status as payment_status',
                'payment_transactions.paid_at',
                'payment_transactions.payment_method',
                'memorials.full_name as memorial_name',
                'memorials.uuid as memorial_uuid',
            ])
            ->where('payment_transactions.status', 'paid');

        if ($fromDate) {
            $query->where('invoices.invoice_date', '>=', $fromDate->format('Y-m-d'));
        }

        return $query->orderBy('invoices.invoice_date', 'desc')->get();
    }

    /**
     * Process a single invoice from Herdenkingsportaal
     */
    protected function processInvoice($invoice): string
    {
        // Check if invoice already exists (by Herdenkingsportaal invoice number)
        $existingInvoice = Invoice::where('source', 'herdenkingsportaal')
            ->where('external_reference', $invoice->invoice_number)
            ->first();

        // Determine project (always Herdenkingsportaal project)
        $project = Project::where('slug', 'herdenkingsportaal')->first();

        $invoiceData = [
            'invoice_number' => $this->generateInvoiceNumber(),
            'type' => 'income',
            'project_id' => $project?->id,
            'customer_id' => null, // No customer records in HavunAdmin
            'invoice_date' => $invoice->invoice_date,
            'payment_date' => $invoice->paid_at ? Carbon::parse($invoice->paid_at) : null,
            'description' => $this->buildDescription($invoice),
            'subtotal' => floatval($invoice->amount_excl_vat),
            'vat_amount' => floatval($invoice->vat_amount),
            'vat_percentage' => floatval($invoice->vat_rate) * 100, // Convert 0.2100 to 21.00
            'total' => floatval($invoice->amount_incl_vat),
            'status' => 'paid',
            'payment_method' => $invoice->payment_method ?? 'mollie',
            'source' => 'herdenkingsportaal',
            'external_reference' => $invoice->invoice_number,
            'mollie_payment_id' => $invoice->mollie_payment_id,
            'metadata' => [
                'payment_uuid' => $invoice->payment_uuid,
                'memorial_name' => $invoice->memorial_name,
                'memorial_uuid' => $invoice->memorial_uuid,
                'package_type' => $invoice->package_type,
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
            ],
        ];

        if ($existingInvoice) {
            // Update existing invoice if payment status changed
            if ($existingInvoice->payment_date === null && $invoiceData['payment_date']) {
                $existingInvoice->update($invoiceData);
                return 'updated';
            }
            return 'skipped';
        }

        // Create new invoice
        Invoice::create($invoiceData);
        return 'created';
    }

    /**
     * Build invoice description from Herdenkingsportaal data
     */
    protected function buildDescription($invoice): string
    {
        $parts = [];

        if ($invoice->memorial_name) {
            $parts[] = "Memorial: {$invoice->memorial_name}";
        }

        if ($invoice->customer_name) {
            $parts[] = "Klant: {$invoice->customer_name}";
        }

        if ($invoice->package_type) {
            $packageName = match($invoice->package_type) {
                'memorial_monument' => 'Premium Monument',
                'memorial_website' => 'Premium+ Website',
                default => ucfirst(str_replace('_', ' ', $invoice->package_type))
            };
            $parts[] = "Pakket: {$packageName}";
        }

        return implode(' | ', $parts) ?: 'Herdenkingsportaal factuur';
    }

    /**
     * Generate unique invoice number for HavunAdmin
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
     * Get total revenue by month for a specific year
     */
    public function getTotalRevenueByMonth(int $year)
    {
        return DB::connection($this->connection)
            ->table('invoices')
            ->join('payment_transactions', 'invoices.payment_transaction_id', '=', 'payment_transactions.id')
            ->selectRaw('
                CAST(strftime("%m", invoice_date) AS INTEGER) as month,
                COUNT(*) as invoice_count,
                SUM(amount_excl_vat) as total_excl_vat,
                SUM(vat_amount) as total_vat,
                SUM(amount_incl_vat) as total_incl_vat
            ')
            ->whereRaw('strftime("%Y", invoices.invoice_date) = ?', [$year])
            ->where('payment_transactions.status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get sync history
     */
    public function getSyncHistory(int $limit = 10)
    {
        return ApiSync::where('service', 'herdenkingsportaal')
            ->latest('started_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get last successful sync
     */
    public function getLastSync(): ?ApiSync
    {
        return ApiSync::where('service', 'herdenkingsportaal')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Test database connection
     */
    public function testConnection(): bool
    {
        try {
            DB::connection($this->connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Herdenkingsportaal database connection failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
