<?php

namespace App\Console\Commands;

use App\Services\MollieService;
use Illuminate\Console\Command;

class SyncMolliePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:mollie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize payments from Mollie API';

    /**
     * Execute the console command.
     */
    public function handle(MollieService $mollieService): int
    {
        $this->info('Starting Mollie payment sync...');

        try {
            $stats = $mollieService->syncPayments();

            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Payments found', $stats['found']],
                    ['Invoices created', $stats['created']],
                    ['Invoices updated', $stats['updated']],
                    ['Skipped', $stats['skipped']],
                    ['Failed', $stats['failed']],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
