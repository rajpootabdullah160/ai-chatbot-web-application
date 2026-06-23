<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

// Guarantee route protection wall
check_auth();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Pull dynamic records on user logging history pipeline
try {
    $stmt = $pdo->prepare("SELECT * FROM chats WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    $chat_history = $stmt->fetchAll();
} catch (PDOException $e) {
    $chat_history = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Chatbot</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-robot"></i> AI Chatbot</h2>
                <button class="close-sidebar-btn" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="user-profile">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info">
                    <span class="welcome">Welcome,</span>
                    <span class="username"><?= htmlspecialchars($username) ?></span>
                </div>
            </div>

            <div class="sidebar-menu">
                <h3>Conversation History</h3>

                <div class="history-list" id="historyList">
                    <?php if (empty($chat_history)): ?>
                        <p class="empty-text">No conversation history found.</p>
                    <?php else: ?>
                        <?php foreach($chat_history as $chat): ?>
                            <div class="history-item">
                                <i class="far fa-comment-alt"></i>
                                <div class="msg-preview">
                                    <p class="u-msg">
                                        <?= htmlspecialchars(substr($chat['user_message'], 0, 28)) ?>...
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-footer">
                <button id="clearHistoryBtn" class="btn-danger">
                    <i class="fas fa-trash-alt"></i> Clear History
                </button>

                <a href="auth/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <main class="main-content">

            <header class="chat-header">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="bot-identity">
                    <div class="bot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>

                    <div>
                        <h4>AI Assistant</h4>
                        <span class="status-indicator">
                            <span class="dot"></span> Online
                        </span>
                    </div>
                </div>
            </header>

            <section class="chat-body" id="chatBox">

                <?php if (empty($chat_history)): ?>

                    <div class="welcome-hero" id="welcomeHero">
                        <i class="fas fa-brain hero-icon"></i>

                        <h2>
                            Welcome, <?= htmlspecialchars($username) ?>!
                        </h2>

                        <p>
                            Ask me anything. I'm your AI-powered assistant, ready to help with questions, ideas, learning, and problem-solving.
                        </p>
                    </div>

                <?php else: ?>

                    <?php foreach($chat_history as $chat): ?>

                        <!-- User Message -->
                        <div class="message-wrapper user">
                            <div class="message-bubble">
                                <p><?= htmlspecialchars($chat['user_message']) ?></p>

                                <span class="timestamp">
                                    <?= date('h:i A', strtotime($chat['created_at'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Bot Message -->
                        <div class="message-wrapper bot">
                            <div class="message-bubble">
                                <p><?= nl2br(htmlspecialchars($chat['bot_reply'])) ?></p>

                                <span class="timestamp">
                                    <?= date('h:i A', strtotime($chat['created_at'])) ?>
                                </span>
                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </section>

            <footer class="chat-footer-input">
                <form id="chatForm" class="input-form">

                    <input
                        type="text"
                        id="userInput"
                        placeholder="Type your message..."
                        autocomplete="off"
                        required
                    >

                    <button type="submit" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>

                </form>
            </footer>

        </main>

    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>