<?php

namespace App\Console\Commands;

use App\Models\CfAccount;
use App\Jobs\SyncUserSubmissions;
use Illuminate\Console\Command;

class SyncSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:sync-submissions {--handle= : Sync submissions for specific CF handle} {--all : Sync submissions for all CF accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user submissions from Codeforces to update problem solved status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $handle = $this->option('handle');
        $all = $this->option('all');

        if ($all) {
            $this->info('Syncing submissions for all CF accounts...');
            
            $cfAccounts = CfAccount::all();
            
            if ($cfAccounts->isEmpty()) {
                $this->warn('No CF accounts found.');
                return;
            }

            $this->info("Found {$cfAccounts->count()} CF accounts.");
            
            foreach ($cfAccounts as $cfAccount) {
                $this->info("Dispatching sync job for: {$cfAccount->handle}");
                SyncUserSubmissions::dispatch($cfAccount->id);
            }

            $this->info('Sync jobs dispatched successfully!');
            
        } elseif ($handle) {
            $this->info("Syncing submissions for handle: {$handle}");
            
            $cfAccount = CfAccount::where('handle', $handle)->first();
            
            if (!$cfAccount) {
                $this->error("CF account not found for handle: {$handle}");
                return;
            }

            SyncUserSubmissions::dispatch($cfAccount->id);
            $this->info('Sync job dispatched successfully!');
            
        } else {
            $this->error('Please specify either --handle=<handle> or --all');
            return;
        }
    }
}

