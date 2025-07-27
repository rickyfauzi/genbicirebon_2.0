<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Struct;
use Google\Protobuf\Value;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            if (!class_exists(SessionsClient::class)) {
                Log::error('SessionsClient class not found. Ensure google/cloud-dialogflow is installed.');
                return response()->json([
                    'message' => 'Dialogflow library not found. Please install with composer.',
                    'quick_replies' => []
                ], 500);
            }

            $message = $request->input('message');
            $response = $this->detectIntent($message);

            return response()->json([
                'message' => $response['message'],
                'quick_replies' => $response['quick_replies']
            ]);
        } catch (\Exception $e) {
            Log::error("Exception: " . $e->getMessage());
            Log::error("File: " . $e->getFile());
            Log::error("Line: " . $e->getLine());
            return response()->json([
                'message' => 'Maaf, terjadi kesalahan di server.',
                'quick_replies' => []
            ], 500);
        }
    }

    protected function detectIntent(string $text): array
    {
        $projectId = 'websitebot-etqi';
        $sessionId = session()->getId();
        $credentialsPath = storage_path('app/google/dialogflow-credentials.json');

        $sessionsClient = new SessionsClient([
            'credentials' => $credentialsPath
        ]);

        $session = $sessionsClient->sessionName($projectId, $sessionId);

        // Prepare text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id');

        // Create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // Create detect intent request
        $detectIntentRequest = new DetectIntentRequest();
        $detectIntentRequest->setSession($session);
        $detectIntentRequest->setQueryInput($queryInput);

        // Get response
        $response = $sessionsClient->detectIntent($detectIntentRequest);
        $queryResult = $response->getQueryResult();

        // Get fulfillment text
        $fulfillmentText = $queryResult->getFulfillmentText();

        // Extract quick replies from parameters
        $quickReplies = $this->extractQuickReplies($queryResult);

        // If no quick replies from parameters, determine based on context
        if (empty($quickReplies)) {
            $activeContexts = $sessionsClient->listContexts($session);
            $quickReplies = $this->determineQuickRepliesFromContext($activeContexts);
        }

        // Close the session client
        $sessionsClient->close();

        return [
            'message' => $fulfillmentText ?: 'Maaf, saya tidak mengerti pertanyaan Anda.',
            'quick_replies' => $quickReplies
        ];
    }

    protected function extractQuickReplies($queryResult): array
    {
        $quickReplies = [];
        $parameters = $queryResult->getParameters();

        if ($parameters && $parameters->hasField('quick_replies')) {
            $quickRepliesField = $parameters->getField('quick_replies');
            if ($quickRepliesField->hasListValue()) {
                $listValue = $quickRepliesField->getListValue();
                foreach ($listValue->getValues() as $value) {
                    if ($value->hasStringValue()) {
                        $quickReplies[] = $value->getStringValue();
                    }
                }
            }
        }

        return $quickReplies;
    }

    protected function determineQuickRepliesFromContext(RepeatedField $activeContexts): array
    {
        foreach ($activeContexts as $context) {
            $contextName = $context->getName();

            if (strpos($contextName, 'beasiswa-context') !== false) {
                return [
                    "Syarat pendaftaran",
                    "Dokumen yang dibutuhkan",
                    "Timeline pendaftaran",
                    "Kontak admin beasiswa"
                ];
            }

            if (strpos($contextName, 'program-context') !== false) {
                return [
                    "Program sosial",
                    "Program edukasi",
                    "Event mendatang",
                    "Info volunteer"
                ];
            }

            if (strpos($contextName, 'pendaftaran-context') !== false) {
                return [
                    "Formulir pendaftaran",
                    "Persyaratan dokumen",
                    "Jadwal seleksi",
                    "Kriteria penilaian"
                ];
            }
        }

        // Default quick replies
        return [
            "Info beasiswa",
            "Program unggulan",
            "Cara mendaftar",
            "Hubungi admin"
        ];
    }
}
