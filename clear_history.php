<?php
header('Content-Type: application/json');
require_once 'functions.php';

$response = ['success' => false];

try {
    $historyFile = 'url_history.json';
    file_put_contents($historyFile, json_encode([]));
    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>