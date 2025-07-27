<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatbotAnalyticsService;
use App\Http\Controllers\ChatbotController;

class ChatbotMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:maintenance 
                            {--sync-intents : Sync intents from Dialogflow}
                            {--clean-data=90 : Clean data older than X days}
                            {--optimize : Optimize suggestion performance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform chatbot maintenance tasks including data cleanup and optimization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting chatbot maintenance...');

        try {
            // Sync intents if requested
            if ($this->option('sync-intents')) {
                $this->info('Syncing intents from Dialogflow...');
                $chatbotController = new ChatbotController();
                $chatbotController->refreshIntents();
                $this->info('✓ Intents synced successfully');
            }

            // Clean old data
            $cleanDays = (int) $this->option('clean-data');
            if ($cleanDays > 0) {
                $this->info("Cleaning data older than {$cleanDays} days...");
                ChatbotAnalyticsService::cleanOldData($cleanDays);
                $this->info('✓ Old data cleaned successfully');
            }

            // Optimize suggestions
            if ($this->option('optimize')) {
                $this->info('Optimizing suggestion performance...');
                $this->optimizeSuggestions();
                $this->info('✓ Suggestions optimized successfully');
            }

            // Display maintenance summary
            $this->displayMaintenanceSummary();

            $this->info('Chatbot maintenance completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Maintenance failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Optimize suggestion performance
     */
    private function optimizeSuggestions()
    {
        // Get analytics data for optimization
        $dashboardData = ChatbotAnalyticsService::getDashboardData(30);

        // Log optimization insights
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Interactions', $dashboardData['total_interactions']],
                ['Unique Sessions', $dashboardData['unique_sessions']],
                ['Avg Session Duration', round($dashboardData['avg_session_duration'], 2) . ' seconds'],
                ['Conversion Rate', round($dashboardData['conversion_rate'], 2) . '%']
            ]
        );

        // Show top performing suggestions
        if (!empty($dashboardData['best_suggestions'])) {
            $this->info('Top Performing Suggestions:');
            $suggestionData = [];
            foreach ($dashboardData['best_suggestions']->take(5) as $suggestion) {
                $suggestionData[] = [
                    $suggestion->suggestion_text,
                    $suggestion->context_intent,
                    round($suggestion->click_rate, 1) . '%'
                ];
            }

            $this->table(['Suggestion', 'Context', 'Click Rate'], $suggestionData);
        }

        // Cache optimization results
        \Illuminate\Support\Facades\Cache::put('chatbot_optimization_data', $dashboardData, 3600);
    }

    /**
     * Display maintenance summary
     */
    private function displayMaintenanceSummary()
    {
        $this->info('=== Maintenance Summary ===');

        // Get current statistics
        $stats = [
            'Active Sessions' => \App\Models\ChatbotSession::getActiveSessionsCount(),
            'Cache Status' => \Illuminate\Support\Facades\Cache::has('dialogflow_intents_websitebot-etqi') ? 'Active' : 'Empty',
            'Database Size' => $this->getDatabaseSize(),
            'Last Optimization' => \Illuminate\Support\Facades\Cache::has('chatbot_optimization_data') ? 'Recent' : 'Needed'
        ];

        foreach ($stats as $key => $value) {
            $this->line("<info>{$key}:</info> {$value}");
        }
    }

    /**
     * Get estimated database size for chatbot tables
     */
    private function getDatabaseSize()
    {
        try {
            $interactions = \App\Models\ChatbotInteraction::count();
            $sessions = \App\Models\ChatbotSession::count();
            $intentUsage = \App\Models\ChatbotIntentUsage::count();
            $suggestions = \App\Models\ChatbotSuggestionAnalytics::count();

            $total = $interactions + $sessions + $intentUsage + $suggestions;
            return number_format($total) . ' records';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}
