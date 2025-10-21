<?php

namespace App\Console\Commands;

use App\Models\Problem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixProblemTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:problem-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert comma-separated problem tags to JSON array format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding problems with comma-separated tags...');
        
        // Get problems where tags don't start with '[' (not JSON)
        $problems = DB::table('problems')
            ->whereNotNull('tags')
            ->whereRaw("tags NOT LIKE '[%'")
            ->get();
        
        if ($problems->isEmpty()) {
            $this->info('No problems found with comma-separated tags.');
            return;
        }
        
        $this->info("Found {$problems->count()} problems to fix.");
        
        $bar = $this->output->createProgressBar($problems->count());
        $bar->start();
        
        $fixed = 0;
        
        foreach ($problems as $problem) {
            try {
                // Convert comma-separated string to array
                $tagsString = trim($problem->tags, '"'); // Remove outer quotes if any
                $tagsArray = !empty($tagsString) 
                    ? array_map('trim', explode(',', $tagsString))
                    : [];
                
                // Update with JSON format
                DB::table('problems')
                    ->where('id', $problem->id)
                    ->update(['tags' => json_encode($tagsArray)]);
                
                $fixed++;
            } catch (\Exception $e) {
                $this->error("\nError fixing problem ID {$problem->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        
        $this->newLine(2);
        $this->info("Successfully fixed {$fixed} problems!");
    }
}

