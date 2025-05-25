<?php
require_once 'functions.php';

$shortUrl = '';
$error = '';
$history = getUrlHistory();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = $_POST['url'] ?? '';
    
    try {
        // Validate URL
        if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            throw new Exception("Please enter a valid URL (e.g., https://example.com)");
        }
        
        // Shorten URL using external API
        $shortUrl = shortenUrlWithAPI($originalUrl);
        
        // Save to history
        addToUrlHistory($originalUrl, $shortUrl);
        $history = getUrlHistory();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickURL - Free URL Shortener</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-link"></i> QuickURL</h1>
            <p class="tagline">Free URL shortener using public API</p>
        </header>
        
        <main>
            <div class="shortener-card">
                <form id="url-form" method="POST">
                    <div class="input-group">
                        <input type="url" name="url" placeholder="Paste your long URL here..." required>
                        <button type="submit">Shorten</button>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error) ?>
                            <?php if (strpos($error, 'unavailable') !== false): ?>
                                <div class="error-suggestion">
                                    You can still copy and share the original URL.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </form>
                
                <?php if ($shortUrl): ?>
                    <div class="result-container">
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i> Your shortened URL is ready!
                        </div>
                        
                        <div class="result-box">
                            <input type="text" id="short-url" value="<?= htmlspecialchars($shortUrl) ?>" readonly>
                            <button id="copy-btn" class="copy-btn">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                        
                        <div class="share-buttons">
                            <button class="share-btn twitter">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button class="share-btn facebook">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                            <button class="share-btn whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="history-card">
                <div class="history-header">
                    <h3><i class="fas fa-history"></i> Recently Shortened URLs</h3>
                    <button id="clear-history" class="btn clear-btn">
                        <i class="fas fa-trash-alt"></i> Clear
                    </button>
                </div>
                <?php if (empty($history)): ?>
                    <p class="empty-message">No URLs shortened yet</p>
                <?php else: ?>
                    <div class="urls-list">
                        <?php foreach ($history as $item): ?>
                            <div class="url-item">
                                <div class="url-info">
                                    <div class="short-url">
                                        <a href="<?= htmlspecialchars($item['short_url']) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= htmlspecialchars(parse_url($item['short_url'], PHP_URL_HOST) . parse_url($item['short_url'], PHP_URL_PATH)) ?>
                                        </a>
                                    </div>
                                    <div class="original-url" title="<?= htmlspecialchars($item['original_url']) ?>">
                                        <?= htmlspecialchars(truncateUrl($item['original_url'], 50)) ?>
                                    </div>
                                </div>
                                <div class="url-actions">
                                    <button class="btn copy-btn" data-url="<?= htmlspecialchars($item['short_url']) ?>">
                                        <i class="far fa-copy"></i>
                                    </button>
                                    <span class="date"><?= htmlspecialchars($item['date']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>Powered by <a href="https://www.urlshort.dev/" target="_blank">urlshort.dev</a> API</p>
            <p>© <?= date('Y') ?> QuickURL - Free URL Shortener</p>
        </footer>
    </div>
    
    <script src="script.js"></script>
</body>
</html>