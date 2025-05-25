document.addEventListener('DOMContentLoaded', function() {
    // Copy short URL to clipboard
    const copyBtn = document.getElementById('copy-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const shortUrl = document.getElementById('short-url');
            shortUrl.select();
            shortUrl.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(shortUrl.value).then(function() {
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                copyBtn.classList.add('copied');
                
                setTimeout(function() {
                    copyBtn.innerHTML = '<i class="far fa-copy"></i> Copy';
                    copyBtn.classList.remove('copied');
                }, 2000);
            });
        });
    }
    
    // Share buttons functionality
    const shareButtons = document.querySelectorAll('.share-btn');
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const shortUrl = document.getElementById('short-url')?.value || '';
            const text = 'Check out this link: ';
            
            if (this.classList.contains('twitter')) {
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text + shortUrl)}`, '_blank');
            } else if (this.classList.contains('facebook')) {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shortUrl)}`, '_blank');
            } else if (this.classList.contains('whatsapp')) {
                window.open(`https://wa.me/?text=${encodeURIComponent(text + shortUrl)}`, '_blank');
            }
        });
    });
    
    // Copy buttons in history
    document.querySelectorAll('.url-actions .btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (!url) return;
            
            navigator.clipboard.writeText(url).then(function() {
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                
                icon.className = 'fas fa-check';
                button.classList.add('copied');
                
                setTimeout(function() {
                    icon.className = originalClass;
                    button.classList.remove('copied');
                }, 2000);
            });
        });
    });
    
    // Auto-focus URL input on page load
    const urlInput = document.querySelector('input[name="url"]');
    if (urlInput) {
        urlInput.focus();
    }

    // Clear history button
    const clearHistoryBtn = document.getElementById('clear-history');
    if (clearHistoryBtn) {
        clearHistoryBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear your history?')) {
                fetch('clear_history.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    }
    
    // Enhanced copy functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const url = this.getAttribute('data-url') || 
                        document.getElementById('short-url')?.value;
            
            if (!url) return;
            
            try {
                await navigator.clipboard.writeText(url);
                
                // Visual feedback
                const icon = this.querySelector('i');
                const originalIcon = icon.className;
                
                icon.className = 'fas fa-check';
                this.classList.add('copied');
                
                setTimeout(() => {
                    icon.className = originalIcon;
                    this.classList.remove('copied');
                }, 2000);
                
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        });
    });
});