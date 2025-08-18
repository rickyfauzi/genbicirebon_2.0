<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;

class FirestoreService
{
    protected $db;

    public function __construct()
    {
        $this->db = new FirestoreClient([
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
            'projectId'   => env('FIREBASE_PROJECT_ID', 'chatbot'),
        ]);
    }

    /**
     * Simpan log percakapan user ↔ chatbot
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null)
    {
        return $this->db->collection('chat_logs')->add([
            'session_id' => $sessionId,
            'question'   => $question,
            'answer'     => $answer,
            'source'     => $source, // "dialogflow" | "openai"
            'timestamp'  => new Timestamp(new \DateTime()),
            'user_id'    => $userId,
        ]);
    }

    /**
     * Simpan error log (ketika ada kegagalan)
     */
    public function addErrorLog(string $errorMessage, string $userMessage = null, $userId = null)
    {
        return $this->db->collection('error_logs')->add([
            'error_message' => $errorMessage,
            'user_message'  => $userMessage,
            'timestamp'     => new Timestamp(new \DateTime()),
            'user_id'       => $userId,
        ]);
    }

    /**
     * Update system metrics per hari (total query, success, fallback)
     */
    public function updateSystemMetrics(string $date, array $updates)
    {
        $docRef = $this->db->collection('system_metrics')->document($date);

        // gunakan merge untuk update parsial
        return $docRef->set($updates, ['merge' => true]);
    }

    /**
     * Tambahkan knowledge base baru
     */
    public function addKnowledgeBase(string $question, string $answer, string $category = null)
    {
        return $this->db->collection('knowledge_base')->add([
            'question'   => $question,
            'answer'     => $answer,
            'category'   => $category,
            'created_at' => new Timestamp(new \DateTime()),
        ]);
    }
}
