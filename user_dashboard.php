<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Display Pokémon browsing and commenting functionalities here
?>
