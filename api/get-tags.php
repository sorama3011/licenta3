<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'tags' => []
];

try {
    // Get tags
    $stmt = $conn->prepare("
        SELECT e.*, COUNT(pe.produs_id) as product_count
        FROM etichete e
        LEFT JOIN produse_etichete pe ON e.id = pe.eticheta_id
        LEFT JOIN produse p ON pe.produs_id = p.id AND p.activ = 1
        GROUP BY e.id
        ORDER BY e.nume
    ");
    $stmt->execute();
    
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['tags'] = $tags;
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);