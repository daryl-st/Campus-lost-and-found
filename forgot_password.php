<?php
session_start();
require 'includes/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = trim(htmlspecialchars($_POST['email']));
    
    if (empty($email)) {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database (you'll need to create this table)
                try {
                    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) 
                                          ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
                    $stmt->execute([$email, $reset_token, $expires_at, $reset_token, $expires_at]);
                    
                    // In a real application, you would send an email here
                    // For demo purposes, we'll just show the reset link
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token;
                    $message = "Password reset instructions have been sent to your email. For demo purposes, here's your reset link: <a href='$reset_link'>Reset Password</a>";
                    
                } catch (PDOException $e) {
                    // Create the table if it doesn't exist
                    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(255) NOT NULL,
                        token VARCHAR(255) NOT NULL,
                        expires_at DATETIME NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_email (email)
                    )");
                    
                    // Try again
                    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$email, $reset_token, $expires_at]);
                    
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token;
                    $message = "Password reset instructions have been sent to your email. For demo purposes, here's your reset link: <a href='$reset_link'>Reset Password</a>";
                }
            } else {
                $message = "If an account with that email exists, you will receive password reset instructions.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Campus Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
        }

        .welcome-text {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #718096;
            font-size: 0.95rem;
        }

        .error-msg {
            background: #fed7d7;
            color: #c53030;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 4px solid #e53e3e;
        }

        .success-msg {
            background: #c6f6d5;
            color: #2f855a;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 4px solid #38a169;
        }

        .success-msg a {
            color: #2f855a;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .auth-footer p {
            color: #718096;
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-footer a:hover {
            color: #764ba2;
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .home-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .forgot-container {
                padding: 30px 25px;
            }
            
            .home-link {
                position: static;
                display: inline-block;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-link">← Back to Home</a>
    
    <div class="forgot-container">
        <div class="logo-section">
            <a href="index.php" class="logo">🔍 Campus Lost & Found</a>
            <h1 class="welcome-text">Forgot Password?</h1>
            <p class="subtitle">Enter your email to reset your password</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success-msg">
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" 
                       placeholder="Enter your email address" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <button type="submit" name="reset_password" class="submit-btn">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <p>Remember your password? <a href="login.php">Sign in here</a></p>
            <p>Don't have an account? <a href="register.php">Create one</a></p>
        </div>
    </div>
</body>
</html>
