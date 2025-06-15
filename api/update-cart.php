<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cartCount' => 0
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get cart item data
    $cartItemId = intval($_POST['cartItemId'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    // Validate input
    if ($cartItemId <= 0) {
        $response['message'] = 'ID articol coș invalid.';
    } elseif ($quantity < 0) {
        $response['message'] = 'Cantitate invalidă.';
    } else {
        try {
            // Get user ID or session ID
            $userId = $_SESSION['user_id'] ?? null;
            $sessionId = session_id();
            
            // Check if cart item exists and belongs to user
            $stmt = $conn->prepare("
                SELECT c.*, p.stoc 
                FROM cos_cumparaturi c
                JOIN produse p ON c.produs_id = p.id
                WHERE c.id = :id 
                AND (c.utilizator_id = :user_id OR (:user_id IS NULL AND c.sesiune_id = :session_id))
            ");
            $stmt->bindParam(':id', $cartItemId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
            
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cartItem) {
                $response['message'] = 'Articolul nu există în coșul dumneavoastră.';
            } elseif ($quantity > 0 && $quantity > $cartItem['stoc']) {
                $response['message'] = 'Stoc insuficient. Stoc disponibil: ' . $cartItem['stoc'];
            } else {
                if ($quantity === 0) {
                    // Remove item from cart
                    $stmt = $conn->prepare("DELETE FROM cos_cumparaturi WHERE id = :id");
                    $stmt->bindParam(':id', $cartItemId);
                    $stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Produsul a fost eliminat din coș.';
                } else {
                    // Update quantity
                    $stmt = $conn->prepare("
                        UPDATE cos_cumparaturi 
                        SET cantitate = :quantity, data_adaugare = NOW() 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':quantity', $quantity);
                    $stmt->bindParam(':id', $cartItemId);
                    $stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Cantitatea a fost actualizată.';
                }
                
                // Get cart count
                $stmt = $conn->prepare("
                    SELECT SUM(cantitate) as total FROM cos_cumparaturi 
                    WHERE utilizator_id = :user_id OR (:user_id IS NULL AND sesiune_id = :session_id)
                ");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':session_id', $sessionId);
                $stmt->execute();
                
                $cartTotal = $stmt->fetch(PDO::FETCH_ASSOC);
                $response['cartCount'] = intval($cartTotal['total'] ?? 0);
                
                // Log the action
                $actionDesc = $quantity === 0 
                    ? 'Eliminare produs din coș: ID ' . $cartItem['produs_id']
                    : 'Actualizare cantitate în coș: ID ' . $cartItem['produs_id'] . ', Cantitate ' . $quantity;
                
                logAction($conn, 'update_cart', $actionDesc, $userId);
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);