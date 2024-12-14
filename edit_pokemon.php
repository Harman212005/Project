<?php
require 'connect.php';
require 'auth_admin.php'; // Add the semicolon here

// Check if an ID is provided for editing
if (isset($_GET['id'])) {
    $pokemon_id = $_GET['id'];

    // Fetch the Pokémon details from the database
    $stmt = $db->prepare("SELECT * FROM pokemon WHERE id = :id");
    $stmt->bindValue(':id', $pokemon_id);
    $stmt->execute();

    $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);

    // If Pokémon doesn't exist, redirect to the dashboard
    if (!$pokemon) {
        header('Location: admin_dashboard.php');
        exit;
    }

    // Fetch the image associated with this Pokémon from the images table
    $stmt_image = $db->prepare("SELECT * FROM images WHERE page_id = :page_id");
    $stmt_image->bindValue(':page_id', $pokemon_id);
    $stmt_image->execute();
    $image = $stmt_image->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission for updating the Pokémon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = $_POST['type']; // Dropdown value for type
    $hitpoints = $_POST['hitpoints'];
    $attack = $_POST['attack'];
    $defense = $_POST['defense'];
    
    // Handle image upload
    $image_filename = null; // Set to null by default

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_path = 'uploads/' . basename($image_name);
        
        // Move uploaded image to the 'uploads' folder
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // Insert the new image into the images table
            $stmt = $db->prepare("INSERT INTO images (page_id, filename, created_at) VALUES (:page_id, :filename, NOW())");
            $stmt->execute([
                ':page_id' => $pokemon_id,
                ':filename' => $image_path
            ]);
            $image_filename = $image_path; // Update with new image path
        }
    }

    // Update the Pokémon in the database
    $stmt = $db->prepare("UPDATE pokemon 
                          SET name = :name, description = :description, type = :type, 
                              hitpoints = :hitpoints, attack = :attack, defense = :defense
                          WHERE id = :id");

    $stmt->execute([
        ':id' => $pokemon_id,
        ':name' => $name,
        ':description' => $description,
        ':type' => $type,
        ':hitpoints' => $hitpoints,
        ':attack' => $attack,
        ':defense' => $defense
    ]);

    // Redirect back to the admin dashboard after updating
    header('Location: admin_dashboard.php');
    exit;
}

// Handle image deletion (if the user opts to delete the image)
if (isset($_GET['delete_image']) && !empty($image)) {
    // Delete the image file from the 'uploads' folder
    if (file_exists($image['filename'])) {
        unlink($image['filename']); // Delete the image file
    }
    
    // Remove the image from the 'images' table
    $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
    $stmt->bindValue(':id', $image['id']);
    $stmt->execute();

    // Redirect back to the edit page after deletion
    header('Location: edit_pokemon.php?id=' . $pokemon_id); 
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pokémon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input, textarea, select, button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background-color: #218838;
        }
        .image-preview {
            margin-top: 10px;
            text-align: center;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
        }
        .image-preview a {
            display: block;
            margin-top: 10px;
            color: red;
            text-decoration: none;
        }
        .image-preview a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Pokémon</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($pokemon['name']); ?>" placeholder="Name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" placeholder="Description" rows="4" required><?= htmlspecialchars($pokemon['description']); ?></textarea>

            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="<?= htmlspecialchars($pokemon['type']); ?>" selected><?= htmlspecialchars($pokemon['type']); ?></option>
                <option value="Fire">Fire</option>
                <option value="Water">Water</option>
                <option value="Grass">Grass</option>
                <option value="Electric">Electric</option>
                <option value="Rock">Rock</option>
                <option value="Psychic">Psychic</option>
                <option value="Ice">Ice</option>
                <option value="Dragon">Dragon</option>
                <option value="Dark">Dark</option>
                <option value="Fairy">Fairy</option>
                <option value="Normal">Normal</option>
                <option value="Flying">Flying</option>
                <option value="Bug">Bug</option>
                <option value="Poison">Poison</option>
                <option value="Ground">Ground</option>
                <option value="Steel">Steel</option>
                <option value="Fighting">Fighting</option>
                <option value="Ghost">Ghost</option>
            </select>

            <label for="hitpoints">Hitpoints:</label>
            <input type="number" id="hitpoints" name="hitpoints" value="<?= htmlspecialchars($pokemon['hitpoints']); ?>" placeholder="Hitpoints" required>

            <label for="attack">Attack:</label>
            <input type="number" id="attack" name="attack" value="<?= htmlspecialchars($pokemon['attack']); ?>" placeholder="Attack" required>

            <label for="defense">Defense:</label>
            <input type="number" id="defense" name="defense" value="<?= htmlspecialchars($pokemon['defense']); ?>" placeholder="Defense" required>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image">

            <!-- Image Preview and Delete -->
            <?php if (!empty($image)): ?>
                <div class="image-preview">
                    <img src="<?= htmlspecialchars($image['filename']); ?>" alt="Pokemon Image Preview">
                    <a href="edit_pokemon.php?id=<?= $pokemon_id; ?>&delete_image=1" onclick="return confirm('Are you sure you want to delete this image?')">Delete Image</a>
                </div>
            <?php endif; ?>

            <button type="submit">Update Pokémon</button>
        </form>
    </div>
</body>
</html>
