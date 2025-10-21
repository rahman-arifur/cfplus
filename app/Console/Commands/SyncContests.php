<?php

namespace App\Console\Commands;

use App\Jobs\SyncContestsFromCf;
use Illuminate\Console\Command;

class SyncContests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:sync-contests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync contests from Codeforces API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching contest sync job...');
        
        SyncContestsFromCf::dispatch();
        
        $this->info('Contest sync job dispatched successfully!');
        $this->comment('Run "php artisan queue:work" to process the job.');
        
        return Command::SUCCESS;
    }
}
