// اسکرول خودکار به پایین
function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

// بارگذاری خودکار پیام‌ها (هر 5 ثانیه)
function autoRefresh() {
    const currentPage = window.location.pathname;
    
    if (currentPage.includes('chat.php')) {
        setInterval(function() {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('messagesArea');
                    const oldMessages = document.getElementById('messagesArea');
                    
                    if (newMessages && oldMessages) {
                        const oldScroll = oldMessages.scrollTop;
                        const oldHeight = oldMessages.scrollHeight;
                        
                        oldMessages.innerHTML = newMessages.innerHTML;
                        
                        if (oldHeight - oldScroll < 300) {
                            scrollToBottom();
                        }
                    }
                });
        }, 5000);
    }
}

// اجرا بعد از لود صفحه
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    autoRefresh();
});
