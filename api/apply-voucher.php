<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'discount' => 0,
    'total' => 0
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get voucher code
    $voucherCode = trim($_POST['code'] ?? '');
    
    // Validate input
    if (empty($voucherCode)) {
        $response['message'] = 'Codul voucher este obligatoriu.';
    } else {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Trebuie să fiți autentificat pentru a folosi vouchere.';
            } else {
                $userId = $_SESSION['user_id'];
                
                // Calculate cart subtotal
                $subtotal = 0;
                
                if (isset($_POST['subtotal'])) {
                    $subtotal = floatval($_POST['subtotal']);
                } else {
                    // Get cart items to calculate subtotal
                    $sessionId = session_id();
                    
                    $stmt = $conn->prepare("
                        SELECT c.cantitate, p.pret, p.pret_redus
                        FROM cos_cumparaturi c
                        JOIN produse p ON c.produs_id = p.id
                        WHERE c.utilizator_id = :user_id OR (:user_id IS NULL AND c.sesiune_id = :session_id)
                    ");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':session_id', $sessionId);
                    $stmt->execute();
                    
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($items as $item) {
                        $price = $item['pret_redus'] ? $item['pret_redus'] : $item['pret'];
                        $subtotal += $price * $item['cantitate'];
                    }
                }
                
                // Check if voucher exists and is valid
                $stmt = $conn->prepare("
                    SELECT v.* 
                    FROM vouchere v
                    LEFT JOIN vouchere_utilizatori vu ON v.id = vu.voucher_id AND vu.utilizator_id = :user_id
                    WHERE v.cod = :code 
                    AND v.activ = 1 
                    AND v.data_inceput <= CURDATE() 
                    AND v.data_expirare >= CURDATE()
                    AND (v.utilizari_maxime IS NULL OR v.utilizari_curente < v.utilizari_maxime)
                    AND (v.valoare_minima_comanda IS NULL OR v.valoare_minima_comanda <= :subtotal)
                    AND (vu.id IS NULL OR vu.folosit = 0)
                ");
                $stmt->bindParam(':code', $voucherCode);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':subtotal', $subtotal);
                $stmt->execute();
                
                $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$voucher) {
                    $response['message'] = 'Codul voucher nu este valid sau a expirat.';
                } else {
                    // Calculate discount
                    if ($voucher['tip_discount'] === 'procent') {
                        $discount = $subtotal * ($voucher['discount'] / 100);
                    } else {
                        $discount = min($voucher['discount'], $subtotal);
                    }
                    
                    // Calculate shipping
                    $shipping = ($subtotal - $discount) >= 150 ? 0 : 15;
                    
                    // Calculate total
                    $total = $subtotal - $discount + $shipping;
                    
                    // Store voucher in session
                    $_SESSION['voucher'] = [
                        'id' => $voucher['id'],
                        'code' => $voucher['cod'],
                        'discount' => $voucher['discount'],
                        'type' => $voucher['tip_discount']
                    ];
                    
                    $response['success'] = true;
                    $response['message'] = 'Voucher aplicat cu succes!';
                    $response['discount'] = $discount;
                    $response['total'] = $total;
                    $response['shipping'] = $shipping;
                    $response['voucher'] = [
                        'code' => $voucher['cod'],
                        'discount' => $voucher['discount'],
                        'type' => $voucher['tip_discount']
                    ];
                    
                    // Log the action
                    logAction($conn, 'apply_voucher', 'Aplicare voucher: ' . $voucherCode, $userId);
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