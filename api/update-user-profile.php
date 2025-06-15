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
    $response['message'] = 'Trebuie să fiți autentificat pentru a actualiza profilul.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $newsletter = isset($_POST['newsletter']) && $_POST['newsletter'] === 'on';
    
    // Validate input
    $errors = [];
    
    if (empty($firstName)) {
        $errors['firstName'] = 'Prenumele este obligatoriu.';
    }
    
    if (empty($lastName)) {
        $errors['lastName'] = 'Numele este obligatoriu.';
    }
    
    // Password validation only if user wants to change it
    $changePassword = false;
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        $changePassword = true;
        
        if (empty($currentPassword)) {
            $errors['currentPassword'] = 'Parola actuală este obligatorie.';
        }
        
        if (empty($newPassword)) {
            $errors['newPassword'] = 'Noua parolă este obligatorie.';
        } elseif (strlen($newPassword) < 8) {
            $errors['newPassword'] = 'Noua parolă trebuie să aibă cel puțin 8 caractere.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors['confirmPassword'] = 'Parolele nu se potrivesc.';
        }
    }
    
    if (empty($errors)) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get current user data
            $stmt = $conn->prepare("SELECT * FROM utilizatori WHERE id = :id AND activ = 1");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $response['message'] = 'Utilizatorul nu a fost găsit sau contul este dezactivat.';
            } else {
                // Verify current password if changing password
                if ($changePassword && !password_verify($currentPassword, $user['parola'])) {
                    $errors['currentPassword'] = 'Parola actuală este incorectă.';
                    $response['errors'] = $errors;
                    $response['message'] = 'Vă rugăm să corectați erorile din formular.';
                } else {
                    // Begin transaction
                    $conn->beginTransaction();
                    
                    // Update user profile
                    $stmt = $conn->prepare("
                        UPDATE utilizatori 
                        SET prenume = :firstName, nume = :lastName, telefon = :phone, newsletter = :newsletter
                        WHERE id = :id
                    ");
                    
                    $stmt->bindParam(':firstName', $firstName);
                    $stmt->bindParam(':lastName', $lastName);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':newsletter', $newsletter, PDO::PARAM_BOOL);
                    $stmt->bindParam(':id', $userId);
                    $stmt->execute();
                    
                    // Update password if requested
                    if ($changePassword) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("UPDATE utilizatori SET parola = :password WHERE id = :id");
                        $stmt->bindParam(':password', $hashedPassword);
                        $stmt->bindParam(':id', $userId);
                        $stmt->execute();
                        
                        // Log password change
                        logAction($conn, 'password_change', 'Schimbare parolă', $userId);
                    }
                    
                    // Update newsletter subscription
                    if ($newsletter) {
                        $fullName = $firstName . ' ' . $lastName;
                        $email = $user['email'];
                        $confirmationCode = generateToken(16);
                        
                        $stmt = $conn->prepare("
                            INSERT INTO newsletter (email, nume, cod_confirmare, confirmat)
                            VALUES (:email, :name, :code, 1)
                            ON DUPLICATE KEY UPDATE 
                            nume = :name, 
                            cod_confirmare = :code, 
                            activ = 1
                        ");
                        
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':name', $fullName);
                        $stmt->bindParam(':code', $confirmationCode);
                        $stmt->execute();
                    } else {
                        // Update newsletter status to inactive
                        $stmt = $conn->prepare("
                            UPDATE newsletter 
                            SET activ = 0 
                            WHERE email = :email
                        ");
                        $stmt->bindParam(':email', $user['email']);
                        $stmt->execute();
                    }
                    
                    // Update session data
                    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                    
                    // Log the action
                    logAction($conn, 'profile_update', 'Actualizare profil utilizator', $userId);
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $response['success'] = true;
                    $response['message'] = 'Profilul a fost actualizat cu succes!';
                }
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