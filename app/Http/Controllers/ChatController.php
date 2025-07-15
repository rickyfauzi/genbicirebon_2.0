<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function detectIntent(Request $request)
    {
        $request->validate([
            'queryText' => 'required|string',
            'session' => 'nullable|string',
        ]);

        try {
            // 1. Setup Credentials
            $credentialsPath = storage_path('app/genbi-key.json');
            if (!file_exists($credentialsPath)) {
                throw new \Exception('File credentials tidak ditemukan');
            }

            putenv("GOOGLE_APPLICATION_CREDENTIALS=$credentialsPath");

            $projectId = 'genbichatbot-gjxn';
            $sessionId = $request->input('session', 'web-session-' . uniqid());
            $queryText = $request->input('queryText');

            $sessionsClient = new SessionsClient([
                'credentials' => $credentialsPath
            ]);

            $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

            // 3. Create Query Input
            $textInput = (new TextInput())
                ->setText($queryText)
                ->setLanguageCode('id');

            $queryInput = (new QueryInput())
                ->setText($textInput);

            // 4. Create DetectIntentRequest
            $detectIntentRequest = (new DetectIntentRequest())
                ->setSession($sessionName)
                ->setQueryInput($queryInput);

            // 5. Send Request
            $response = $sessionsClient->detectIntent($detectIntentRequest);
            $queryResult = $response->getQueryResult();

            // 6. Close Connection
            $sessionsClient->close();

            return response()->json([
                'fulfillmentText' => $queryResult->getFulfillmentText() ?: 'Maaf, saya tidak mengerti',
                'intent' => $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : null
            ]);
        } catch (\Exception $e) {
            Log::error('Dialogflow Error: ' . $e->getMessage());
            return response()->json([
                'fulfillmentText' => 'Maaf, sedang ada gangguan teknis',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
}
