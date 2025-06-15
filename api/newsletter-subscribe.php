<?php
// Include database configuration
require_once '../auth/db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get email
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $response['message'] = 'Adresa de email este obligatorie.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Adresa de email nu este validă.';
    } else {
        try {
            // Generate confirmation code
            $confirmationCode = generateToken(16);
            
            // Insert or update newsletter subscription
            $stmt = $conn->prepare("
                INSERT INTO newsletter (email, nume, cod_confirmare)
                VALUES (:email, :name, :code)
                ON DUPLICATE KEY UPDATE 
                nume = :name, 
                cod_confirmare = :code, 
                activ = 1, 
                data_inscriere = NOW()
            ");
            
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':code', $confirmationCode);
            
            if ($stmt->execute()) {
                // In a real application, send confirmation email
                // For demo purposes, we'll just return success
                
                // Log the action
                logAction($conn, 'newsletter_subscribe', 'Înscriere newsletter: ' . $email);
                
                $response['success'] = true;
                $response['message'] = 'Te-ai abonat cu succes la newsletter! Vei primi un email de confirmare.';
            } else {
                $response['message'] = 'A apărut o eroare la procesarea cererii. Vă rugăm să încercați din nou.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);