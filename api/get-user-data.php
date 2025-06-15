<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'user' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Utilizatorul nu este autentificat.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get user data
    $stmt = $conn->prepare("
        SELECT id, prenume, nume, email, telefon, puncte_fidelitate, data_inregistrare, ultima_autentificare, newsletter
        FROM utilizatori
        WHERE id = :user_id AND activ = 1
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $response['message'] = 'Utilizatorul nu a fost găsit sau contul este dezactivat.';
    } else {
        // Get user addresses
        $stmt = $conn->prepare("
            SELECT * FROM adrese
            WHERE utilizator_id = :user_id
            ORDER BY implicit DESC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $user['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get order count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total_orders, SUM(total) as total_spent
            FROM comenzi
            WHERE utilizator_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['total_orders'] = intval($orderStats['total_orders'] ?? 0);
        $user['total_spent'] = floatval($orderStats['total_spent'] ?? 0);
        
        // Get favorites count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total_favorites
            FROM favorite
            WHERE utilizator_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $favoritesStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['total_favorites'] = intval($favoritesStats['total_favorites'] ?? 0);
        
        // Format dates
        $user['data_inregistrare_formatted'] = date('d.m.Y', strtotime($user['data_inregistrare']));
        $user['ultima_autentificare_formatted'] = $user['ultima_autentificare'] ? date('d.m.Y H:i', strtotime($user['ultima_autentificare'])) : null;
        
        $response['success'] = true;
        $response['user'] = $user;
    }
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);