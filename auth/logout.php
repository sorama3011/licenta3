<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Log the logout action if user is logged in
if (isset($_SESSION['user_id'])) {
    logAction($conn, 'logout', 'Deconectare utilizator', $_SESSION['user_id']);
}

// Remove remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Delete token from database
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("DELETE FROM sesiuni WHERE id = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    // Delete cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
$_SESSION = array();
session_destroy();

// Initialize response array
$response = [
    'success' => true,
    'message' => 'Te-ai deconectat cu succes!',
    'redirect' => 'index.html'
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);