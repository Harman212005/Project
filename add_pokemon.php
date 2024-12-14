<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = $_POST['type']; // Dropdown value for type
    $hitpoints = $_POST['hitpoints'];
    $attack = $_POST['attack'];
    $defense = $_POST['defense'];
    $image_uploaded = false;

    try {
        // Begin transaction
        $db->beginTransaction();

        // Insert Pokémon data
        $stmt = $db->prepare("INSERT INTO pokemon (name, description, type, hitpoints, attack, defense) 
                               VALUES (:name, :description, :type, :hitpoints, :attack, :defense)");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':type' => $type,
            ':hitpoints' => $hitpoints,
            ':attack' => $attack,
            ':defense' => $defense,
        ]);

        // Get the ID of the newly created Pokémon
        $pokemon_id = $db->lastInsertId();

        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $image_info = getimagesize($image['tmp_name']);
            
            if ($image_info) {
                $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array(strtolower($image_ext), $allowed_types)) {
                    // Resize the image
                    $new_width = 300; // Example resize width
                    $new_height = 300; // Example resize height
                    $resized_image = imagecreatetruecolor($new_width, $new_height);
                    $original_image = imagecreatefromstring(file_get_contents($image['tmp_name']));

                    imagecopyresampled($resized_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $image_info[0], $image_info[1]);
                    $upload_dir = 'uploads/';
                    $new_filename = $upload_dir . uniqid() . '.' . $image_ext;

                    // Save resized images
                    switch ($image_ext) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($resized_image, $new_filename);
                            break;
                        case 'png':
                            imagepng($resized_image, $new_filename);
                            break;
                        case 'gif':
                            imagegif($resized_image, $new_filename);
                            break;
                    }

                    // Insert image record into database
                    $stmt = $db->prepare("INSERT INTO images (page_id, filename) VALUES (:page_id, :filename)");
                    $stmt->execute([
                        ':page_id' => $pokemon_id,
                        ':filename' => $new_filename,
                    ]);

                    $image_uploaded = true;
                } else {
                    throw new Exception("Invalid image type. Only JPG, PNG, and GIF are allowed.");
                }
            } else {
                throw new Exception("Uploaded file is not a valid image.");
            }
        }

        // Commit transaction
        $db->commit();

        // Redirect to admin dashboard
        header('Location: admin_dashboard.php?success=' . ($image_uploaded ? 'Image and Pokémon added' : 'Pokémon added'));
        exit;

    } catch (Exception $e) {
        // Rollback transaction on failure
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pokémon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #003366;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="text"], input[type="number"], textarea, select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }
        input[type="file"] {
            border: none;
        }
        button {
            padding: 10px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #00509e;
        }
        textarea {
            resize: vertical;
        }
        .success-message {
            background-color: #e0f7fa;
            color: #00796b;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Pokémon</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Name" required>
            <textarea name="description" placeholder="Description" rows="4"></textarea>
            <select name="type" required>
                <option value="" disabled selected>Select Type</option>
                <option value="Normal">Normal</option>
                <option value="Fire">Fire</option>
                <option value="Water">Water</option>
                <option value="Electric">Electric</option>
                <option value="Grass">Grass</option>
                <option value="Ice">Ice</option>
                <option value="Fighting">Fighting</option>
                <option value="Poison">Poison</option>
                <option value="Ground">Ground</option>
                <option value="Flying">Flying</option>
                <option value="Psychic">Psychic</option>
                <option value="Bug">Bug</option>
                <option value="Rock">Rock</option>
                <option value="Ghost">Ghost</option>
                <option value="Dragon">Dragon</option>
                <option value="Dark">Dark</option>
                <option value="Steel">Steel</option>
                <option value="Fairy">Fairy</option>
            </select>
            <input type="number" name="hitpoints" placeholder="Hitpoints" required>
            <input type="number" name="attack" placeholder="Attack" required>
            <input type="number" name="defense" placeholder="Defense" required>
            <input type="file" name="image" accept="image/*">
            <button type="submit">Add Pokémon</button>
        </form>
    </div>
</body>
</html>
