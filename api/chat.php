<?php
header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

// Authorization Check: Prevent unauthorized API access
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized Access. Please login.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handling dynamic incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ACTION: Clear Chat History
    if (isset($_POST['action']) && $_POST['action'] === 'clear_history') {
        try {
            $delete = $pdo->prepare("DELETE FROM chats WHERE user_id = ?");
            $delete->execute([$user_id]);
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Could not clear history: ' . $e->getMessage()]);
            exit();
        }
    }

    // 2. ACTION: Default Processing of Bot Message Transaction
    $input = json_decode(file_get_contents('php://input'), true);
    $user_message = isset($input['message']) ? trim($input['message']) : '';

    if (empty($user_message)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
        exit();
    }

    // Official Groq Cloud API Chat Completions Endpoint
    $api_url = "https://api.groq.com/openai/v1/chat/completions";

    // Crafting request payload aligned precisely with Groq / OpenAI chat completion standard
    $payload = [
        "model" => GROQ_MODEL,
        "messages" => [
            [
                "role" => "user",
                "content" => $user_message
            ]
        ],
        "temperature" => 0.7
    ];

    // Initialize clean cURL session targeting Groq systems
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    
    // Bypasses local XAMPP SSL handshake glitches safely
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Catch immediate infrastructure drops or local firewall blocks
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        echo json_encode(['success' => false, 'error' => "cURL network loss infrastructure: " . $error_msg]);
        exit();
    }
    curl_close($ch);

    // If Groq responds with an error code (e.g. 401 Invalid Key or 429 Rate Limit)
    if ($http_code !== 200) {
        echo json_encode(['success' => false, 'error' => "Groq API Server responded with code: $http_code. Raw response: " . $response]);
        exit();
    }

    $res_data = json_decode($response, true);
    
    // Parse OpenAI/Groq standard response structure safely
    if (isset($res_data['choices'][0]['message']['content'])) {
        $bot_reply = $res_data['choices'][0]['message']['content'];
        
        // Save conversation into structural logs inside MySQL schema
        try {
            $stmt = $pdo->prepare("INSERT INTO chats (user_id, user_message, bot_reply) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $user_message, $bot_reply]);
            
            // Output successfully formatted response back to frontend (main.js)
            echo json_encode([
                'success' => true,
                'user_message' => $user_message,
                'bot_reply' => $bot_reply,
                'timestamp' => date('h:i A')
            ]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database storage mapping failure: ' . $e->getMessage()]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Unexpected structural parsing data returned from Groq API.']);
        exit();
    }
}
?>