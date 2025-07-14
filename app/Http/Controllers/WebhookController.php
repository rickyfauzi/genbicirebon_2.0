<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// =========================================================================
// PERUBAHAN UTAMA: Namespace yang benar untuk library Google Cloud terbaru
// =========================================================================
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Firestore\FirestoreClient;
use Exception;

class WebhookController extends Controller
{
    private $firestore;
    private $projectId = 'genbichatbot'; // Pastikan ini adalah Project ID Google Cloud Anda

    public function __construct()
    {
        // Inisialisasi Firestore Client saat controller dibuat
        try {
            // Konstruktor FirestoreClient secara otomatis akan mencari GOOGLE_APPLICATION_CREDENTIALS dari env
            $this->firestore = new FirestoreClient([
                'projectId' => $this->projectId,
            ]);
        } catch (Exception $e) {
            Log::error('Firestore Initialization Failed: ' . $e->getMessage());
            $this->firestore = null;
        }
    }

    /**
     * Handles all incoming chat requests, routing them based on their source.
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('🔥 RAW JSON RECEIVED', ['content' => $request->getContent()]);

        try {
            // Case 1: Request is a Fulfillment Webhook from DIALOGFLOW
            if (isset($data['queryResult'])) {
                return $this->handleDialogflowFulfillment($data);
            }
            // Case 2: Request is a Proxy call from OUR CUSTOM FRONTEND
            elseif (isset($data['queryText']) && isset($data['sessionId'])) {
                return $this->handleFrontendProxy($request);
            }
            // If neither format matches
            throw new Exception('Invalid request format.');
        } catch (Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'fulfillmentText' => 'Maaf, sistem chatbot sedang mengalami gangguan teknis. Coba lagi nanti.',
                'source' => 'genbicirebon.org (Error)'
            ], 500);
        }
    }

    /**
     * Handles requests coming directly from Dialogflow's fulfillment webhook.
     */
    private function handleDialogflowFulfillment($data)
    {
        Log::info('Handling Dialogflow Fulfillment Webhook.');

        $intentName = $data['queryResult']['intent']['displayName'] ?? 'Unknown Intent';
        $queryText = $data['queryResult']['queryText'] ?? '';
        $sessionId = last(explode('/', $data['session'])); // Ekstrak session ID

        $cachedResponse = $this->getFromCache($queryText);
        if ($cachedResponse) {
            $this->logToFirestore($sessionId, $queryText, $cachedResponse, $intentName, 'cache');
            return response()->json(['fulfillmentText' => $cachedResponse]);
        }

        $responseText = $this->getResponseForIntent($intentName, $queryText, $data);

        $this->logToFirestore($sessionId, $queryText, $responseText, $intentName, 'webhook');
        $this->saveToCache($queryText, $responseText); // Simpan ke cache jika jawaban baru

        return response()->json(['fulfillmentText' => $responseText]);
    }

    /**
     * Handles proxy requests from our custom frontend chat.
     */
    private function handleFrontendProxy(Request $request)
    {
        Log::info('Handling Proxy from Custom Frontend.');

        $validated = $request->validate([
            'queryText' => 'required|string|max:255',
            'sessionId' => 'required|string',
        ]);

        $queryText = $validated['queryText'];
        $sessionId = $validated['sessionId'];

        $cachedResponse = $this->getFromCache($queryText);
        if ($cachedResponse) {
            $this->logToFirestore($sessionId, $queryText, $cachedResponse, 'N/A from Cache', 'cache');
            return response()->json(['fulfillmentText' => $cachedResponse, 'source' => 'cache']);
        }

        $dialogflowResponse = $this->detectIntent($this->projectId, $sessionId, $queryText, 'id');

        $this->logToFirestore($sessionId, $queryText, $dialogflowResponse['fulfillmentText'], $dialogflowResponse['intentName'], 'dialogflow');
        $this->saveToCache($queryText, $dialogflowResponse['fulfillmentText']); // Simpan ke cache jika jawaban baru

        return response()->json([
            'fulfillmentText' => $dialogflowResponse['fulfillmentText'],
            'source' => 'genbicirebon.org (Proxy)',
            'detectedIntent' => $dialogflowResponse['intentName']
        ]);
    }

    /**
     * Gets a dynamic response based on intent for fulfillment calls.
     */
    private function getResponseForIntent($intentName, $queryText, $data)
    {
        switch ($intentName) {
            case 'definisi.genbi':
                return "Ini jawaban dari Webhook: GenBI (Generasi Baru Indonesia) adalah komunitas elit penerima beasiswa Bank Indonesia yang dibina untuk menjadi pemimpin masa depan.";
            case 'kontakgenbiintent':
                return "Anda bisa menghubungi kami via Instagram @genbi.cirebon atau email genbicirebon@gmail.com. (Info dari Webhook).";
            default:
                return $data['queryResult']['fulfillmentText'] ?? "Maaf, saya belum mengerti pertanyaan itu.";
        }
    }

    /**
     * Calls Dialogflow API to detect intent.
     */
    private function detectIntent($projectId, $sessionId, $text, $languageCode)
    {
        $sessionsClient = new SessionsClient();
        $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        try {
            $response = $sessionsClient->detectIntent($sessionName, $queryInput);
            $queryResult = $response->getQueryResult();
            return [
                'fulfillmentText' => $queryResult->getFulfillmentText(),
                'intentName' => $queryResult->getIntent()->getDisplayName(),
            ];
        } finally {
            $sessionsClient->close();
        }
    }

    /**
     * Logs the conversation to Firestore.
     */
    private function logToFirestore($sessionId, $question, $answer, $intentName, $source)
    {
        if (!$this->firestore) {
            Log::warning('Firestore client not available. Skipping log.');
            return;
        }
        try {
            $collectionReference = $this->firestore->collection('chat_history');
            $collectionReference->add([
                'sessionId' => $sessionId,
                'question' => $question,
                'answer' => $answer,
                'intent' => $intentName,
                'source' => $source,
                'timestamp' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log to Firestore: ' . $e->getMessage());
        }
    }

    /**
     * Creates a normalized key for caching.
     */
    private function getCacheKey($question)
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $question)));
    }

    /**
     * Checks for a relevant answer in the Firestore cache.
     */
    private function getFromCache($question)
    {
        if (!$this->firestore) {
            return null;
        }

        $cacheKey = $this->getCacheKey($question);
        if (empty($cacheKey)) return null;

        try {
            $cacheRef = $this->firestore->collection('chat_cache')->document($cacheKey);
            $snapshot = $cacheRef->snapshot();

            if ($snapshot->exists()) {
                Log::info('Cache hit!', ['key' => $cacheKey]);
                return $snapshot->data()['answer'];
            }

            Log::info('Cache miss.', ['key' => $cacheKey]);
            return null;
        } catch (Exception $e) {
            Log::error('Failed to get from cache: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Saves a new question-answer pair to the cache.
     */
    private function saveToCache($question, $answer)
    {
        if (!$this->firestore || empty($answer)) {
            return;
        }

        $cacheKey = $this->getCacheKey($question);
        if (empty($cacheKey)) return;

        try {
            $cacheRef = $this->firestore->collection('chat_cache')->document($cacheKey);
            $cacheRef->set([
                'question' => $question,
                'answer' => $answer,
                'last_updated' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ]);
            Log::info('Saved to cache.', ['key' => $cacheKey]);
        } catch (Exception $e) {
            Log::error('Failed to save to cache: ' . $e->getMessage());
        }
    }
}
