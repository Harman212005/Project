<?php
require 'connect.php';
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: index.php'); // Redirect if not admin
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':role', $role);

        if ($stmt->execute()) {
            header('Location: admin_dashboard.php'); // Redirect to admin dashboard after success
            exit;
        } else {
            $error = "Failed to add user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin</title>
</head>
<body>
    <h1>Add New User</h1>
    <form method="POST">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="role">Role:</label><br>
        <select name="role" id="role" required>
            <option value="Admin">Admin</option>
            <option value="User">User</option>
        </select><br><br>

        <button type="submit">Add User</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error; ?></p>
    <?php endif; ?>
</body>
</html>
