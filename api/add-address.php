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
    $response['message'] = 'Trebuie să fiți autentificat pentru a adăuga o adresă.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $addressType = trim($_POST['addressType'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $county = trim($_POST['county'] ?? '');
    $zipCode = trim($_POST['zipCode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $isDefault = isset($_POST['isDefault']) && $_POST['isDefault'] === 'on';
    
    // Validate input
    $errors = [];
    
    if (empty($addressType) || !in_array($addressType, ['livrare', 'facturare'])) {
        $errors['addressType'] = 'Tipul adresei este invalid.';
    }
    
    if (empty($address)) {
        $errors['address'] = 'Adresa este obligatorie.';
    }
    
    if (empty($city)) {
        $errors['city'] = 'Orașul este obligatoriu.';
    }
    
    if (empty($county)) {
        $errors['county'] = 'Județul este obligatoriu.';
    }
    
    if (empty($zipCode)) {
        $errors['zipCode'] = 'Codul poștal este obligatoriu.';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Telefonul este obligatoriu.';
    }
    
    if (empty($errors)) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Begin transaction
            $conn->beginTransaction();
            
            // If set as default, unset other default addresses of the same type
            if ($isDefault) {
                $stmt = $conn->prepare("
                    UPDATE adrese 
                    SET implicit = 0 
                    WHERE utilizator_id = :user_id AND tip = :type
                ");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':type', $addressType);
                $stmt->execute();
            }
            
            // Add new address
            $stmt = $conn->prepare("
                INSERT INTO adrese (utilizator_id, tip, adresa, oras, judet, cod_postal, telefon, implicit)
                VALUES (:user_id, :type, :address, :city, :county, :zip_code, :phone, :is_default)
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $addressType);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':county', $county);
            $stmt->bindParam(':zip_code', $zipCode);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':is_default', $isDefault, PDO::PARAM_BOOL);
            
            $stmt->execute();
            $addressId = $conn->lastInsertId();
            
            // Log the action
            logAction($conn, 'add_address', 'Adăugare adresă nouă: ' . $addressType, $userId);
            
            // Commit transaction
            $conn->commit();
            
            $response['success'] = true;
            $response['message'] = 'Adresa a fost adăugată cu succes!';
            $response['addressId'] = $addressId;
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
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