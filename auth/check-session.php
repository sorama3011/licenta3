<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Initialize response array
$response = [
    'loggedIn' => false,
    'user' => null
];

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $response['loggedIn'] = true;
    
    // Get user data
    $user = getCurrentUser($conn);
    
    if ($user) {
        $response['user'] = [
            'id' => $user['id'],
            'name' => $user['prenume'] . ' ' . $user['nume'],
            'email' => $user['email'],
            'points' => $user['puncte_fidelitate']
        ];
    }
} 
// Check if user is logged in via remember me cookie
elseif (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $conn->prepare("
            SELECT s.*, u.id, u.prenume, u.nume, u.email, u.puncte_fidelitate
            FROM sesiuni s
            JOIN utilizatori u ON s.utilizator_id = u.id
            WHERE s.id = :token
            AND s.data_expirare > NOW()
            AND u.activ = 1
        ");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session) {
            // Set session variables
            $_SESSION['user_id'] = $session['id'];
            $_SESSION['user_name'] = $session['prenume'] . ' ' . $session['nume'];
            $_SESSION['user_email'] = $session['email'];
            
            // Update last login time
            updateLastLogin($conn, $session['id']);
            
            // Log the action
            logAction($conn, 'auto_login', 'Autentificare automată prin cookie', $session['id']);
            
            $response['loggedIn'] = true;
            $response['user'] = [
                'id' => $session['id'],
                'name' => $session['prenume'] . ' ' . $session['nume'],
                'email' => $session['email'],
                'points' => $session['puncte_fidelitate']
            ];
            
            // Extend cookie expiration
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
            
            // Update token expiration in database
            $stmt = $conn->prepare("UPDATE sesiuni SET data_expirare = FROM_UNIXTIME(:expiry) WHERE id = :token");
            $stmt->bindParam(':expiry', $expiry);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Log error but don't expose to user
        error_log('Database error in check-session.php: ' . $e->getMessage());
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);