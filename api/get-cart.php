<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'items' => [],
    'summary' => [
        'subtotal' => 0,
        'shipping' => 0,
        'discount' => 0,
        'total' => 0
    ]
];

try {
    // Get user ID or session ID
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    // Get cart items
    $stmt = $conn->prepare("
        SELECT c.id, c.produs_id, c.cantitate, 
               p.nume, p.pret, p.pret_redus, p.cantitate as greutate, p.imagine
        FROM cos_cumparaturi c
        JOIN produse p ON c.produs_id = p.id
        WHERE c.utilizator_id = :user_id OR (:user_id IS NULL AND c.sesiune_id = :session_id)
        ORDER BY c.data_adaugare DESC
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':session_id', $sessionId);
    $stmt->execute();
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $subtotal = 0;
    $cartItems = [];
    
    foreach ($items as $item) {
        $price = $item['pret_redus'] ? $item['pret_redus'] : $item['pret'];
        $itemTotal = $price * $item['cantitate'];
        $subtotal += $itemTotal;
        
        $cartItems[] = [
            'id' => $item['id'],
            'productId' => $item['produs_id'],
            'name' => $item['nume'],
            'price' => $price,
            'originalPrice' => $item['pret'],
            'quantity' => $item['cantitate'],
            'weight' => $item['greutate'],
            'image' => $item['imagine'],
            'total' => $itemTotal
        ];
    }
    
    // Calculate shipping
    $shipping = $subtotal >= 150 ? 0 : 15;
    
    // Apply discount if voucher is set in session
    $discount = 0;
    if (isset($_SESSION['voucher']) && $subtotal > 0) {
        $voucherId = $_SESSION['voucher']['id'];
        
        $stmt = $conn->prepare("
            SELECT * FROM vouchere 
            WHERE id = :id 
            AND activ = 1 
            AND data_inceput <= CURDATE() 
            AND data_expirare >= CURDATE()
            AND (utilizari_maxime IS NULL OR utilizari_curente < utilizari_maxime)
            AND (valoare_minima_comanda IS NULL OR valoare_minima_comanda <= :subtotal)
        ");
        $stmt->bindParam(':id', $voucherId);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->execute();
        
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($voucher) {
            if ($voucher['tip_discount'] === 'procent') {
                $discount = $subtotal * ($voucher['discount'] / 100);
            } else {
                $discount = min($voucher['discount'], $subtotal);
            }
            
            $response['voucher'] = [
                'code' => $voucher['cod'],
                'discount' => $voucher['discount'],
                'type' => $voucher['tip_discount']
            ];
        } else {
            // Voucher is no longer valid
            unset($_SESSION['voucher']);
        }
    }
    
    // Calculate total
    $total = $subtotal - $discount + $shipping;
    
    $response['success'] = true;
    $response['items'] = $cartItems;
    $response['summary'] = [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'discount' => $discount,
        'total' => $total
    ];
    $response['itemCount'] = count($cartItems);
    $response['totalQuantity'] = array_sum(array_column($cartItems, 'quantity'));
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);