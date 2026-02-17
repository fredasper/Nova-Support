const CHAT_API_BASE_URL = window.NOVA_API_BASE || (window.location.hostname === 'localhost' ? '/appdib/backend/api' : '/api');

/* ===== CHAT FUNCTIONALITY ===== */

let isSendingMessage = false;

/**
 * Initializes chat with welcome message
 */
function initializeChat() {
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
        return;
    }

    chatWindow.innerHTML = '';
    addMessage("Hello! I'm Nova, your student support assistant. How can I help you today?", 'bot');
}

/**
 * Sends a message from user
 */
async function sendMessage() {
    if (isSendingMessage) {
        return;
    }

    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.querySelector('.send-btn');
    const messageText = (chatInput?.value || '').trim();

    if (!messageText || !chatInput) {
        return;
    }

    addMessage(messageText, 'user');
    chatInput.value = '';

    isSendingMessage = true;
    if (sendBtn) sendBtn.disabled = true;
    chatInput.disabled = true;

    const typingId = addTypingIndicator();

    try {
        const email = localStorage.getItem('studentEmail') || '';
        const response = await fetch(API_BASE_URL + '/chat/send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email,
                message: messageText
            })
        });

        const data = await response.json();
        removeTypingIndicator(typingId);

        if (!response.ok) {
            addMessage(data.error || 'Sorry, I could not process your request right now.', 'bot');
            return;
        }

        addMessage(data.reply || 'Sorry, I could not process your request right now.', 'bot');
    } catch (error) {
        removeTypingIndicator(typingId);
        console.error(error);
        addMessage('Server error. Please try again in a moment.', 'bot');
    } finally {
        isSendingMessage = false;
        if (sendBtn) sendBtn.disabled = false;
        chatInput.disabled = false;
        chatInput.focus();
    }
}

/**
 * Handles Enter key press in chat input
 * @param {KeyboardEvent} event - Keyboard event
 */
function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

/**
 * Adds a message to the chat window
 * @param {string} text - Message text
 * @param {string} sender - 'user' or 'bot'
 */
function addMessage(text, sender) {
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
        return;
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;

    const content = document.createElement('div');
    content.className = 'message-content';
    content.textContent = text;

    messageDiv.appendChild(content);
    chatWindow.appendChild(messageDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function addTypingIndicator() {
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
        return null;
    }

    const messageDiv = document.createElement('div');
    const typingId = `typing-${Date.now()}`;
    messageDiv.className = 'message bot';
    messageDiv.id = typingId;

    const content = document.createElement('div');
    content.className = 'message-content';
    content.textContent = 'Nova is typing...';

    messageDiv.appendChild(content);
    chatWindow.appendChild(messageDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;

    return typingId;
}

function removeTypingIndicator(typingId) {
    if (!typingId) {
        return;
    }

    const typingMessage = document.getElementById(typingId);
    if (typingMessage) {
        typingMessage.remove();
    }
}






