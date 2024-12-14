<?php
require 'connect.php';
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user details from the database
    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit;
    }

    // Handle form submission for updating user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
        $role = $_POST['role'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($role)) {
            $error = "All fields are required!";
        } else {
            $sql = "UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':role', $role);
            $stmt->bindValue(':user_id', $user_id);

            if ($stmt->execute()) {
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "Failed to update user.";
            }
        }
    }
} else {
    echo "No user ID specified!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
</head>
<body>
    <h1>Edit User</h1>
    <form method="POST">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br><br>

        <label for="role">Role:</label><br>
        <select name="role" id="role" required>
            <option value="Admin" <?= $user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="User" <?= $user['role'] == 'User' ? 'selected' : ''; ?>>User</option>
        </select><br><br>

        <button type="submit">Update User</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error; ?></p>
    <?php endif; ?>
</body>
</html>
