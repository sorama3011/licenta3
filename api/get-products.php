<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'products' => [],
    'total' => 0,
    'page' => 1,
    'perPage' => 12,
    'totalPages' => 1
];

try {
    // Get filter parameters
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $region = isset($_GET['region']) ? $_GET['region'] : null;
    $tags = isset($_GET['tags']) ? explode(',', $_GET['tags']) : [];
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'recommended';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['perPage']) ? max(1, intval($_GET['perPage'])) : 12;
    
    // Build query
    $query = "
        SELECT p.*, c.nume as categorie, c.slug as categorie_slug, r.nume as regiune
        FROM produse p
        LEFT JOIN categorii c ON p.categorie_id = c.id
        LEFT JOIN regiuni r ON p.regiune_id = r.id
        WHERE p.activ = 1
    ";
    
    $countQuery = "
        SELECT COUNT(*) as total
        FROM produse p
        LEFT JOIN categorii c ON p.categorie_id = c.id
        LEFT JOIN regiuni r ON p.regiune_id = r.id
        WHERE p.activ = 1
    ";
    
    $params = [];
    
    // Add category filter
    if ($category) {
        $query .= " AND c.slug = :category";
        $countQuery .= " AND c.slug = :category";
        $params[':category'] = $category;
    }
    
    // Add region filter
    if ($region) {
        $query .= " AND r.nume = :region";
        $countQuery .= " AND r.nume = :region";
        $params[':region'] = $region;
    }
    
    // Add tags filter
    if (!empty($tags)) {
        $query .= " AND p.id IN (
            SELECT pe.produs_id 
            FROM produse_etichete pe
            JOIN etichete e ON pe.eticheta_id = e.id
            WHERE e.slug IN (" . implode(',', array_fill(0, count($tags), '?')) . ")
            GROUP BY pe.produs_id
            HAVING COUNT(DISTINCT e.slug) = " . count($tags) . "
        )";
        
        $countQuery .= " AND p.id IN (
            SELECT pe.produs_id 
            FROM produse_etichete pe
            JOIN etichete e ON pe.eticheta_id = e.id
            WHERE e.slug IN (" . implode(',', array_fill(0, count($tags), '?')) . ")
            GROUP BY pe.produs_id
            HAVING COUNT(DISTINCT e.slug) = " . count($tags) . "
        )";
        
        foreach ($tags as $index => $tag) {
            $params[] = $tag;
        }
    }
    
    // Add search filter
    if ($search) {
        $query .= " AND (p.nume LIKE :search OR p.descriere LIKE :search OR p.descriere_scurta LIKE :search)";
        $countQuery .= " AND (p.nume LIKE :search OR p.descriere LIKE :search OR p.descriere_scurta LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Add sorting
    switch ($sort) {
        case 'price-asc':
            $query .= " ORDER BY COALESCE(p.pret_redus, p.pret) ASC";
            break;
        case 'price-desc':
            $query .= " ORDER BY COALESCE(p.pret_redus, p.pret) DESC";
            break;
        case 'name-asc':
            $query .= " ORDER BY p.nume ASC";
            break;
        case 'name-desc':
            $query .= " ORDER BY p.nume DESC";
            break;
        case 'newest':
            $query .= " ORDER BY p.data_adaugare DESC";
            break;
        case 'recommended':
        default:
            $query .= " ORDER BY p.recomandat DESC, p.data_adaugare DESC";
            break;
    }
    
    // Add pagination
    $offset = ($page - 1) * $perPage;
    $query .= " LIMIT :offset, :limit";
    
    // Get total count
    $stmt = $conn->prepare($countQuery);
    
    // Bind parameters for count query
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $stmt->bindValue($key + 1, $value);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get products
    $stmt = $conn->prepare($query);
    
    // Bind parameters for main query
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $stmt->bindValue($key + 1, $value);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get tags for each product
    foreach ($products as &$product) {
        $stmt = $conn->prepare("
            SELECT e.* 
            FROM produse_etichete pe
            JOIN etichete e ON pe.eticheta_id = e.id
            WHERE pe.produs_id = :product_id
        ");
        $stmt->bindParam(':product_id', $product['id']);
        $stmt->execute();
        
        $product['tags'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $response['success'] = true;
    $response['products'] = $products;
    $response['total'] = $totalCount;
    $response['page'] = $page;
    $response['perPage'] = $perPage;
    $response['totalPages'] = ceil($totalCount / $perPage);
    
} catch (PDOException $e) {
    $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);