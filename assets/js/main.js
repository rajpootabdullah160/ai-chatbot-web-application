document.addEventListener("DOMContentLoaded", () => {
    const chatForm = document.getElementById("chatForm");
    const userInput = document.getElementById("userInput");
    const chatBox = document.getElementById("chatBox");
    const historyList = document.getElementById("historyList");
    const clearHistoryBtn = document.getElementById("clearHistoryBtn");
    const welcomeHero = document.getElementById("welcomeHero");

    // Responsive Mobile Drawer Navigation Toggle Elements
    const menuToggle = document.getElementById("menuToggle");
    const closeSidebar = document.getElementById("closeSidebar");
    const sidebar = document.getElementById("sidebar");

    if (menuToggle && sidebar && closeSidebar) {
        menuToggle.addEventListener("click", () => sidebar.classList.add("active"));
        closeSidebar.addEventListener("click", () => sidebar.classList.remove("active"));
    }

    // Auto Scroll Execution Layer Utility Tool
    const scrollToBottom = () => {
        chatBox.scrollTop = chatBox.scrollHeight;
    };
    scrollToBottom();

    // Primary Asynchronous Submission Handling API Stream Execution
    chatForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const message = userInput.value.trim();
        if (!message) return;

        // Eliminate landing UI hero view card if message flows active
        if (welcomeHero) welcomeHero.style.display = "none";

        // Append User Bubble View immediately
        appendMessage(message, "user", formatAMPM(new Date()));
        userInput.value = "";
        
        // Render Async Skeleton Loader Indicator on screen array
        const loaderId = appendTypingLoader();
        scrollToBottom();

try {
    // Asynchronous endpoint querying configuration
    const response = await fetch("api/chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" }, // Fixed syntax here
        body: JSON.stringify({ message: message })
    });
            const data = await response.json();
            removeTypingLoader(loaderId);

            if (data.success) {
                appendMessage(data.bot_reply, "bot", data.timestamp);
                updateSidebarHistory(data.user_message);
            } else {
                appendMessage("System Alert Error: " + data.error, "bot", formatAMPM(new Date()));
            }
        } catch (err) {
            removeTypingLoader(loaderId);
            appendMessage("Critical network handshake dropped. Please verify backend configurations.", "bot", formatAMPM(new Date()));
        }
        scrollToBottom();
    });

    // Clear Logging History Stream Event Actions Matrix Mapping
    clearHistoryBtn.addEventListener("click", async () => {
        if (!confirm("Are you sure you want to permanently delete all chat history records?")) return;
        
        const formData = new FormData();
        formData.append("action", "clear_history");

        try {
            const res = await fetch("api/chat.php", { method: "POST", body: formData });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert("Error clearing historical traces: " + data.error);
            }
        } catch (e) {
            alert("Fatal network connection issue during deletion mapping.");
        }
    });

    // Subroutine helper logic engines
    function appendMessage(text, sender, timestamp) {
        const wrapper = document.createElement("div");
        wrapper.classList.add("message-wrapper", sender);
        
        // Escape special HTML elements to protect the ecosystem layer from XSS injections
        const cleanText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        const formattedText = sender === 'bot' ? cleanText.replace(/\n/g, "<br>") : cleanText;

        wrapper.innerHTML = `
            <div class="message-bubble">
                <p>${formattedText}</p>
                <span class="timestamp">${timestamp}</span>
            </div>
        `;
        chatBox.appendChild(wrapper);
    }

    function appendTypingLoader() {
        const id = "loader_" + Date.now();
        const wrapper = document.createElement("div");
        wrapper.classList.add("message-wrapper", "bot");
        wrapper.setAttribute("id", id);
        wrapper.innerHTML = `
            <div class="message-bubble">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        chatBox.appendChild(wrapper);
        return id;
    }

    function removeTypingLoader(id) {
        const targetElement = document.getElementById(id);
        if (targetElement) targetElement.remove();
    }

    function updateSidebarHistory(msg) {
        const emptyText = historyList.querySelector(".empty-text");
        if (emptyText) emptyText.remove();

        const item = document.createElement("div");
        item.classList.add("history-item");
        item.innerHTML = `
            <i class="far fa-comment-alt"></i>
            <div class="msg-preview">
                <p class="u-msg">${msg.substring(0, 28)}...</p>
            </div>
        `;
        historyList.insertBefore(item, historyList.firstChild);
    }

    function formatAMPM(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0'+minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }
});