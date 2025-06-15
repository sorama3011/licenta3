<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'orders' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a accesa istoricul comenzilor.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get orders
    $stmt = $conn->prepare("
        SELECT id, numar_comanda, status, total, metoda_plata, status_plata, data_plasare, data_livrare, numar_awb
        FROM comenzi
        WHERE utilizator_id = :user_id
        ORDER BY data_plasare DESC
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order details for each order
    foreach ($orders as &$order) {
        $stmt = $conn->prepare("
            SELECT d.*, p.imagine
            FROM detalii_comenzi d
            LEFT JOIN produse p ON d.produs_id = p.id
            WHERE d.comanda_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order['id']);
        $stmt->execute();
        
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order['item_count'] = count($order['items']);
        $order['total_quantity'] = array_sum(array_column($order['items'], 'cantitate'));
        
        // Format dates
        $order['data_plasare_formatted'] = date('d.m.Y H:i', strtotime($order['data_plasare']));
        $order['data_livrare_formatted'] = $order['data_livrare'] ? date('d.m.Y', strtotime($order['data_livrare'])) : null;
        
        // Translate status
        $statusTranslations = [
            'in_asteptare' => 'În așteptare',
            'confirmata' => 'Confirmată',
            'in_procesare' => 'În procesare',
            'expediata' => 'Expediată',
            'livrata' => 'Livrată',
            'anulata' => 'Anulată'
        ];
        
        $order['status_translated'] = $statusTranslations[$order['status']] ?? $order['status'];
        
        // Translate payment status
        $paymentStatusTranslations = [
            'in_asteptare' => 'În așteptare',
            'platita' => 'Plătită',
            'rambursata' => 'Rambursată',
            'anulata' => 'Anulată'
        ];
        
        $order['status_plata_translated'] = $paymentStatusTranslations[$order['status_plata']] ?? $order['status_plata'];
        
        // Translate payment method
        $paymentMethodTranslations = [
            'card' => 'Card de credit/debit',
            'transfer_bancar' => 'Transfer bancar',
            'ramburs' => 'Plata la livrare'
        ];
        
        $order['metoda_plata_translated'] = $paymentMethodTranslations[$order['metoda_plata']] ?? $order['metoda_plata'];
    }
    
    $response['success'] = true;
    $response['orders'] = $orders;
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);