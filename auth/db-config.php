<?php
// Database connection configuration
$db_host = "localhost";
$db_name = "gusturi_romanesti";
$db_user = "root";
$db_pass = "";

// Establish database connection
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Eroare de conexiune la baza de date: " . $e->getMessage());
}

// Helper function for logging actions
function logAction($conn, $action, $description, $userId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO log_actiuni (utilizator_id, actiune, descriere, ip_address)
        VALUES (:user_id, :action, :description, :ip)
    ");
    
    $stmt->bindParam(":user_id", $userId);
    $stmt->bindParam(":action", $action);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":ip", $ip);
    
    return $stmt->execute();
}

// Function to generate a random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user data
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM utilizatori WHERE id = :id");
    $stmt->bindParam(":id", $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to update user's last login time
function updateLastLogin($conn, $userId) {
    $stmt = $conn->prepare("UPDATE utilizatori SET ultima_autentificare = NOW() WHERE id = :id");
    $stmt->bindParam(":id", $userId);
    return $stmt->execute();
}

// Function to add loyalty points
function addLoyaltyPoints($conn, $userId, $points, $description, $referenceId = null) {
    // First update the user's total points
    $stmt = $conn->prepare("
        UPDATE utilizatori 
        SET puncte_fidelitate = puncte_fidelitate + :points 
        WHERE id = :user_id
    ");
    $stmt->bindParam(":points", $points);
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    
    // Then log the points transaction
    $stmt = $conn->prepare("
        INSERT INTO istoric_puncte (utilizator_id, puncte, tip, descriere, referinta_id)
        VALUES (:user_id, :points, 'adaugare', :description, :reference_id)
    ");
    $stmt->bindParam(":user_id", $userId);
    $stmt->bindParam(":points", $points);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":reference_id", $referenceId);
    
    return $stmt->execute();
}

// Function to use loyalty points
function useLoyaltyPoints($conn, $userId, $points, $description, $referenceId = null) {
    // Check if user has enough points
    $stmt = $conn->prepare("SELECT puncte_fidelitate FROM utilizatori WHERE id = :user_id");
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData['puncte_fidelitate'] < $points) {
        return false; // Not enough points
    }
    
    // Update the user's total points
    $stmt = $conn->prepare("
        UPDATE utilizatori 
        SET puncte_fidelitate = puncte_fidelitate - :points 
        WHERE id = :user_id
    ");
    $stmt->bindParam(":points", $points);
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    
    // Log the points transaction
    $stmt = $conn->prepare("
        INSERT INTO istoric_puncte (utilizator_id, puncte, tip, descriere, referinta_id)
        VALUES (:user_id, :points, 'utilizare', :description, :reference_id)
    ");
    $stmt->bindParam(":user_id", $userId);
    $stmt->bindParam(":points", $points);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":reference_id", $referenceId);
    
    return $stmt->execute();
}