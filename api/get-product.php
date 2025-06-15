<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'product' => null
];

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response['message'] = 'ID produs invalid.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $productId = intval($_GET['id']);
    
    // Get product details
    $stmt = $conn->prepare("
        SELECT p.*, c.nume as categorie, c.slug as categorie_slug, r.nume as regiune
        FROM produse p
        LEFT JOIN categorii c ON p.categorie_id = c.id
        LEFT JOIN regiuni r ON p.regiune_id = r.id
        WHERE p.id = :id AND p.activ = 1
    ");
    $stmt->bindParam(':id', $productId);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $response['message'] = 'Produsul nu a fost găsit sau nu este disponibil.';
    } else {
        // Get product images
        $stmt = $conn->prepare("
            SELECT * FROM imagini_produse
            WHERE produs_id = :product_id
            ORDER BY principal DESC
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get nutritional information
        $stmt = $conn->prepare("
            SELECT * FROM informatii_nutritionale
            WHERE produs_id = :product_id
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $product['nutritional_info'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get ingredients
        $stmt = $conn->prepare("
            SELECT * FROM ingrediente
            WHERE produs_id = :product_id
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $ingredients = $stmt->fetch(PDO::FETCH_ASSOC);
        $product['ingredients'] = $ingredients ? $ingredients['lista_ingrediente'] : null;
        
        // Get product tags
        $stmt = $conn->prepare("
            SELECT e.* 
            FROM produse_etichete pe
            JOIN etichete e ON pe.eticheta_id = e.id
            WHERE pe.produs_id = :product_id
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $product['tags'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get related products (same category)
        $stmt = $conn->prepare("
            SELECT p.id, p.nume, p.pret, p.pret_redus, p.cantitate, p.imagine, p.slug
            FROM produse p
            WHERE p.categorie_id = :category_id AND p.id != :product_id AND p.activ = 1
            ORDER BY p.recomandat DESC, RAND()
            LIMIT 4
        ");
        $stmt->bindParam(':category_id', $product['categorie_id']);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $product['related_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get reviews
        $stmt = $conn->prepare("
            SELECT r.*, u.prenume, u.nume
            FROM recenzii r
            JOIN utilizatori u ON r.utilizator_id = u.id
            WHERE r.produs_id = :product_id AND r.aprobat = 1
            ORDER BY r.data_adaugare DESC
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $product['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate average rating
        $stmt = $conn->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
            FROM recenzii
            WHERE produs_id = :product_id AND aprobat = 1
        ");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
        $product['avg_rating'] = floatval($ratingData['avg_rating'] ?? 0);
        $product['review_count'] = intval($ratingData['review_count'] ?? 0);
        
        $response['success'] = true;
        $response['product'] = $product;
        
        // Log view if user is logged in
        if (isset($_SESSION['user_id'])) {
            logAction($conn, 'view_product', 'Vizualizare produs: ' . $product['nume'], $_SESSION['user_id']);
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);