<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a gestiona lista de favorite.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get favorite ID
    $favoriteId = intval($_POST['favoriteId'] ?? 0);
    
    // Validate input
    if ($favoriteId <= 0) {
        $response['message'] = 'ID favorit invalid.';
    } else {
        try {
            $userId = $_SESSION['user_id'];
            
            // Check if favorite exists and belongs to user
            $stmt = $conn->prepare("
                SELECT f.*, p.nume 
                FROM favorite f
                JOIN produse p ON f.produs_id = p.id
                WHERE f.id = :id AND f.utilizator_id = :user_id
            ");
            $stmt->bindParam(':id', $favoriteId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $favorite = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$favorite) {
                $response['message'] = 'Produsul nu există în lista dumneavoastră de favorite.';
            } else {
                // Remove from favorites
                $stmt = $conn->prepare("DELETE FROM favorite WHERE id = :id");
                $stmt->bindParam(':id', $favoriteId);
                $stmt->execute();
                
                $response['success'] = true;
                $response['message'] = 'Produsul a fost eliminat din lista de favorite.';
                
                // Log the action
                logAction($conn, 'remove_favorite', 'Eliminare produs din favorite: ' . $favorite['nume'], $userId);
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);