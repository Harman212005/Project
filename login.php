<?php
require 'connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $errors = [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username or email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Proceed if no validation errors
    if (empty($errors)) {
        // Prepare and execute SQL query to fetch user
        $sql = "SELECT user_id, username, password, role FROM users WHERE username = :username OR email = :username LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Store user info in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'Admin') {
                    header('Location: admin_dashboard.php');
                    exit;
                } else if ($user['role'] === 'Registered User') {
                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = "Invalid role assigned to the user. Contact support.";
                }
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pokémon InfoHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Login to Pokémon InfoHub</h1>
    </header>

    <main>
        <form action="login.php" method="post">
            <label for="username">Username or Email:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <button type="submit">Login</button>
        </form>

        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p style="color: red;"><?= htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </main>
</body>
</html>
