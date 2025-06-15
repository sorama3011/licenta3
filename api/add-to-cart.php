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
    // Get product data
    $productId = intval($_POST['productId'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Validate input
    if ($productId <= 0) {
        $response['message'] = 'ID produs invalid.';
    } elseif ($quantity <= 0) {
        $response['message'] = 'Cantitate invalidă.';
    } else {
        try {
            // Check if product exists and is active
            $stmt = $conn->prepare("SELECT id, stoc FROM produse WHERE id = :id AND activ = 1");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $response['message'] = 'Produsul nu există sau nu este disponibil.';
            } elseif ($product['stoc'] < $quantity) {
                $response['message'] = 'Stoc insuficient. Stoc disponibil: ' . $product['stoc'];
            } else {
                // Get user ID or session ID
                $userId = $_SESSION['user_id'] ?? null;
                $sessionId = session_id();
                
                // Check if product is already in cart
                $stmt = $conn->prepare("
                    SELECT id, cantitate FROM cos_cumparaturi 
                    WHERE produs_id = :product_id 
                    AND (utilizator_id = :user_id OR (:user_id IS NULL AND sesiune_id = :session_id))
                ");
                $stmt->bindParam(':product_id', $productId);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':session_id', $sessionId);
                $stmt->execute();
                
                $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($cartItem) {
                    // Update quantity
                    $newQuantity = $cartItem['cantitate'] + $quantity;
                    
                    if ($newQuantity > $product['stoc']) {
                        $response['message'] = 'Stoc insuficient. Stoc disponibil: ' . $product['stoc'];
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE cos_cumparaturi 
                            SET cantitate = :quantity, data_adaugare = NOW() 
                            WHERE id = :id
                        ");
                        $stmt->bindParam(':quantity', $newQuantity);
                        $stmt->bindParam(':id', $cartItem['id']);
                        $stmt->execute();
                        
                        $response['success'] = true;
                        $response['message'] = 'Cantitatea a fost actualizată în coș.';
                    }
                } else {
                    // Add new item to cart
                    $stmt = $conn->prepare("
                        INSERT INTO cos_cumparaturi (utilizator_id, sesiune_id, produs_id, cantitate)
                        VALUES (:user_id, :session_id, :product_id, :quantity)
                    ");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':session_id', $sessionId);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->bindParam(':quantity', $quantity);
                    $stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Produsul a fost adăugat în coș.';
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
                $actionDesc = 'Adăugare produs în coș: ID ' . $productId . ', Cantitate ' . $quantity;
                logAction($conn, 'add_to_cart', $actionDesc, $userId);
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);