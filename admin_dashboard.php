<?php
require 'connect.php';

session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// Handle Search and Sort for Pokémon
$pokemon_search_query = isset($_GET['pokemon_search']) ? trim($_GET['pokemon_search']) : '';
$pokemon_sort_column = isset($_GET['pokemon_sort']) ? $_GET['pokemon_sort'] : 'name';
$pokemon_sort_order = isset($_GET['pokemon_order']) && $_GET['pokemon_order'] === 'desc' ? 'DESC' : 'ASC';

// Validate sort column and order
$allowed_pokemon_columns = ['id', 'name', 'type', 'hitpoints', 'attack', 'defense'];
if (!in_array($pokemon_sort_column, $allowed_pokemon_columns)) {
    $pokemon_sort_column = 'name';
}

// Build Pokémon query
$pokemon_sql = "SELECT * FROM pokemon 
                WHERE name LIKE :search_query 
                ORDER BY $pokemon_sort_column $pokemon_sort_order";
$pokemon_stmt = $db->prepare($pokemon_sql);
$pokemon_stmt->bindValue(':search_query', "%$pokemon_search_query%", PDO::PARAM_STR);
$pokemon_stmt->execute();
$pokemons = $pokemon_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Search and Sort for Users
$user_search_query = isset($_GET['user_search']) ? trim($_GET['user_search']) : '';
$user_sort_column = isset($_GET['user_sort']) ? $_GET['user_sort'] : 'username';
$user_sort_order = isset($_GET['user_order']) && $_GET['user_order'] === 'desc' ? 'DESC' : 'ASC';

// Validate sort column and order
$allowed_user_columns = ['user_id', 'username', 'email', 'role'];
if (!in_array($user_sort_column, $allowed_user_columns)) {
    $user_sort_column = 'username';
}

// Build User query
$user_sql = "SELECT user_id, username, email, role FROM users 
             WHERE username LIKE :search_query 
             ORDER BY $user_sort_column $user_sort_order";
$user_stmt = $db->prepare($user_sql);
$user_stmt->bindValue(':search_query', "%$user_search_query%", PDO::PARAM_STR);
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Pokémon Deletion
if (isset($_GET['delete_pokemon_id'])) {
    $delete_pokemon_id = $_GET['delete_pokemon_id'];
    $delete_sql = "DELETE FROM pokemon WHERE id = :id";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bindValue(':id', $delete_pokemon_id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        header('Location: admin_dashboard.php');
        exit;
    } else {
        echo "<script>alert('Failed to delete Pokémon. Please try again.');</script>";
    }
}

// Handle User Deletion
if (isset($_GET['delete_user_id'])) {
    $delete_user_id = $_GET['delete_user_id'];
    $delete_sql = "DELETE FROM users WHERE user_id = :user_id";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bindValue(':user_id', $delete_user_id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        header('Location: admin_dashboard.php');
        exit;
    } else {
        echo "<script>alert('Failed to delete user. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pokémon InfoHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input[type="text"] {
            padding: 8px;
            width: 200px;
            margin-right: 10px;
        }
        .search-form button {
            padding: 8px 12px;
        }
        .sort-links a {
            text-decoration: none;
            color: #007bff;
            margin-left: 5px;
        }
        .sort-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <div>Welcome, Admin <a href="logout.php">Logout</a></div>
</header>

<main>
    <h1>Admin Dashboard</h1>

    <!-- Pokémon Management Section -->
    <section>
        <h2>Pokémon Management</h2>

        <!-- Search Form -->
        <form class="search-form" method="get" action="admin_dashboard.php">
            <input type="text" name="pokemon_search" value="<?= htmlspecialchars($pokemon_search_query) ?>" placeholder="Search Pokémon">
            <button type="submit">Search</button>
        </form>

        <form method="get" action="add_pokemon.php" style="margin-bottom: 20px;">
        <button type="submit" style="padding: 8px 12px;">Add Pokémon</button>
    </form>

        <table>
            <thead>
                <tr>
                    <th>
                        <a href="?pokemon_sort=id&pokemon_order=<?= $pokemon_sort_column === 'id' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            ID <?= $pokemon_sort_column === 'id' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?pokemon_sort=name&pokemon_order=<?= $pokemon_sort_column === 'name' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Name <?= $pokemon_sort_column === 'name' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>Description</th>
                    <th>
                        <a href="?pokemon_sort=type&pokemon_order=<?= $pokemon_sort_column === 'type' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Type <?= $pokemon_sort_column === 'type' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?pokemon_sort=hitpoints&pokemon_order=<?= $pokemon_sort_column === 'hitpoints' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            HP <?= $pokemon_sort_column === 'hitpoints' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?pokemon_sort=attack&pokemon_order=<?= $pokemon_sort_column === 'attack' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Attack <?= $pokemon_sort_column === 'attack' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?pokemon_sort=defense&pokemon_order=<?= $pokemon_sort_column === 'defense' && $pokemon_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Defense <?= $pokemon_sort_column === 'defense' ? ($pokemon_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pokemons as $pokemon): ?>
                <tr>
                    <td><?= htmlspecialchars($pokemon['id']) ?></td>
                    <td><a href="pokemon_details.php?id=<?= htmlspecialchars($pokemon['id']); ?>"><?= htmlspecialchars($pokemon['name']); ?></a></td>
                    <td><?= htmlspecialchars($pokemon['description']) ?></td>
                    <td><?= htmlspecialchars($pokemon['type']) ?></td>
                    <td><?= htmlspecialchars($pokemon['hitpoints']) ?></td>
                    <td><?= htmlspecialchars($pokemon['attack']) ?></td>
                    <td><?= htmlspecialchars($pokemon['defense']) ?></td>
                    <td>
                        <a href="edit_pokemon.php?id=<?= $pokemon['id'] ?>">Edit</a>
                        <a href="?delete_pokemon_id=<?= $pokemon['id'] ?>" onclick="return confirm('Delete Pokémon?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- User Management Section -->
    <section>
        <h2>User Management</h2>

        <!-- Search Form -->
        <form class="search-form" method="get" action="admin_dashboard.php">
            <input type="text" name="user_search" value="<?= htmlspecialchars($user_search_query) ?>" placeholder="Search Users">
            <button type="submit">Search</button>
        </form>

        <form method="get" action="add_user.php" style="margin-bottom: 20px;">
        <button type="submit" style="padding: 8px 12px;">Add User</button>
    </form>

        <table>
            <thead>
                <tr>
                    <th>
                        <a href="?user_sort=user_id&user_order=<?= $user_sort_column === 'user_id' && $user_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            User ID <?= $user_sort_column === 'user_id' ? ($user_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?user_sort=username&user_order=<?= $user_sort_column === 'username' && $user_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Username <?= $user_sort_column === 'username' ? ($user_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>Email</th>
                    <th>
                        <a href="?user_sort=role&user_order=<?= $user_sort_column === 'role' && $user_sort_order === 'ASC' ? 'desc' : 'asc' ?>">
                            Role <?= $user_sort_column === 'role' ? ($user_sort_order === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="edit_user.php?user_id=<?= $user['user_id'] ?>">Edit</a>
                        <a href="?delete_user_id=<?= $user['user_id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
