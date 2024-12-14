<?php
require 'connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; // Ensure no further code is executed after the redirect
}

$username = $_SESSION['username'] ?? 'Guest';

// Handle search and sort
$search_query = $_GET['search'] ?? '';
$sort_column = $_GET['sort'] ?? 'id';
$valid_sort_columns = ['id', 'name', 'type', 'hitpoints', 'attack', 'defense'];
if (!in_array($sort_column, $valid_sort_columns)) {
    $sort_column = 'id';
}

// Fetch Pokémon based on search and sort criteria
$sql = "SELECT * FROM pokemon WHERE 
    name LIKE :search_query OR 
    description LIKE :search_query OR 
    type LIKE :search_query 
    ORDER BY $sort_column";
$stmt = $db->prepare($sql);
$stmt->bindValue(':search_query', "%$search_query%");
$stmt->execute();
$pokemons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon InfoHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="header-content">
        <span>Welcome to Pokémon InfoHub</span>
        <div class="auth-links">
        <?php if ($username): ?>
                <p>Hello, <?= htmlspecialchars($username); ?>!</p>
                <a class="logout-button" href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a> | <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
    <h1>Pokémon List</h1>

    <!-- Search Form -->
    <form method="get" action="index.php">
        <input type="text" name="search" placeholder="Search Pokémon..." value="<?= htmlspecialchars($search_query); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Sort Form -->
    <form method="get" action="index.php" style="margin-top: 10px;">
        <label for="sort">Sort by:</label>
        <select name="sort" id="sort">
            <option value="id" <?= $sort_column == 'id' ? 'selected' : ''; ?>>ID</option>
            <option value="name" <?= $sort_column == 'name' ? 'selected' : ''; ?>>Name</option>
            <option value="type" <?= $sort_column == 'type' ? 'selected' : ''; ?>>Type</option>
            <option value="hitpoints" <?= $sort_column == 'hitpoints' ? 'selected' : ''; ?>>HP</option>
            <option value="attack" <?= $sort_column == 'attack' ? 'selected' : ''; ?>>Attack</option>
            <option value="defense" <?= $sort_column == 'defense' ? 'selected' : ''; ?>>Defense</option>
        </select>
        <button type="submit">Sort</button>
    </form>

    <!-- Pokémon Table -->
    <table border="1" cellpadding="10" style="margin-top: 20px;">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Type</th>
        <th>HP</th>
        <th>Attack</th>
        <th>Defense</th>
    </tr>
    <?php if (count($pokemons) > 0): ?>
        <?php foreach ($pokemons as $pokemon): ?>
            <tr>
                <td><?= htmlspecialchars($pokemon['id']); ?></td>
                <td>
                    <a href="pokemon_details.php?id=<?= htmlspecialchars($pokemon['id']); ?>">
                        <?= htmlspecialchars($pokemon['name']); ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($pokemon['description']); ?></td>
                <td><?= htmlspecialchars($pokemon['type']); ?></td>
                <td><?= htmlspecialchars($pokemon['hitpoints']); ?></td>
                <td><?= htmlspecialchars($pokemon['attack']); ?></td>
                <td><?= htmlspecialchars($pokemon['defense']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">No Pokémon found.</td>
        </tr>
    <?php endif; ?>
</table>
</main>
</body>
</html>
