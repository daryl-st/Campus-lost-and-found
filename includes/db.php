<?php
// includes/db.php

$host = "localhost";
$dbname = "campus_lost_found";
$username = "campus"; // Change if needed
$password = "campuslost";     // Change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>