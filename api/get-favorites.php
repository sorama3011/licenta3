<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'favorites' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a accesa lista de favorite.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get favorites
    $stmt = $conn->prepare("
        SELECT f.id, f.produs_id, p.nume, p.pret, p.pret_redus, p.cantitate as greutate, p.imagine, p.descriere_scurta,
               c.nume as categorie, r.nume as regiune
        FROM favorite f
        JOIN produse p ON f.produs_id = p.id
        LEFT JOIN categorii c ON p.categorie_id = c.id
        LEFT JOIN regiuni r ON p.regiune_id = r.id
        WHERE f.utilizator_id = :user_id
        ORDER BY f.data_adaugare DESC
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['favorites'] = $favorites;
    $response['count'] = count($favorites);
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);