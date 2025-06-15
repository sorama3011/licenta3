<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'order' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a accesa detaliile comenzii.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response['message'] = 'ID comandă invalid.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $orderId = intval($_GET['id']);
    
    // Get order details
    $stmt = $conn->prepare("
        SELECT * FROM comenzi
        WHERE id = :order_id AND utilizator_id = :user_id
    ");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $response['message'] = 'Comanda nu a fost găsită sau nu vă aparține.';
    } else {
        // Get order items
        $stmt = $conn->prepare("
            SELECT d.*, p.imagine
            FROM detalii_comenzi d
            LEFT JOIN produse p ON d.produs_id = p.id
            WHERE d.comanda_id = :order_id
        ");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get voucher details if used
        if ($order['voucher_id']) {
            $stmt = $conn->prepare("SELECT cod, discount, tip_discount FROM vouchere WHERE id = :id");
            $stmt->bindParam(':id', $order['voucher_id']);
            $stmt->execute();
            
            $order['voucher'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Format dates
        $order['data_plasare_formatted'] = date('d.m.Y H:i', strtotime($order['data_plasare']));
        $order['data_confirmare_formatted'] = $order['data_confirmare'] ? date('d.m.Y H:i', strtotime($order['data_confirmare'])) : null;
        $order['data_expediere_formatted'] = $order['data_expediere'] ? date('d.m.Y H:i', strtotime($order['data_expediere'])) : null;
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
        
        $response['success'] = true;
        $response['order'] = $order;
        
        // Log the action
        logAction($conn, 'view_order', 'Vizualizare detalii comandă: #' . $order['numar_comanda'], $userId);
    }
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);