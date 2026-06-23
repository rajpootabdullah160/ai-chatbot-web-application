<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Redirect user to dashboard if they are already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize raw user input strings safely
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // 1. Username Regex Validation Check
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $errors[] = "Username must be 4-20 characters (letters, numbers, and underscores only).";
    }

    // 2. Email Validation Filter Check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid, standard email address format.";
    }

    // 3. Password Complexity Engine Check
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}/', $password)) {
        $errors[] = "Password requires a minimum of 8 characters, with 1 uppercase letter, 1 lowercase letter, and 1 number.";
    }

    // 4. Duplicate Record Database Integrity Verification
    if (empty($errors)) {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $errors[] = "That username or email address is already registered.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database pipeline connection error: " . $e->getMessage();
        }
    }

    // 5. Execution State: Register User Safely
    if (empty($errors)) {
        // Securely hash the text password string using the standard BCRYPT algorithm
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->execute([$username, $email, $hashed_password]);
            
            $success_message = "Registration successful! Redirecting to login page...";
            header("refresh:2;url=login.php");
        } catch (PDOException $e) {
            $errors[] = "Account provisioning failure: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - AI Chatbot</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern Dual Panel Matrix Override Styles */
        body.auth-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            background: #090d16;
            overflow: hidden;
        }

        /* Welcome Lounge Brand Panel Styling */
        .welcome-brand-panel {
            background: radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.2), transparent 45vw),
                        radial-gradient(circle at 80% 70%, rgba(168, 85, 247, 0.15), transparent 45vw),
                        #0b0f19;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            position: relative;
        }

        .welcome-brand-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(rgba(11, 15, 25, 0.3), rgba(11, 15, 25, 0.7));
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            max-width: 520px;
            animation: fadeIn 0.8s ease;
        }

        .brand-logo-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            display: inline-block;
            filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.4));
        }

        .brand-content h1 {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .brand-content h1 span {
            background: linear-gradient(to right, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-content p {
            color: #9ca3af;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            background: rgba(255, 255, 255, 0.02);
            padding: 1rem;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.04);
            transform: translateX(5px);
        }

        .feature-item i {
            font-size: 1.2rem;
            color: #6366f1;
        }

        .feature-item span {
            font-size: 0.95rem;
            color: #e5e7eb;
            font-weight: 500;
        }

        /* Access Form Portal Box Styling */
        .form-portal-panel {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background: radial-gradient(circle at top right, rgba(168, 85, 247, 0.05), transparent 30vw);
        }

        .auth-card {
            margin: 0;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        /* Responsive Breakpoint Matrix */
        @media (max-width: 968px) {
            body.auth-body {
                grid-template-columns: 1fr;
            }
            .welcome-brand-panel {
                display: none; /* Hide brand panel on smaller viewports */
            }
        }
    </style>
</head>
<body class="auth-body">

    <!-- LEFT COLUMN: Ambient Lounge Welcome Branding Interface -->
    <div class="welcome-brand-panel">
        <div class="brand-content">
            <div class="brand-logo-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h1>Welcome to your<br><span>Intelligent Workspace</span></h1>
            <p>Unlock the power of conversational AI. Start your journey today to seamlessly analyze datasets, automate logic, or brainstorm creative conceptual content outlines.</p>
            
            <div class="feature-item">
                <i class="fas fa-bolt"></i>
                <span>Powered by lightning-fast Gemini 2.0 Flash architecture</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                <span>Secure BCRYPT verification hashing algorithms</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-history"></i>
                <span>Persistent chat transaction matrix memory logs</span>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Secure Account Creation Access Portal -->
    <div class="form-portal-panel">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="auth-subtitle">Fill in your information to instantiate profile access</p>

            <!-- PHP Dynamic Alert Box Notification Interface Layer -->
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <!-- Frontend Form Layout -->
            <form action="register.php" method="POST" id="registrationForm">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="regUsername" placeholder="Username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="regEmail" placeholder="Email Address" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="regPassword" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-primary">Register Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <!-- Frontend Form Handling Script Pipeline -->
    <script src="../assets/js/register-validation.js"></script>
</body>
</html>