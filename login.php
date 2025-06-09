<?php 
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username_email = trim(htmlspecialchars($_POST['username_email']));
    $password = $_POST['password'];

    if (empty($username_email)) $errors[] = "Username/Email is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$username_email, $username_email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid credentials";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Lost & Found</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card card">
            <div class="auth-header">
                <a href="index.php" class="logo">
                    <span class="logo-icon">🔍</span> Campus Lost & Found
                </a>
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username_email" class="form-label">Email or Username</label>
                    <div class="input-with-icon">
                        <span class="input-icon">👤</span>
                        <input type="text" id="username_email" name="username_email" class="form-control" required value="<?= htmlspecialchars($_POST['username_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="password-header">
                        <label for="password" class="form-label">Password</label>
                        <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-with-icon password-input">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="password-toggle" onclick="togglePassword()">👁</span>
                    </div>
                </div>

                <div class="remember-me">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                </div>

                <button type="submit" name="login" class="btn btn-full">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById("password");
            pwd.type = pwd.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
