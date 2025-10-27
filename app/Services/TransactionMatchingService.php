<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class TransactionMatchingService
{
    /**
     * Find and link duplicate transactions based on memorial_reference
     *
     * @param Invoice $newInvoice The newly imported invoice to check for duplicates
     * @return array ['matched' => bool, 'master' => Invoice|null, 'duplicates' => Collection]
     */
    public function findAndLinkDuplicates(Invoice $newInvoice): array
    {
        // Can only match if we have a memorial reference
        if (!$newInvoice->memorial_reference) {
            return [
                'matched' => false,
                'master' => null,
                'duplicates' => collect([]),
            ];
        }

        // Find all invoices with the same memorial_reference
        $matches = Invoice::where('memorial_reference', $newInvoice->memorial_reference)
            ->where('id', '!=', $newInvoice->id)
            ->get();

        if ($matches->isEmpty()) {
            // No duplicates found - this is the first/master record
            return [
                'matched' => false,
                'master' => null,
                'duplicates' => collect([]),
            ];
        }

        // Found duplicates - determine which is the master
        $master = $this->determineMaster($newInvoice, $matches);

        // Link all non-master records to the master
        $duplicates = collect([$newInvoice])->merge($matches)->reject(fn($inv) => $inv->id === $master->id);

        foreach ($duplicates as $duplicate) {
            $duplicate->update([
                'parent_invoice_id' => $master->id,
                'is_duplicate' => true,
                'match_confidence' => 100, // 100% confidence because memorial_reference is unique
                'match_notes' => "Automatically linked to master invoice #{$master->invoice_number} via memorial reference: {$newInvoice->memorial_reference}",
            ]);
        }

        Log::info('Transaction duplicates linked', [
            'memorial_reference' => $newInvoice->memorial_reference,
            'master_id' => $master->id,
            'duplicate_ids' => $duplicates->pluck('id')->toArray(),
        ]);

        return [
            'matched' => true,
            'master' => $master->fresh(),
            'duplicates' => $duplicates->fresh(),
        ];
    }

    /**
     * Determine which invoice should be the master record
     *
     * Priority:
     * 1. Herdenkingsportaal source (most authoritative)
     * 2. Gmail (has full details)
     * 3. Mollie (has payment info)
     * 4. Bunq (just bank transaction)
     * 5. Manual entry
     */
    protected function determineMaster(Invoice $newInvoice, $existingMatches): Invoice
    {
        $allInvoices = collect([$newInvoice])->merge($existingMatches);

        $sourcePriority = [
            'herdenkingsportaal' => 1,
            'gmail' => 2,
            'mollie' => 3,
            'bunq' => 4,
            'manual' => 5,
        ];

        // Sort by source priority (lower number = higher priority)
        // Then by created_at (earlier = higher priority)
        $sorted = $allInvoices->sortBy(function ($invoice) use ($sourcePriority) {
            return [
                $sourcePriority[$invoice->source] ?? 99,
                $invoice->created_at->timestamp,
            ];
        });

        return $sorted->first();
    }

    /**
     * Extract memorial reference from various sources
     *
     * @param string $text Text to extract from (email body, transaction description, etc.)
     * @return string|null The 12-character memorial reference if found
     */
    public function extractMemorialReference(string $text): ?string
    {
        // Pattern: 12 alphanumeric characters (first 12 chars of UUID without hyphens)
        // Common patterns:
        // - "Memorial: 1a2b3c4d5e6f"
        // - "Ref: 1a2b3c4d5e6f"
        // - "Betalingskenmerk: 1a2b3c4d5e6f"
        // - Just the reference standalone: "1a2b3c4d5e6f"

        // Remove hyphens from potential UUIDs (in case they send full UUID)
        $cleanText = str_replace('-', '', $text);

        // Try to find 12 consecutive alphanumeric characters
        if (preg_match('/\b([a-f0-9]{12})\b/i', $cleanText, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    /**
     * Get all master invoices (non-duplicates or parents of duplicates)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMasterInvoices()
    {
        return Invoice::where('is_duplicate', false)
            ->with(['duplicates' => function ($query) {
                $query->where('is_duplicate', true);
            }])
            ->get();
    }

    /**
     * Get duplicate groups - invoices grouped by their memorial_reference
     *
     * @return array
     */
    public function getDuplicateGroups(): array
    {
        $invoices = Invoice::whereNotNull('memorial_reference')
            ->get()
            ->groupBy('memorial_reference');

        return $invoices
            ->filter(fn($group) => $group->count() > 1) // Only groups with 2+ invoices
            ->map(function ($group) {
                $master = $group->firstWhere('is_duplicate', false);
                $duplicates = $group->where('is_duplicate', true);

                return [
                    'memorial_reference' => $group->first()->memorial_reference,
                    'master' => $master,
                    'duplicates' => $duplicates,
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();
    }
}
