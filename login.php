<?php
session_start();
require 'config/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// user login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-box">
        <h1>Task Manager</h1>
        <p class="auth-sub">Login to your account</p>

        <?php if (isset($_GET['registered'])): ?>
            <p class="success">Account created! You can now login.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="off">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password">
            </div>
            <button type="submit" class="btn-auth">Login</button>
        </form>

        <p class="auth-link">No account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
