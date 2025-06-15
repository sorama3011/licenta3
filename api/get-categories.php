<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'categories' => []
];

try {
    // Get categories
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categorii c
        LEFT JOIN produse p ON c.id = p.categorie_id AND p.activ = 1
        GROUP BY c.id
        ORDER BY c.nume
    ");
    $stmt->execute();
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['categories'] = $categories;
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);