<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;

class ChatbotController extends Controller
{
    public function detectIntent(Request $request)
    {
        $text = $request->input('message');
        $projectId = 'learned-fusion-460215';

        $sessionClient = new SessionsClient();
        $session = $sessionClient->sessionName($projectId, uniqid());

        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id'); // bahasa Indonesia

        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        $response = $sessionClient->detectIntent($session, $queryInput);
        $result = $response->getQueryResult();
        $reply = $result->getFulfillmentText();

        $sessionClient->close();

        return response()->json(['reply' => $reply]);
    }
}
