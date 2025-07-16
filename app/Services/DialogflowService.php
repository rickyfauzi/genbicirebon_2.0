<?php

namespace App\Services;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;

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
    }

    public function detectIntentText($text, $sessionId)
    {
        putenv("GOOGLE_APPLICATION_CREDENTIALS=" . $this->credentials);

        $sessionsClient = new SessionsClient();
        $session = $sessionsClient->sessionName($this->projectId, $sessionId);

        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($this->languageCode);

        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();

        $sessionsClient->close();

        return [
            'response' => $queryResult->getFulfillmentText(),
            'intent' => $queryResult->getIntent()->getDisplayName(),
            'confidence' => $queryResult->getIntentDetectionConfidence()
        ];
    }
}
