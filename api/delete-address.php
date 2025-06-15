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
    $response['message'] = 'Trebuie să fiți autentificat pentru a șterge o adresă.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get address ID
    $addressId = intval($_POST['addressId'] ?? 0);
    
    // Validate input
    if ($addressId <= 0) {
        $response['message'] = 'ID adresă invalid.';
    } else {
        try {
            $userId = $_SESSION['user_id'];
            
            // Check if address exists and belongs to user
            $stmt = $conn->prepare("
                SELECT * FROM adrese 
                WHERE id = :id AND utilizator_id = :user_id
            ");
            $stmt->bindParam(':id', $addressId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $addressData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$addressData) {
                $response['message'] = 'Adresa nu a fost găsită sau nu vă aparține.';
            } else {
                // Delete address
                $stmt = $conn->prepare("DELETE FROM adrese WHERE id = :id");
                $stmt->bindParam(':id', $addressId);
                $stmt->execute();
                
                $response['success'] = true;
                $response['message'] = 'Adresa a fost ștearsă cu succes!';
                
                // Log the action
                logAction($conn, 'delete_address', 'Ștergere adresă: ID ' . $addressId, $userId);
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);