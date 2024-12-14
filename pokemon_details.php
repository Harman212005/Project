<?php
require 'connect.php';
session_start();

// CAPTCHA generation
function generateCaptcha() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_length = 6;
    $captcha_string = '';
    for ($i = 0; $i < $captcha_length; $i++) {
        $captcha_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    $_SESSION['captcha'] = $captcha_string; // Store in session
    return $captcha_string;
}

// Generate CAPTCHA image
function generateCaptchaImage($captcha_string) {
    $width = 200;
    $height = 60;
    $image = imagecreatetruecolor($width, $height);
    $bg_color = imagecolorallocate($image, 255, 255, 255); // White background
    $text_color = imagecolorallocate($image, 0, 0, 0); // Black text
    imagefill($image, 0, 0, $bg_color);
    $font = 'path/to/font.ttf'; // Use a path to a TTF font on your server

    imagettftext($image, 30, 0, 50, 40, $text_color, $font, $captcha_string);
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// Check user role
$user_role = $_SESSION['role'] ?? 'Guest'; // Default to 'Guest' if no role is set
$is_admin = $user_role === 'Admin';

// Get Pokémon ID
$pokemon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Pokémon Details
$sql = "SELECT * FROM pokemon WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':id', $pokemon_id, PDO::PARAM_INT);
$stmt->execute();
$pokemon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pokemon) {
    echo "Pokémon not found.";
    exit;
}

// Handle Comment Deletion (Admin Only)
if ($is_admin && isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']);
    $delete_comment = "DELETE FROM comments WHERE id = :id";
    $stmt = $db->prepare($delete_comment);
    $stmt->bindValue(':id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: pokemon_details.php?id=$pokemon_id");
    exit;
}

// Handle Comment Submission (Non-Admins Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_admin) {
    $user_name = htmlspecialchars($_POST['user_name']);
    $comment_text = htmlspecialchars($_POST['comment_text']);
    $captcha_input = $_POST['captcha_input']; // User input CAPTCHA

    // Validate CAPTCHA
    if ($captcha_input !== $_SESSION['captcha']) {
        echo "CAPTCHA is incorrect. Please try again.";
        // Do not process the comment and let the user correct the CAPTCHA
    } else {
        if (!empty($user_name) && !empty($comment_text)) {
            $insert_comment = "INSERT INTO comments (pokemon_id, user_name, comment_text) VALUES (:pokemon_id, :user_name, :comment_text)";
            $stmt = $db->prepare($insert_comment);
            $stmt->bindValue(':pokemon_id', $pokemon_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            $stmt->bindValue(':comment_text', $comment_text, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            echo "Please fill in all fields.";
        }
    }
}

// Fetch Comments
$fetch_comments = "SELECT * FROM comments WHERE pokemon_id = :pokemon_id ORDER BY created_at DESC";
$stmt = $db->prepare($fetch_comments);
$stmt->bindValue(':pokemon_id', $pokemon_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pokémon Image
$sql_image = "SELECT filename FROM images WHERE page_id = :pokemon_id LIMIT 1";
$stmt_image = $db->prepare($sql_image);
$stmt_image->bindValue(':pokemon_id', $pokemon_id, PDO::PARAM_INT);
$stmt_image->execute();
$image = $stmt_image->fetch(PDO::FETCH_ASSOC);

$filename = $image['filename'] ?? ''; // Use empty string if no image exists

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pokemon['name']); ?> Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* General Reset and Body Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
    max-width: 1200px;
    margin: auto;
}

h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 20px;
}

h2, h3 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 10px;
}

/* Pokémon Information */
p {
    font-size: 1.1rem;
    line-height: 1.5;
    color: #555;
}

strong {
    font-weight: bold;
}

img {
    max-width: 100%;
    height: auto;
    margin: 20px 0;
}

/* Comments Section */
ul {
    list-style-type: none;
    padding: 0;
    margin: 20px 0;
}

ul li {
    background-color: #fff;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

ul li strong {
    color: #007bff;
}

ul li small {
    color: #999;
    font-size: 0.9rem;
    display: block;
    margin-top: 5px;
}

a {
    color: red;
    font-size: 1rem;
    text-decoration: none;
    margin-left: 10px;
}

a:hover {
    text-decoration: underline;
}

/* Form Styling */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
    margin-top: 30px;
}

form input[type="text"], form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
}

form textarea {
    resize: vertical;
    min-height: 100px;
}

form button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
}

form button:hover {
    background-color: #0056b3;
}

/* CAPTCHA Section */
img[alt="CAPTCHA"] {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin: 10px 0;
}

form input[name="captcha_input"] {
    width: 50%;
    display: inline-block;
}

form input[type="submit"] {
    width: 100%;
}
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($pokemon['name']); ?></h1>
    <p><strong>Description:</strong> <?= htmlspecialchars($pokemon['description']); ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($pokemon['type']); ?></p>
    <p><strong>HP:</strong> <?= htmlspecialchars($pokemon['hitpoints']); ?></p>
    <p><strong>Attack:</strong> <?= htmlspecialchars($pokemon['attack']); ?></p>
    <p><strong>Defense:</strong> <?= htmlspecialchars($pokemon['defense']); ?></p>

        <!-- Pokémon Image -->
    <?php if (!empty($filename)): ?>
        <img src="<?= htmlspecialchars($filename); ?>" alt="<?= htmlspecialchars($pokemon['name']); ?> Image" />
    <?php else: ?>
        <p>No image available for this Pokémon.</p>
    <?php endif; ?>

    <!-- Comments Section -->
    <h2>Comments</h2>
    <?php if (!empty($comments)): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?= htmlspecialchars($comment['user_name']); ?>:</strong>
                    <?= htmlspecialchars($comment['comment_text']); ?>
                    <small>(<?= $comment['created_at']; ?>)</small>
                    <?php if ($is_admin): ?>
                        <!-- Admin Delete Option -->
                        <a href="pokemon_details.php?id=<?= $pokemon_id; ?>&delete_comment_id=<?= $comment['id']; ?>">Delete</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet. Be the first to comment!</p>
    <?php endif; ?>

    <?php if (!$is_admin): ?>
        <!-- Comment Form for Non-Admins -->
        <h3>Add a Comment</h3>
        <form method="post">
            <input type="text" name="user_name" placeholder="Your Name" required>
            <br>
            <textarea name="comment_text" placeholder="Your Comment" required></textarea>
            <br>
            <img src="generate_captcha.php" alt="CAPTCHA" width="100" height="40">
            <br>
            <input type="text" name="captcha_input" placeholder="Enter CAPTCHA" required>
            <br>
            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>

