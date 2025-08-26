<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FirestoreService;
use Symfony\Component\DomCrawler\Crawler;

class ChatbotController extends Controller
{
    private $firestoreService;

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    public function sendMessage(Request $request)
    {
        // Generate unique request ID untuk tracking
        $requestId = 'REQ_' . Str::uuid()->toString();
        $sessionId = $request->input('session_id', session()->getId());
        $userId = auth()->id();
        $message = $request->input('message');
        $messageLength = strlen($message);
        
        // Performance tracking variables
        $startOverall = microtime(true);
        $memoryStart = memory_get_usage(true);
        $memoryPeakStart = memory_get_peak_usage(true);
        
        // System metrics
        $activeUsers = $this->getActiveUserCount();
        $serverLoad = sys_getloadavg()[0] ?? 0;
        
        // Log request initiation
        Log::info("PERF_START", [
            'request_id' => $requestId,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'message_length' => $messageLength,
            'active_users' => $activeUsers,
            'server_load' => round($serverLoad, 2),
            'memory_start_mb' => round($memoryStart / 1024 / 1024, 2),
            'timestamp' => now()->toISOString()
        ]);

        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';
        $layerResults = [];

        try {
            // === LAYER 1: DIALOGFLOW ===
            $startDialogflow = microtime(true);
            $memoryDialogflowStart = memory_get_usage(true);
            
            Log::info("PERF_LAYER1_START", [
                'request_id' => $requestId,
                'layer' => 'dialogflow',
                'memory_before_mb' => round($memoryDialogflowStart / 1024 / 1024, 2)
            ]);
            
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            $memoryDialogflowEnd = memory_get_usage(true);
            $memoryDialogflowUsed = $memoryDialogflowEnd - $memoryDialogflowStart;
            
            $layerResults['dialogflow'] = [
                'duration_ms' => $durationDialogflow,
                'memory_used_mb' => round($memoryDialogflowUsed / 1024 / 1024, 2),
                'success' => $dialogflowResponse !== null,
                'is_fallback' => $dialogflowResponse['is_fallback'] ?? true,
                'confidence' => $dialogflowResponse['confidence'] ?? 0,
                'intent_name' => $dialogflowResponse['intent_name'] ?? 'unknown'
            ];
            
            Log::info("PERF_LAYER1_END", [
                'request_id' => $requestId,
                'layer' => 'dialogflow',
                'duration_ms' => $durationDialogflow,
                'memory_used_mb' => round($memoryDialogflowUsed / 1024 / 1024, 2),
                'success' => $dialogflowResponse !== null,
                'is_fallback' => $dialogflowResponse['is_fallback'] ?? true,
                'confidence' => round($dialogflowResponse['confidence'] ?? 0, 3),
                'intent_name' => $dialogflowResponse['intent_name'] ?? 'unknown'
            ]);

            // Check Dialogflow response validity
            $dialogflowValid = $this->isDialogflowResponseValid($dialogflowResponse);
            
            if ($dialogflowValid) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';
                
                // Get suggestions from OpenAI
                $startSuggestions = microtime(true);
                $memoryOpenAISugStart = memory_get_usage(true);
                
                Log::info("PERF_SUGGESTIONS_START", [
                    'request_id' => $requestId,
                    'type' => 'openai_suggestions_only',
                    'memory_before_mb' => round($memoryOpenAISugStart / 1024 / 1024, 2)
                ]);
                
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $durationSuggestions = round((microtime(true) - $startSuggestions) * 1000, 2);
                $memoryOpenAISugEnd = memory_get_usage(true);
                $memoryOpenAISugUsed = $memoryOpenAISugEnd - $memoryOpenAISugStart;
                
                $layerResults['openai_suggestions'] = [
                    'duration_ms' => $durationSuggestions,
                    'memory_used_mb' => round($memoryOpenAISugUsed / 1024 / 1024, 2),
                    'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                ];
                
                Log::info("PERF_SUGGESTIONS_END", [
                    'request_id' => $requestId,
                    'type' => 'openai_suggestions_only',
                    'duration_ms' => $durationSuggestions,
                    'memory_used_mb' => round($memoryOpenAISugUsed / 1024 / 1024, 2),
                    'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                ]);

                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                // === LAYER 2: FIRESTORE KNOWLEDGE BASE ===
                $startFirestore = microtime(true);
                $memoryFirestoreStart = memory_get_usage(true);
                
                Log::info("PERF_LAYER2_START", [
                    'request_id' => $requestId,
                    'layer' => 'firestore',
                    'memory_before_mb' => round($memoryFirestoreStart / 1024 / 1024, 2)
                ]);
                
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                $durationFirestore = round((microtime(true) - $startFirestore) * 1000, 2);
                $memoryFirestoreEnd = memory_get_usage(true);
                $memoryFirestoreUsed = $memoryFirestoreEnd - $memoryFirestoreStart;
                
                $layerResults['firestore'] = [
                    'duration_ms' => $durationFirestore,
                    'memory_used_mb' => round($memoryFirestoreUsed / 1024 / 1024, 2),
                    'found_answer' => $firestoreAnswer !== null,
                    'answer_length' => $firestoreAnswer ? strlen($firestoreAnswer) : 0
                ];
                
                Log::info("PERF_LAYER2_END", [
                    'request_id' => $requestId,
                    'layer' => 'firestore',
                    'duration_ms' => $durationFirestore,
                    'memory_used_mb' => round($memoryFirestoreUsed / 1024 / 1024, 2),
                    'found_answer' => $firestoreAnswer !== null,
                    'answer_length' => $firestoreAnswer ? strlen($firestoreAnswer) : 0
                ]);

                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';
                    
                    // Get suggestions from OpenAI
                    $startSuggestions = microtime(true);
                    $memoryOpenAISugStart = memory_get_usage(true);
                    
                    Log::info("PERF_SUGGESTIONS_START", [
                        'request_id' => $requestId,
                        'type' => 'openai_suggestions_only',
                        'memory_before_mb' => round($memoryOpenAISugStart / 1024 / 1024, 2)
                    ]);
                    
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $durationSuggestions = round((microtime(true) - $startSuggestions) * 1000, 2);
                    $memoryOpenAISugEnd = memory_get_usage(true);
                    $memoryOpenAISugUsed = $memoryOpenAISugEnd - $memoryOpenAISugStart;
                    
                    $layerResults['openai_suggestions'] = [
                        'duration_ms' => $durationSuggestions,
                        'memory_used_mb' => round($memoryOpenAISugUsed / 1024 / 1024, 2),
                        'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                    ];
                    
                    Log::info("PERF_SUGGESTIONS_END", [
                        'request_id' => $requestId,
                        'type' => 'openai_suggestions_only',
                        'duration_ms' => $durationSuggestions,
                        'memory_used_mb' => round($memoryOpenAISugUsed / 1024 / 1024, 2),
                        'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                    ]);

                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    // === LAYER 3: OPENAI FALLBACK ===
                    Log::info("PERF_LAYER3_START", [
                        'request_id' => $requestId,
                        'layer' => 'openai_fallback'
                    ]);
                    
                    // Check if web scraping is needed
                    $contextData = null;
                    $needsScraping = preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message);
                    
                    if ($needsScraping) {
                        $startScraping = microtime(true);
                        $memoryScrapingStart = memory_get_usage(true);
                        
                        Log::info("PERF_SCRAPING_START", [
                            'request_id' => $requestId,
                            'url' => 'https://genbicirebon.org/kegiatan',
                            'memory_before_mb' => round($memoryScrapingStart / 1024 / 1024, 2)
                        ]);
                        
                        $contextData = $this->scrapeWebsiteForActivities();
                        $durationScraping = round((microtime(true) - $startScraping) * 1000, 2);
                        $memoryScrapingEnd = memory_get_usage(true);
                        $memoryScrapingUsed = $memoryScrapingEnd - $memoryScrapingStart;
                        
                        $layerResults['web_scraping'] = [
                            'duration_ms' => $durationScraping,
                            'memory_used_mb' => round($memoryScrapingUsed / 1024 / 1024, 2),
                            'success' => $contextData !== null,
                            'data_length' => $contextData ? strlen($contextData) : 0
                        ];
                        
                        Log::info("PERF_SCRAPING_END", [
                            'request_id' => $requestId,
                            'duration_ms' => $durationScraping,
                            'memory_used_mb' => round($memoryScrapingUsed / 1024 / 1024, 2),
                            'success' => $contextData !== null,
                            'data_length' => $contextData ? strlen($contextData) : 0
                        ]);
                    }

                    $startOpenAI = microtime(true);
                    $memoryOpenAIStart = memory_get_usage(true);
                    
                    Log::info("PERF_OPENAI_START", [
                        'request_id' => $requestId,
                        'type' => 'full_response',
                        'has_context' => $contextData !== null,
                        'memory_before_mb' => round($memoryOpenAIStart / 1024 / 1024, 2)
                    ]);
                    
                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    $memoryOpenAIEnd = memory_get_usage(true);
                    $memoryOpenAIUsed = $memoryOpenAIEnd - $memoryOpenAIStart;
                    
                    $layerResults['openai_full'] = [
                        'duration_ms' => $durationOpenAI,
                        'memory_used_mb' => round($memoryOpenAIUsed / 1024 / 1024, 2),
                        'has_context' => $contextData !== null,
                        'success' => isset($openAIResult['answer']) && !empty($openAIResult['answer']),
                        'answer_length' => isset($openAIResult['answer']) ? strlen($openAIResult['answer']) : 0,
                        'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                    ];
                    
                    Log::info("PERF_OPENAI_END", [
                        'request_id' => $requestId,
                        'type' => 'full_response',
                        'duration_ms' => $durationOpenAI,
                        'memory_used_mb' => round($memoryOpenAIUsed / 1024 / 1024, 2),
                        'success' => isset($openAIResult['answer']) && !empty($openAIResult['answer']),
                        'answer_length' => isset($openAIResult['answer']) ? strlen($openAIResult['answer']) : 0,
                        'suggestions_count' => count($openAIResult['suggestions'] ?? [])
                    ]);

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';

                        // Learning Loop: Save to knowledge base
                        if ($contextData === null) {
                            $startKBSave = microtime(true);
                            
                            Log::info("PERF_KB_SAVE_START", [
                                'request_id' => $requestId,
                                'type' => 'knowledge_base_addition'
                            ]);
                            
                            $kbId = $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                            $durationKBSave = round((microtime(true) - $startKBSave) * 1000, 2);
                            
                            $layerResults['knowledge_base_save'] = [
                                'duration_ms' => $durationKBSave,
                                'firestore_id' => $kbId,
                                'success' => $kbId !== null
                            ];
                            
                            Log::info("PERF_KB_SAVE_END", [
                                'request_id' => $requestId,
                                'duration_ms' => $durationKBSave,
                                'firestore_id' => $kbId,
                                'success' => $kbId !== null
                            ]);
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang berbeda.";
                        $source = 'openai_fail';
                    }
                    
                    Log::info("PERF_LAYER3_END", [
                        'request_id' => $requestId,
                        'layer' => 'openai_fallback',
                        'final_source' => $source
                    ]);
                }
            }

            // === SAVE CHAT LOG & METRICS ===
            if ($source !== 'error') {
                $startChatLog = microtime(true);
                
                Log::info("PERF_CHATLOG_START", [
                    'request_id' => $requestId,
                    'source' => $source
                ]);
                
                $chatLogId = $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
                $durationChatLog = round((microtime(true) - $startChatLog) * 1000, 2);
                
                $startMetrics = microtime(true);
                $metricsId = $this->firestoreService->updateSystemMetrics($source);
                $durationMetrics = round((microtime(true) - $startMetrics) * 1000, 2);
                
                $layerResults['data_persistence'] = [
                    'chat_log' => [
                        'duration_ms' => $durationChatLog,
                        'firestore_id' => $chatLogId,
                        'success' => $chatLogId !== null
                    ],
                    'metrics' => [
                        'duration_ms' => $durationMetrics,
                        'firestore_id' => $metricsId,
                        'success' => $metricsId !== null
                    ]
                ];
                
                Log::info("PERF_CHATLOG_END", [
                    'request_id' => $requestId,
                    'chat_log_duration_ms' => $durationChatLog,
                    'chat_log_id' => $chatLogId,
                    'metrics_duration_ms' => $durationMetrics,
                    'metrics_id' => $metricsId
                ]);
            }

        } catch (\Exception $e) {
            $startErrorLog = microtime(true);
            $errorLogId = $this->firestoreService->addErrorLog($e->getMessage(), $message, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_id' => $requestId
            ]);
            $durationErrorLog = round((microtime(true) - $startErrorLog) * 1000, 2);
            
            Log::error("PERF_ERROR", [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error_log_duration_ms' => $durationErrorLog,
                'error_log_id' => $errorLogId
            ]);
        }

        // === FINAL PERFORMANCE SUMMARY ===
        $durationOverall = round((microtime(true) - $startOverall) * 1000, 2);
        $memoryEnd = memory_get_usage(true);
        $memoryPeakEnd = memory_get_peak_usage(true);
        $memoryUsedTotal = $memoryEnd - $memoryStart;
        $memoryPeakUsed = $memoryPeakEnd - $memoryPeakStart;
        
        Log::info("PERF_SUMMARY", [
            'request_id' => $requestId,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'final_source' => $source,
            'total_duration_ms' => $durationOverall,
            'active_users' => $activeUsers,
            'server_load' => round($serverLoad, 2),
            'memory_usage' => [
                'total_used_mb' => round($memoryUsedTotal / 1024 / 1024, 2),
                'peak_used_mb' => round($memoryPeakUsed / 1024 / 1024, 2),
                'final_usage_mb' => round($memoryEnd / 1024 / 1024, 2)
            ],
            'layer_performance' => $layerResults,
            'performance_grade' => $this->getPerformanceGrade($durationOverall),
            'timestamp_end' => now()->toISOString()
        ]);

        return response()->json($response);
    }

    private function getActiveUserCount(): int
    {
        try {
            // Estimate berdasarkan session yang aktif dalam 5 menit terakhir
            return $this->firestoreService->getActiveUserCount() ?? 1;
        } catch (\Exception $e) {
            return 1; // Default fallback
        }
    }

    private function getPerformanceGrade($duration): string
    {
        if ($duration < 500) return 'EXCELLENT';
        if ($duration < 1000) return 'GOOD';
        if ($duration < 2000) return 'FAIR';
        if ($duration < 5000) return 'POOR';
        return 'CRITICAL';
    }

    private function isDialogflowResponseValid($response): bool
    {
        return $response &&
               !empty($response['text']) &&
               !$response['is_fallback'] &&
               trim($response['text']) !== '' &&
               !str_contains(strtolower($response['text']), 'sorry') &&
               !str_contains(strtolower($response['text']), 'tidak mengerti');
    }

    private function detectIntent(string $text)
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $sessionId = session()->getId();
            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);
            
            if (!file_exists($credentialsPath)) {
                Log::error("DIALOGFLOW_CRED_MISSING", ['path' => $credentialsPath]);
                return null;
            }
            
            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $session = $sessionsClient->sessionName($projectId, $sessionId);
            
            $textInput = (new TextInput())
                ->setText($text)
                ->setLanguageCode('id');
            $queryInput = (new QueryInput())->setText($textInput);
            $request = (new DetectIntentRequest())
                ->setSession($session)
                ->setQueryInput($queryInput);
            
            $response = $sessionsClient->detectIntent($request);
            $queryResult = $response->getQueryResult();
            
            $fulfillmentText = $queryResult->getFulfillmentText();
            $intentName = $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'No Intent';
            $isFallback = $queryResult->getIntent() ? $queryResult->getIntent()->getIsFallback() : true;
            $confidence = $queryResult->getIntentDetectionConfidence();

            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            $memoryEnd = memory_get_usage(true);
            $memoryUsed = round(($memoryEnd - $memoryStart) / 1024 / 1024, 2);
            
            Log::info("PERF_TEST_DIALOGFLOW", [
                'test_id' => $testId,
                'duration_ms' => $durationDialogflow,
                'memory_used_mb' => $memoryUsed,
                'intent_name' => $intentName,
                'confidence' => round($confidence, 3),
                'is_fallback' => $isFallback
            ]);

            $sessionsClient->close();
            
            $totalDuration = round((microtime(true) - $startTest) * 1000, 2);
            
            Log::info("PERF_TEST_END", [
                'test_id' => $testId,
                'total_duration_ms' => $totalDuration,
                'status' => 'success'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Dialogflow berhasil!',
                'response' => $fulfillmentText ?: 'Tidak ada respons teks dari Dialogflow.',
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
                'duration_ms' => $durationDialogflow,
                'test_id' => $testId
            ]);
        } catch (\Exception $e) {
            $totalDuration = round((microtime(true) - $startTest) * 1000, 2);
            
            Log::error("PERF_TEST_ERROR", [
                'test_id' => $testId,
                'total_duration_ms' => $totalDuration,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $errorLogId = $this->firestoreService->addErrorLog($e->getMessage(), 'Test Dialogflow', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'test_id' => $testId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungkan ke Dialogflow: ' . $e->getMessage(),
                'test_id' => $testId,
                'error_log_id' => $errorLogId
            ], 500);
        }
    }

    public function sendMessageDialogflowOnly(Request $request)
    {
        $testId = 'DF_ONLY_' . Str::uuid()->toString();
        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());
        $startTest = microtime(true);
        $memoryStart = memory_get_usage(true);

        Log::info("PERF_DF_ONLY_START", [
            'test_id' => $testId,
            'session_id' => $sessionId,
            'message_length' => strlen($message),
            'memory_start_mb' => round($memoryStart / 1024 / 1024, 2)
        ]);

        $response = [
            'message' => 'Dialogflow tidak memberikan respons',
            'source' => 'dialogflow_fail',
            'debug_info' => [],
            'test_id' => $testId
        ];

        try {
            $startDialogflow = microtime(true);
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            $memoryEnd = memory_get_usage(true);
            $memoryUsed = round(($memoryEnd - $memoryStart) / 1024 / 1024, 2);

            Log::info("PERF_DF_ONLY_RESPONSE", [
                'test_id' => $testId,
                'duration_ms' => $durationDialogflow,
                'memory_used_mb' => $memoryUsed,
                'success' => $dialogflowResponse !== null,
                'intent_name' => $dialogflowResponse['intent_name'] ?? 'unknown',
                'confidence' => round($dialogflowResponse['confidence'] ?? 0, 3),
                'is_fallback' => $dialogflowResponse['is_fallback'] ?? true,
                'response_length' => $dialogflowResponse ? strlen($dialogflowResponse['text'] ?? '') : 0
            ]);

            if ($dialogflowResponse && !empty($dialogflowResponse['text'])) {
                $response['message'] = $dialogflowResponse['text'];
                $response['source'] = 'dialogflow';
                $response['debug_info'] = $dialogflowResponse;
            }
        } catch (\Exception $e) {
            Log::error("PERF_DF_ONLY_ERROR", [
                'test_id' => $testId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        $totalDuration = round((microtime(true) - $startTest) * 1000, 2);
        $memoryFinal = memory_get_usage(true);
        
        Log::info("PERF_DF_ONLY_END", [
            'test_id' => $testId,
            'total_duration_ms' => $totalDuration,
            'final_source' => $response['source'],
            'memory_final_mb' => round($memoryFinal / 1024 / 1024, 2),
            'performance_grade' => $this->getPerformanceGrade($totalDuration)
        ]);

        return response()->json($response);
    }

    /**
     * Get system performance metrics
     */
    public function getPerformanceMetrics()
    {
        $metricsId = 'METRICS_' . Str::uuid()->toString();
        $startMetrics = microtime(true);
        
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $serverLoad = sys_getloadavg();
            $activeUsers = $this->getActiveUserCount();
            
            // Get recent performance data from Firestore
            $recentPerformance = $this->firestoreService->getRecentPerformanceMetrics();
            
            $metrics = [
                'metrics_id' => $metricsId,
                'timestamp' => now()->toISOString(),
                'system' => [
                    'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                    'memory_peak_mb' => round($memoryPeak / 1024 / 1024, 2),
                    'server_load_1min' => round($serverLoad[0] ?? 0, 2),
                    'server_load_5min' => round($serverLoad[1] ?? 0, 2),
                    'server_load_15min' => round($serverLoad[2] ?? 0, 2),
                    'active_users' => $activeUsers
                ],
                'chatbot_performance' => $recentPerformance ?? [
                    'avg_response_time_ms' => 0,
                    'total_requests_last_hour' => 0,
                    'success_rate_percent' => 0,
                    'dialogflow_usage_percent' => 0,
                    'firestore_usage_percent' => 0,
                    'openai_usage_percent' => 0
                ],
                'collection_duration_ms' => round((microtime(true) - $startMetrics) * 1000, 2)
            ];
            
            Log::info("PERF_METRICS_COLLECTED", $metrics);
            
            return response()->json($metrics);
        } catch (\Exception $e) {
            Log::error("PERF_METRICS_ERROR", [
                'metrics_id' => $metricsId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Failed to collect performance metrics',
                'metrics_id' => $metricsId
            ], 500);
        }
    }

    /**
     * Get detailed system health check
     */
    public function healthCheck()
    {
        $healthId = 'HEALTH_' . Str::uuid()->toString();
        $startHealth = microtime(true);
        
        Log::info("PERF_HEALTH_START", ['health_id' => $healthId]);
        
        $checks = [
            'dialogflow' => $this->checkDialogflowHealth(),
            'firestore' => $this->checkFirestoreHealth(),
            'openai' => $this->checkOpenAIHealth(),
            'web_scraping' => $this->checkWebScrapingHealth()
        ];
        
        $allHealthy = array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] === 'healthy';
        }, true);
        
        $totalDuration = round((microtime(true) - $startHealth) * 1000, 2);
        
        $healthStatus = [
            'health_id' => $healthId,
            'timestamp' => now()->toISOString(),
            'overall_status' => $allHealthy ? 'healthy' : 'degraded',
            'total_check_duration_ms' => $totalDuration,
            'services' => $checks,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]
        ];
        
        Log::info("PERF_HEALTH_END", [
            'health_id' => $healthId,
            'overall_status' => $healthStatus['overall_status'],
            'total_duration_ms' => $totalDuration,
            'services_checked' => array_keys($checks)
        ]);
        
        return response()->json($healthStatus);
    }

    private function checkDialogflowHealth(): array
    {
        $start = microtime(true);
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);
            
            if (!file_exists($credentialsPath)) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Credentials file not found',
                    'duration_ms' => round((microtime(true) - $start) * 1000, 2)
                ];
            }
            
            // Quick connection test
            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $sessionsClient->close();
            
            return [
                'status' => 'healthy',
                'message' => 'Connection successful',
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        }
    }

    private function checkFirestoreHealth(): array
    {
        $start = microtime(true);
        try {
            // Test Firestore connection
            $testResult = $this->firestoreService->healthCheck();
            
            return [
                'status' => $testResult ? 'healthy' : 'unhealthy',
                'message' => $testResult ? 'Connection successful' : 'Connection failed',
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        }
    }

    private function checkOpenAIHealth(): array
    {
        $start = microtime(true);
        try {
            $apiKey = env('OPENROUTER_API_KEY');
            
            if (empty($apiKey)) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'API key not configured',
                    'duration_ms' => round((microtime(true) - $start) * 1000, 2)
                ];
            }
            
            // Quick API test with minimal request
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [["role" => "user", "content" => "test"]],
                "max_tokens" => 5,
            ]);
            
            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'message' => $response->successful() ? 'API responding' : 'API error: ' . $response->status(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        }
    }

    private function checkWebScrapingHealth(): array
    {
        $start = microtime(true);
        try {
            $response = Http::timeout(10)->get('https://genbicirebon.org');
            
            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'message' => $response->successful() ? 'Website accessible' : 'Website unreachable: ' . $response->status(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2)
            ];
        }
    }
}Intent() ? $queryResult->getIntent()->getDisplayName() : 'No Intent';
            $isFallback = $queryResult->getIntent() ? $queryResult->getIntent()->getIsFallback() : true;
            $confidence = $queryResult->getIntentDetectionConfidence();
            
            $sessionsClient->close();
            
            return [
                'text' => $fulfillmentText,
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
            ];
        } catch (\Exception $e) {
            Log::error("DIALOGFLOW_ERROR", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/kegiatan';
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::warning("SCRAPING_HTTP_FAIL", ['url' => $url, 'status' => $response->status()]);
                return null;
            }

            $crawler = new Crawler($response->body());

            $activities = $crawler->filter('.blog-item')->slice(0, 5)->each(function (Crawler $node) {
                $titleNode = $node->filter('.blog-title a');
                $title = $titleNode->count() ? $titleNode->text('Judul tidak ditemukan') : 'Judul tidak ditemukan';

                $dateNode = $node->filter('.blog-meta span')->first();
                $date = $dateNode->count() ? $dateNode->text('Tanggal tidak ditemukan') : 'Tanggal tidak ditemukan';

                return "- {$title} (dipublikasikan sekitar {$date})";
            });

            if (empty($activities)) {
                return "Saat ini tidak ada informasi kegiatan terbaru yang bisa ditampilkan dari website.";
            }

            return "Berikut adalah beberapa kegiatan atau berita terbaru dari website genbicirebon.org:\n" . implode("\n", $activities);
        } catch (\Exception $e) {
            Log::error('SCRAPING_ERROR', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI yang ramah, informatif, dan ahli tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon), sebuah komunitas penerima beasiswa Bank Indonesia. Website resmi adalah genbicirebon.org. Jawablah semua pertanyaan dalam konteks ini.";

        $promptAction = $suggestionsOnly
            ? "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang relevan dengan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna."
            : "Jawab pertanyaan pengguna secara ringkas dan informatif. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan sangat singkat (maksimal 4 kata per saran).";

        $contextInjection = $externalContext
            ? "Gunakan informasi tambahan berikut untuk menjawab pertanyaan pengguna secara akurat:\n---INFO TAMBAHAN---\n{$externalContext}\n-------------------\n"
            : "";

        $systemPrompt = "{$siteContext} {$contextInjection} {$promptAction} Format respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi field 'answer' dengan string kosong.";

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'Genbi Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $text]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.4,
                "max_tokens" => 500,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return [
                    'answer' => $data['answer'] ?? ($suggestionsOnly ? '' : 'Gagal memformat jawaban.'),
                    'suggestions' => $data['suggestions'] ?? [],
                ];
            }

            Log::error('OPENAI_HTTP_ERROR', ['response' => $response->body()]);
            return ['answer' => 'Maaf, saya sedang mengalami kendala teknis (API).', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error('OPENAI_EXCEPTION', ['error' => $e->getMessage()]);
            return ['answer' => 'Maaf, koneksi ke asisten AI sedang bermasalah.', 'suggestions' => []];
        }
    }

 