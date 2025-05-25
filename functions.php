<?php
// Storage file for history
$historyFile = 'url_history.json';

function initializeHistory() {
    global $historyFile;
    if (!file_exists($historyFile)) {
        file_put_contents($historyFile, json_encode([]));
    }
}

function getUrlHistory() {
    global $historyFile;
    initializeHistory();
    $data = file_get_contents($historyFile);
    return json_decode($data, true) ?: [];
}

function addToUrlHistory($originalUrl, $shortUrl) {
    global $historyFile;
    $history = getUrlHistory();
    array_unshift($history, [
        'original_url' => $originalUrl,
        'short_url' => $shortUrl,
        'date' => date('M j, Y g:i a')
    ]);
    $history = array_slice($history, 0, 20);
    file_put_contents($historyFile, json_encode($history));
}

function shortenUrlWithAPI($originalUrl) {
    $apiUrl = 'https://api.encurtador.dev/encurtamentos';
    
    // First try with file_get_contents
    $shortUrl = tryFileGetContents($apiUrl, $originalUrl);
    if ($shortUrl) return $shortUrl;
    
    // Fallback to cURL if file_get_contents fails
    $shortUrl = tryCurl($apiUrl, $originalUrl);
    if ($shortUrl) return $shortUrl;
    
    // Final fallback - use a different API
    return tryBackupAPI($originalUrl);
}

function tryFileGetContents($apiUrl, $originalUrl) {
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode(['url' => $originalUrl]),
            'timeout' => 5
        ],
    ];
    
    try {
        $context = stream_context_create($options);
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            $result = json_decode($response, true);
            if (isset($result['urlEncurtada'])) {
                return $result['urlEncurtada'];
            }
        }
    } catch (Exception $e) {
        error_log("File_get_contents failed: " . $e->getMessage());
    }
    return false;
}

function tryCurl($apiUrl, $originalUrl) {
    if (!function_exists('curl_init')) return false;
    
    try {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $originalUrl]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 || $httpCode === 201) {
            $result = json_decode($response, true);
            if (isset($result['urlEncurtada'])) {
                return $result['urlEncurtada'];
            }
        }
    } catch (Exception $e) {
        error_log("cURL failed: " . $e->getMessage());
    }
    return false;
}

function tryBackupAPI($originalUrl) {
    // Fallback to another free API if primary fails
    $backupApis = [
        'https://is.gd/create.php?format=simple&url=' . urlencode($originalUrl),
        'https://tinyurl.com/api-create.php?url=' . urlencode($originalUrl)
    ];
    
    foreach ($backupApis as $apiUrl) {
        try {
            $response = @file_get_contents($apiUrl);
            if ($response !== false && filter_var($response, FILTER_VALIDATE_URL)) {
                return $response;
            }
        } catch (Exception $e) {
            error_log("Backup API failed: " . $e->getMessage());
        }
    }
    
    throw new Exception("All URL shortening services are currently unavailable. Please try again later.");
}

function truncateUrl($url, $length) {
    return strlen($url) > $length ? substr($url, 0, $length) . '...' : $url;
}
?>