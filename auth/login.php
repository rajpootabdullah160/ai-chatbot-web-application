<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity']); // Accepts username or email
    $password = $_POST['password'];

    if (!empty($identity) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$identity, $identity]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent Session Fixation attacks
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid Username/Email or Password.";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cosmic Chat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to resume your workspace</p>
        
        <?php if($error): ?> <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?=$error?></div> <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user-shield"></i>
                <input type="text" name="identity" placeholder="Username or Email" required autocomplete="off">
            </div>
            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-primary">Log In</button>
        </form>
        <p class="auth-footer">New here? <a href="register.php">Create an Account</a></p>
    </div>
</body>
</html>