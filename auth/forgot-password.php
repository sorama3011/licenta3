<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get email
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $response['message'] = 'Adresa de email este obligatorie.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Adresa de email nu este validă.';
    } else {
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM utilizatori WHERE email = :email AND activ = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $token = generateToken();
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $conn->prepare("
                    INSERT INTO resetare_parola (utilizator_id, token, data_expirare)
                    VALUES (:user_id, :token, :expiry)
                ");
                
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expiry', $expiry);
                $stmt->execute();
                
                // In a real application, send email with reset link
                // For demo purposes, we'll just return success
                
                // Log the action
                logAction($conn, 'password_reset_request', 'Solicitare resetare parolă', $user['id']);
                
                $response['success'] = true;
                $response['message'] = 'Instrucțiunile pentru resetarea parolei au fost trimise pe email.';
            } else {
                // For security reasons, don't reveal if email exists or not
                $response['success'] = true;
                $response['message'] = 'Dacă adresa de email există în baza noastră de date, veți primi instrucțiuni pentru resetarea parolei.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);