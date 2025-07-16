<?php

require_once 'vendor/autoload.php';

// Test 1: Cek apakah class tersedia
if (class_exists('Google\Cloud\Dialogflow\V2\SessionsClient')) {
    echo "✓ Dialogflow SDK berhasil dimuat\n";
} else {
    echo "✗ Dialogflow SDK tidak ditemukan\n";
    exit;
}

// Test 2: Cek environment variables
$projectId = env('DIALOGFLOW_PROJECT_ID');
$credentials = env('DIALOGFLOW_CREDENTIALS');

echo "Project ID: " . ($projectId ?: 'NOT SET') . "\n";
echo "Credentials: " . ($credentials ?: 'NOT SET') . "\n";

// Test 3: Cek file credentials
if (file_exists($credentials)) {
    echo "✓ File credentials ditemukan\n";
} else {
    echo "✗ File credentials tidak ditemukan\n";
}

// Test 4: Cek isi file credentials
$credentialsContent = file_get_contents($credentials);
$credentialsJson = json_decode($credentialsContent, true);

if ($credentialsJson && isset($credentialsJson['project_id'])) {
    echo "✓ File credentials valid\n";
    echo "Project ID in credentials: " . $credentialsJson['project_id'] . "\n";
} else {
    echo "✗ File credentials tidak valid\n";
}
