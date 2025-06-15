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
    $response['message'] = 'Trebuie să fiți autentificat pentru a adăuga produse la favorite.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product ID
    $productId = intval($_POST['productId'] ?? 0);
    
    // Validate input
    if ($productId <= 0) {
        $response['message'] = 'ID produs invalid.';
    } else {
        try {
            $userId = $_SESSION['user_id'];
            
            // Check if product exists
            $stmt = $conn->prepare("SELECT id FROM produse WHERE id = :id AND activ = 1");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                $response['message'] = 'Produsul nu există sau nu este disponibil.';
            } else {
                // Check if product is already in favorites
                $stmt = $conn->prepare("
                    SELECT id FROM favorite 
                    WHERE utilizator_id = :user_id AND produs_id = :product_id
                ");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':product_id', $productId);
                $stmt->execute();
                
                if ($stmt->fetch()) {
                    $response['success'] = true;
                    $response['message'] = 'Produsul este deja în lista de favorite.';
                } else {
                    // Add to favorites
                    $stmt = $conn->prepare("
                        INSERT INTO favorite (utilizator_id, produs_id)
                        VALUES (:user_id, :product_id)
                    ");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Produsul a fost adăugat la favorite!';
                    
                    // Log the action
                    logAction($conn, 'add_to_favorites', 'Adăugare produs la favorite: ID ' . $productId, $userId);
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);