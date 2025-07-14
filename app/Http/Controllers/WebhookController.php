<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Firestore\FirestoreClient;
use Exception;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;

class WebhookController extends Controller
{
    private $firestore;
    private $projectId = 'genbichatbot'; // Pastikan ini adalah Project ID Google Cloud Anda

    public function __construct()
    {
        // Inisialisasi Firestore Client saat controller dibuat
        // Pastikan GOOGLE_APPLICATION_CREDENTIALS sudah diatur di .env
        if (config('services.google.credentials_path') || env('GOOGLE_APPLICATION_CREDENTIALS')) {
            try {
                $this->firestore = new FirestoreClient([
                    'projectId' => $this->projectId,
                ]);
            } catch (Exception $e) {
                Log::error('Firestore Initialization Failed: ' . $e->getMessage());
                $this->firestore = null;
            }
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

        // **LOGIKA CACHING: Cek jawaban di Firestore terlebih dahulu**
        $cachedResponse = $this->getFromCache($queryText, $intentName);
        if ($cachedResponse) {
            $this->logToFirestore($sessionId, $queryText, $cachedResponse, $intentName, 'cache');
            return response()->json(['fulfillmentText' => $cachedResponse]);
        }

        // Jika tidak ada di cache, buat jawaban dinamis
        $responseText = $this->getResponseForIntent($intentName, $queryText, $data);

        // Simpan jawaban baru ke Firestore untuk logging
        $this->logToFirestore($sessionId, $queryText, $responseText, $intentName, 'webhook');

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

        // **LOGIKA CACHING: Cek jawaban di Firestore terlebih dahulu**
        $cachedResponse = $this->getFromCache($queryText);
        if ($cachedResponse) {
            $this->logToFirestore($sessionId, $queryText, $cachedResponse, 'N/A', 'cache');
            return response()->json(['fulfillmentText' => $cachedResponse, 'source' => 'cache']);
        }

        // Jika tidak ada di cache, hubungi Dialogflow
        $dialogflowResponse = $this->detectIntent($this->projectId, $sessionId, $queryText, 'id');

        // Simpan jawaban baru ke Firestore untuk logging
        $this->logToFirestore($sessionId, $queryText, $dialogflowResponse['fulfillmentText'], $dialogflowResponse['intentName'], 'dialogflow');

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
        // Di sini Anda bisa menambahkan logika kustom.
        switch ($intentName) {
            case 'definisi.genbi':
                return "Ini jawaban dari Webhook: GenBI (Generasi Baru Indonesia) adalah komunitas elit penerima beasiswa Bank Indonesia yang dibina untuk menjadi pemimpin masa depan.";
            case 'kontakgenbiintent':
                return "Anda bisa menghubungi kami via Instagram @genbi.cirebon atau email genbicirebon@gmail.com. (Info dari Webhook).";
                // Tambahkan case lain untuk intent yang membutuhkan logika backend
            default:
                // Jika tidak ada logika khusus, gunakan response default dari Dialogflow
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
                'source' => $source, // 'dialogflow', 'webhook', 'cache'
                'timestamp' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log to Firestore: ' . $e->getMessage());
        }
    }

    /**
     * Checks for a relevant answer in the Firestore cache.
     * Caching key is a normalized version of the question text.
     */
    private function getFromCache($question, $intentName = null)
    {
        if (!$this->firestore) {
            Log::warning('Firestore client not available. Skipping cache check.');
            return null;
        }

        // Normalisasi pertanyaan untuk dijadikan kunci cache
        $cacheKey = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '', $question)));
        if (empty($cacheKey)) return null;

        try {
            // Collection untuk cache bisa berbeda, misal 'chat_cache'
            $cacheRef = $this->firestore->collection('chat_cache')->document($cacheKey);
            $snapshot = $cacheRef->snapshot();

            if ($snapshot->exists()) {
                Log::info('Cache hit!', ['key' => $cacheKey]);
                // Anda bisa menambahkan logika relevansi di sini,
                // misal: cek apakah intent-nya sama jika tersedia
                return $snapshot->data()['answer'];
            }

            Log::info('Cache miss.', ['key' => $cacheKey]);
            return null;
        } catch (Exception $e) {
            Log::error('Failed to get from cache: ' . $e->getMessage());
            return null;
        }
    }
}
