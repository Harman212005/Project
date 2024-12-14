<?php
require 'connect.php';
session_start();

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? null;

// Handle form submission to add new Pokémon
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $hitpoints = $_POST['hitpoints'];
    $attack = $_POST['attack'];
    $defense = $_POST['defense'];

    // Insert the new Pokémon into the database
    $sql = "INSERT INTO pokemon (name, description, type, hitpoints, attack, defense, user_id) 
            VALUES (:name, :description, :type, :hitpoints, :attack, :defense, :user_id)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':hitpoints', $hitpoints);
    $stmt->bindValue(':attack', $attack);
    $stmt->bindValue(':defense', $defense);
    $stmt->bindValue(':user_id', $current_user_id);

    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        echo "<p style='color: red;'>Failed to add new Pokémon.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pokémon</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        Add a New Pokémon
    </header>
    <h1>Add New Pokémon</h1>

    <?php if (!$is_logged_in): ?>
        <p>You must be logged in to add a Pokémon. <a href="login.php">Login</a></p>
    <?php else: ?>
        <form action="add.php" method="post">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>

            <label for="description">Description:</label><br>
            <textarea id="description" name="description" required></textarea><br><br>

            <label for="type">Type:</label><br>
            <input type="text" id="type" name="type" required><br><br>

            <label for="hitpoints">HP:</label><br>
            <input type="number" id="hitpoints" name="hitpoints" required><br><br>

            <label for="attack">Attack:</label><br>
            <input type="number" id="attack" name="attack" required><br><br>

            <label for="defense">Defense:</label><br>
            <input type="number" id="defense" name="defense" required><br><br>

            <button type="submit">Add Pokémon</button>
        </form>
    <?php endif; ?>

    <p><a href="index.php">Back to Pokémon List</a></p>
    </body>
<footer>
    &copy; 2024 Pokémon InfoHub. All Rights Reserved.
</footer>
</html>
