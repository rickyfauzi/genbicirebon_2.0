<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

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

    public function addChatLog($data)
    {
        return $this->db->collection('chat_logs')->add($data);
    }

    public function addErrorLog($data)
    {
        return $this->db->collection('error_logs')->add($data);
    }

    public function updateSystemMetrics($date, $data)
    {
        $docRef = $this->db->collection('system_metrics')->document($date);
        return $docRef->set($data, ['merge' => true]);
    }

    public function addKnowledgeBase($data)
    {
        return $this->db->collection('knowledge_base')->add($data);
    }
}
