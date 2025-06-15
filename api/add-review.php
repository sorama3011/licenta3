<?php
// Start session
session_start();

// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Trebuie să fiți autentificat pentru a adăuga o recenzie.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productId = intval($_POST['productId'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    // Validate input
    $errors = [];
    
    if ($productId <= 0) {
        $errors['productId'] = 'ID produs invalid.';
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Rating invalid. Trebuie să fie între 1 și 5.';
    }
    
    if (empty($title)) {
        $errors['title'] = 'Titlul este obligatoriu.';
    }
    
    if (empty($comment)) {
        $errors['comment'] = 'Comentariul este obligatoriu.';
    }
    
    if (empty($errors)) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Check if product exists
            $stmt = $conn->prepare("SELECT id, nume FROM produse WHERE id = :id AND activ = 1");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $response['message'] = 'Produsul nu există sau nu este disponibil.';
            } else {
                // Check if user has already reviewed this product
                $stmt = $conn->prepare("
                    SELECT id FROM recenzii 
                    WHERE produs_id = :product_id AND utilizator_id = :user_id
                ");
                $stmt->bindParam(':product_id', $productId);
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
                
                if ($stmt->fetch()) {
                    $response['message'] = 'Ați adăugat deja o recenzie pentru acest produs.';
                } else {
                    // Add review
                    $stmt = $conn->prepare("
                        INSERT INTO recenzii (produs_id, utilizator_id, rating, titlu, comentariu, aprobat)
                        VALUES (:product_id, :user_id, :rating, :title, :comment, :approved)
                    ");
                    
                    // Auto-approve reviews for now (in a real app, this might be set to 0 for moderation)
                    $approved = 1;
                    
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':rating', $rating);
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':comment', $comment);
                    $stmt->bindParam(':approved', $approved);
                    
                    $stmt->execute();
                    
                    $response['success'] = true;
                    $response['message'] = 'Recenzia a fost adăugată cu succes!';
                    
                    // Log the action
                    logAction($conn, 'add_review', 'Adăugare recenzie pentru: ' . $product['nume'], $userId);
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    } else {
        $response['errors'] = $errors;
        $response['message'] = 'Vă rugăm să corectați erorile din formular.';
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);