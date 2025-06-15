<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'orderId' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a plasa o comandă.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        $userId = $_SESSION['user_id'];
        
        // Get cart items
        $stmt = $conn->prepare("
            SELECT c.produs_id, c.cantitate, p.nume, p.pret, p.pret_redus, p.stoc
            FROM cos_cumparaturi c
            JOIN produse p ON c.produs_id = p.id
            WHERE c.utilizator_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cartItems)) {
            $response['message'] = 'Coșul dumneavoastră este gol.';
            $conn->rollBack();
        } else {
            // Check stock availability
            $outOfStock = [];
            foreach ($cartItems as $item) {
                if ($item['cantitate'] > $item['stoc']) {
                    $outOfStock[] = $item['nume'];
                }
            }
            
            if (!empty($outOfStock)) {
                $response['message'] = 'Stoc insuficient pentru: ' . implode(', ', $outOfStock);
                $conn->rollBack();
            } else {
                // Get form data
                $shippingName = trim($_POST['shippingName'] ?? '');
                $shippingAddress = trim($_POST['shippingAddress'] ?? '');
                $shippingCity = trim($_POST['shippingCity'] ?? '');
                $shippingCounty = trim($_POST['shippingCounty'] ?? '');
                $shippingZip = trim($_POST['shippingZip'] ?? '');
                $shippingPhone = trim($_POST['shippingPhone'] ?? '');
                
                $billingName = trim($_POST['billingName'] ?? '');
                $billingAddress = trim($_POST['billingAddress'] ?? '');
                $billingCity = trim($_POST['billingCity'] ?? '');
                $billingCounty = trim($_POST['billingCounty'] ?? '');
                $billingZip = trim($_POST['billingZip'] ?? '');
                $billingPhone = trim($_POST['billingPhone'] ?? '');
                
                $paymentMethod = trim($_POST['paymentMethod'] ?? '');
                $notes = trim($_POST['notes'] ?? '');
                
                // Validate required fields
                if (empty($shippingName) || empty($shippingAddress) || empty($shippingCity) || 
                    empty($shippingCounty) || empty($shippingZip) || empty($shippingPhone) ||
                    empty($billingName) || empty($billingAddress) || empty($billingCity) || 
                    empty($billingCounty) || empty($billingZip) || empty($billingPhone) ||
                    empty($paymentMethod)) {
                    
                    $response['message'] = 'Toate câmpurile obligatorii trebuie completate.';
                    $conn->rollBack();
                } else {
                    // Calculate totals
                    $subtotal = 0;
                    foreach ($cartItems as $item) {
                        $price = $item['pret_redus'] ? $item['pret_redus'] : $item['pret'];
                        $subtotal += $price * $item['cantitate'];
                    }
                    
                    // Apply voucher discount if available
                    $discount = 0;
                    $voucherId = null;
                    
                    if (isset($_SESSION['voucher'])) {
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
                            
                            // Update voucher usage count
                            $stmt = $conn->prepare("
                                UPDATE vouchere 
                                SET utilizari_curente = utilizari_curente + 1 
                                WHERE id = :id
                            ");
                            $stmt->bindParam(':id', $voucherId);
                            $stmt->execute();
                            
                            // Mark voucher as used for this user
                            $stmt = $conn->prepare("
                                INSERT INTO vouchere_utilizatori (voucher_id, utilizator_id, folosit, data_folosire)
                                VALUES (:voucher_id, :user_id, 1, NOW())
                                ON DUPLICATE KEY UPDATE folosit = 1, data_folosire = NOW()
                            ");
                            $stmt->bindParam(':voucher_id', $voucherId);
                            $stmt->bindParam(':user_id', $userId);
                            $stmt->execute();
                        } else {
                            // Voucher is no longer valid
                            $voucherId = null;
                            unset($_SESSION['voucher']);
                        }
                    }
                    
                    // Calculate shipping
                    $shipping = ($subtotal - $discount) >= 150 ? 0 : 15;
                    
                    // Calculate total
                    $total = $subtotal - $discount + $shipping;
                    
                    // Generate order number
                    $orderNumber = 'GR' . date('Ymd') . rand(1000, 9999);
                    
                    // Insert order
                    $stmt = $conn->prepare("
                        INSERT INTO comenzi (
                            utilizator_id, numar_comanda, subtotal, discount, transport, total, 
                            voucher_id, metoda_plata, status_plata, 
                            nume_livrare, adresa_livrare, oras_livrare, judet_livrare, cod_postal_livrare, telefon_livrare,
                            nume_facturare, adresa_facturare, oras_facturare, judet_facturare, cod_postal_facturare, telefon_facturare,
                            observatii
                        ) VALUES (
                            :user_id, :order_number, :subtotal, :discount, :shipping, :total,
                            :voucher_id, :payment_method, :payment_status,
                            :shipping_name, :shipping_address, :shipping_city, :shipping_county, :shipping_zip, :shipping_phone,
                            :billing_name, :billing_address, :billing_city, :billing_county, :billing_zip, :billing_phone,
                            :notes
                        )
                    ");
                    
                    $paymentStatus = $paymentMethod === 'ramburs' ? 'in_asteptare' : 'in_asteptare';
                    
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':order_number', $orderNumber);
                    $stmt->bindParam(':subtotal', $subtotal);
                    $stmt->bindParam(':discount', $discount);
                    $stmt->bindParam(':shipping', $shipping);
                    $stmt->bindParam(':total', $total);
                    $stmt->bindParam(':voucher_id', $voucherId);
                    $stmt->bindParam(':payment_method', $paymentMethod);
                    $stmt->bindParam(':payment_status', $paymentStatus);
                    $stmt->bindParam(':shipping_name', $shippingName);
                    $stmt->bindParam(':shipping_address', $shippingAddress);
                    $stmt->bindParam(':shipping_city', $shippingCity);
                    $stmt->bindParam(':shipping_county', $shippingCounty);
                    $stmt->bindParam(':shipping_zip', $shippingZip);
                    $stmt->bindParam(':shipping_phone', $shippingPhone);
                    $stmt->bindParam(':billing_name', $billingName);
                    $stmt->bindParam(':billing_address', $billingAddress);
                    $stmt->bindParam(':billing_city', $billingCity);
                    $stmt->bindParam(':billing_county', $billingCounty);
                    $stmt->bindParam(':billing_zip', $billingZip);
                    $stmt->bindParam(':billing_phone', $billingPhone);
                    $stmt->bindParam(':notes', $notes);
                    
                    $stmt->execute();
                    $orderId = $conn->lastInsertId();
                    
                    // Insert order details
                    foreach ($cartItems as $item) {
                        $price = $item['pret_redus'] ? $item['pret_redus'] : $item['pret'];
                        $itemTotal = $price * $item['cantitate'];
                        
                        $stmt = $conn->prepare("
                            INSERT INTO detalii_comenzi (comanda_id, produs_id, nume_produs, pret_unitar, cantitate, subtotal)
                            VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)
                        ");
                        
                        $stmt->bindParam(':order_id', $orderId);
                        $stmt->bindParam(':product_id', $item['produs_id']);
                        $stmt->bindParam(':product_name', $item['nume']);
                        $stmt->bindParam(':price', $price);
                        $stmt->bindParam(':quantity', $item['cantitate']);
                        $stmt->bindParam(':subtotal', $itemTotal);
                        $stmt->execute();
                        
                        // Update product stock
                        $stmt = $conn->prepare("
                            UPDATE produse 
                            SET stoc = stoc - :quantity 
                            WHERE id = :product_id
                        ");
                        $stmt->bindParam(':quantity', $item['cantitate']);
                        $stmt->bindParam(':product_id', $item['produs_id']);
                        $stmt->execute();
                    }
                    
                    // Clear cart
                    $stmt = $conn->prepare("DELETE FROM cos_cumparaturi WHERE utilizator_id = :user_id");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->execute();
                    
                    // Clear voucher from session
                    unset($_SESSION['voucher']);
                    
                    // Add loyalty points (1 point per 10 RON spent)
                    $points = floor($total / 10);
                    if ($points > 0) {
                        addLoyaltyPoints($conn, $userId, $points, 'Puncte pentru comanda #' . $orderNumber, $orderId);
                    }
                    
                    // Log the action
                    logAction($conn, 'place_order', 'Comandă plasată: #' . $orderNumber, $userId);
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $response['success'] = true;
                    $response['message'] = 'Comanda a fost plasată cu succes!';
                    $response['orderId'] = $orderId;
                    $response['orderNumber'] = $orderNumber;
                }
            }
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);