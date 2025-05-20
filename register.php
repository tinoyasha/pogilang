<?php
require 'db.php'; // your DB connection

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];
$linked_id = (int)$_POST['linked_id'];

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, linked_id) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$username, $password, $role, $linked_id]);
    echo "Registration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
