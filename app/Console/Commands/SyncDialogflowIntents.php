<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Cache;

class SyncDialogflowIntents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dialogflow:sync-intents {--force : Force refresh cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all intents from Dialogflow and cache them for dynamic suggestions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Dialogflow intents synchronization...');

        try {
            $chatbotController = new ChatbotController();

            if ($this->option('force')) {
                $this->info('Force refreshing cache...');
                $chatbotController->refreshIntents();
            }

            $intents = $chatbotController->getAllIntents();

            if (empty($intents)) {
                $this->error('No intents found or failed to fetch intents from Dialogflow');
                return 1;
            }

            $this->info('Successfully synced ' . count($intents) . ' intents from Dialogflow');

            // Display intent summary
            $this->table(
                ['Intent Name', 'Keywords Count', 'Training Phrases'],
                collect($intents)->map(function ($intent, $name) {
                    return [
                        $name,
                        count($intent['keywords']),
                        count($intent['training_phrases'])
                    ];
                })->toArray()
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('Error syncing intents: ' . $e->getMessage());
            return 1;
        }
    }
}
