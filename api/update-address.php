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
    $response['message'] = 'Trebuie să fiți autentificat pentru a actualiza o adresă.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $addressId = intval($_POST['addressId'] ?? 0);
    $addressType = trim($_POST['addressType'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $county = trim($_POST['county'] ?? '');
    $zipCode = trim($_POST['zipCode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $isDefault = isset($_POST['isDefault']) && $_POST['isDefault'] === 'on';
    
    // Validate input
    $errors = [];
    
    if ($addressId <= 0) {
        $errors['addressId'] = 'ID adresă invalid.';
    }
    
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
            
            // Check if address exists and belongs to user
            $stmt = $conn->prepare("
                SELECT * FROM adrese 
                WHERE id = :id AND utilizator_id = :user_id
            ");
            $stmt->bindParam(':id', $addressId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $addressData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$addressData) {
                $response['message'] = 'Adresa nu a fost găsită sau nu vă aparține.';
            } else {
                // Begin transaction
                $conn->beginTransaction();
                
                // If set as default, unset other default addresses of the same type
                if ($isDefault) {
                    $stmt = $conn->prepare("
                        UPDATE adrese 
                        SET implicit = 0 
                        WHERE utilizator_id = :user_id AND tip = :type AND id != :address_id
                    ");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':type', $addressType);
                    $stmt->bindParam(':address_id', $addressId);
                    $stmt->execute();
                }
                
                // Update address
                $stmt = $conn->prepare("
                    UPDATE adrese 
                    SET tip = :type, adresa = :address, oras = :city, judet = :county, 
                        cod_postal = :zip_code, telefon = :phone, implicit = :is_default
                    WHERE id = :id AND utilizator_id = :user_id
                ");
                
                $stmt->bindParam(':type', $addressType);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':city', $city);
                $stmt->bindParam(':county', $county);
                $stmt->bindParam(':zip_code', $zipCode);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':is_default', $isDefault, PDO::PARAM_BOOL);
                $stmt->bindParam(':id', $addressId);
                $stmt->bindParam(':user_id', $userId);
                
                $stmt->execute();
                
                // Log the action
                logAction($conn, 'update_address', 'Actualizare adresă: ID ' . $addressId, $userId);
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Adresa a fost actualizată cu succes!';
            }
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