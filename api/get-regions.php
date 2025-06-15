<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'regions' => []
];

try {
    // Get regions
    $stmt = $conn->prepare("
        SELECT r.*, COUNT(p.id) as product_count
        FROM regiuni r
        LEFT JOIN produse p ON r.id = p.regiune_id AND p.activ = 1
        GROUP BY r.id
        ORDER BY r.nume
    ");
    $stmt->execute();
    
    $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['regions'] = $regions;
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);