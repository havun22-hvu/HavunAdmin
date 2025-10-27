<?php

namespace App\Console\Commands;

use App\Services\HerdenkingsportaalService;
use Illuminate\Console\Command;

class SyncHerdenkingsportaalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:herdenkingsportaal {--days=30 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize invoices from Herdenkingsportaal database';

    /**
     * Execute the console command.
     */
    public function handle(HerdenkingsportaalService $service): int
    {
        $this->info('Starting Herdenkingsportaal invoice sync...');

        // Test connection first
        if (!$service->testConnection()) {
            $this->error('Failed to connect to Herdenkingsportaal database.');
            $this->line('Please check your HERDENKINGSPORTAAL_DB_DATABASE configuration in .env');
            return self::FAILURE;
        }

        $this->info('✓ Database connection successful');

        try {
            $lookbackDays = (int) $this->option('days');
            $stats = $service->syncInvoices($lookbackDays);

            $this->newLine();
            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Invoices found', $stats['found']],
                    ['Invoices created', $stats['created']],
                    ['Invoices updated', $stats['updated']],
                    ['Skipped', $stats['skipped']],
                    ['Failed', $stats['failed']],
                ]
            );

            if ($stats['failed'] > 0) {
                $this->warn("⚠ {$stats['failed']} invoices failed to process. Check logs for details.");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
