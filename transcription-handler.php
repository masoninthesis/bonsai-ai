<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the Content-Type header to application/json
header('Content-Type: application/json');

if (!empty($_FILES['audio_file']['tmp_name'])) {
    $targetDir = "uploads/"; // Ensure this directory exists and is writable
    // It's a good practice to ensure the uploads directory exists and is writable
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetFile = $targetDir . basename($_FILES["audio_file"]["name"]);

    // Move the uploaded file
    if (move_uploaded_file($_FILES["audio_file"]["tmp_name"], $targetFile)) {
        // Here, call Deepgram API for transcription
        // This is a placeholder - replace with actual Deepgram API call and handle the response
        $transcriptionText = 'This is a dummy transcription. Replace with actual API call result.';

        echo json_encode(['success' => true, 'transcription' => $transcriptionText]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save the file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
}
