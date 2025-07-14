<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\ApiCore\ApiException;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;

class WebhookController extends Controller
{
    /**
     * Handles incoming chat messages from the custom frontend,
     * detects the intent using Dialogflow, and returns the fulfillment text.
     */
    public function handle(Request $request)
    {
        // 1. Validate the incoming request from your frontend
        $validated = $request->validate([
            'queryText' => 'required|string|max:255',
            'sessionId' => 'required|string',
        ]);

        $queryText = $validated['queryText'];
        $sessionId = $validated['sessionId'];
        $projectId = 'genbichatbot'; // Your Dialogflow Project ID
        $languageCode = 'id';

        try {
            // 2. Send the user's query to Dialogflow to detect the intent
            $dialogflowResponse = $this->detectIntent($projectId, $sessionId, $queryText, $languageCode);

            $fulfillmentText = $dialogflowResponse['fulfillmentText'];
            $intentName = $dialogflowResponse['intentName'];

            // 3. (Optional) Log the conversation to your database or Firestore
            // $this->saveToFirestore($queryText, $fulfillmentText, $sessionId, $intentName);

            // 4. Return the response from Dialogflow back to the frontend
            return response()->json([
                'fulfillmentText' => $fulfillmentText,
                'source' => 'genbicirebon.org (via Dialogflow)',
                'detectedIntent' => $intentName
            ]);
        } catch (ApiException $e) {
            Log::error('Dialogflow API Exception: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'fulfillmentText' => 'Maaf, terjadi kesalahan saat menghubungi asisten virtual kami. Coba lagi nanti.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Webhook General Error: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'fulfillmentText' => 'Maaf, sedang ada gangguan teknis. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    /**
     * Sends a query to the Dialogflow agent.
     *
     * @param string $projectId Your Dialogflow project ID
     * @param string $sessionId A unique identifier for the conversation session
     * @param string $text The user's query text
     * @param string $languageCode The language code (e.g., 'en-US', 'id')
     * @return array
     */
    private function detectIntent($projectId, $sessionId, $text, $languageCode)
    {
        // Set up the Dialogflow Sessions Client
        $sessionsClient = new SessionsClient(['credentials' => config('services.dialogflow.credentials_path')]);
        $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

        // Create the Text Input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        // Create the Query Input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // Send the request to Dialogflow
        $response = $sessionsClient->detectIntent($sessionName, $queryInput);
        $queryResult = $response->getQueryResult();

        $sessionsClient->close();

        return [
            'fulfillmentText' => $queryResult->getFulfillmentText(),
            'intentName' => $queryResult->getIntent()->getDisplayName(),
        ];
    }
}
