<?php

namespace App\Services;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Illuminate\Support\Facades\Log;

class DialogflowService
{
    protected $projectId;
    protected $credentials;
    protected $languageCode;

    public function __construct()
    {
        $this->projectId = config('services.dialogflow.project_id');
        $this->credentials = config('services.dialogflow.credentials');
        $this->languageCode = 'id'; // atau 'en' untuk English
        // Tambahkan di constructor DialogflowService
        if (!file_exists($this->credentials)) {
            Log::error('Dialogflow credentials file not found at: ' . $this->credentials);
            throw new \Exception('Credentials file not found');
        }
    }

    public function detectIntentText($text, $sessionId)
    {
        // Tambahkan debug log
        Log::debug('Dialogflow Config:', [
            'project_id' => $this->projectId,
            'credentials_path' => $this->credentials,
            'file_exists' => file_exists($this->credentials)
        ]);

        try {
            putenv("GOOGLE_APPLICATION_CREDENTIALS=" . $this->credentials);

            $sessionsClient = new SessionsClient();
            $session = $sessionsClient->sessionName($this->projectId, $sessionId);

            // Debug session
            Log::debug('Dialogflow Session:', ['session' => $session]);

            $textInput = new TextInput();
            $textInput->setText($text);
            $textInput->setLanguageCode($this->languageCode);

            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            $response = $sessionsClient->detectIntent($session, $queryInput);
            $queryResult = $response->getQueryResult();

            // Debug response
            Log::debug('Dialogflow Response:', [
                'fulfillmentText' => $queryResult->getFulfillmentText(),
                'intent' => $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : null,
                'confidence' => $queryResult->getIntentDetectionConfidence()
            ]);

            $sessionsClient->close();

            return [
                'response' => $queryResult->getFulfillmentText() ?? 'Maaf, saya tidak mengerti pertanyaan itu',
                'intent' => $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'default',
                'confidence' => $queryResult->getIntentDetectionConfidence()
            ];
        } catch (\Exception $e) {
            Log::error('Dialogflow Error:', ['error' => $e->getMessage()]);
            return [
                'response' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi nanti.',
                'intent' => 'error',
                'confidence' => 0
            ];
        }
    }
}
