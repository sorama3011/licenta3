<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $response['message'] = 'Toate câmpurile sunt obligatorii.';
    } else {
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM utilizatori WHERE email = :email AND activ = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['parola'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenume'] . ' ' . $user['nume'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login time
                updateLastLogin($conn, $user['id']);
                
                // Log the login action
                logAction($conn, 'login', 'Autentificare reușită', $user['id']);
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = generateToken();
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $stmt = $conn->prepare("
                        INSERT INTO sesiuni (id, utilizator_id, data_expirare, ip_address, user_agent)
                        VALUES (:token, :user_id, FROM_UNIXTIME(:expiry), :ip, :user_agent)
                    ");
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':user_id', $user['id']);
                    $stmt->bindParam(':expiry', $expiry);
                    $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
                    $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
                    $stmt->execute();
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                }
                
                $response['success'] = true;
                $response['message'] = 'Autentificare reușită! Bun venit înapoi!';
                
                // Check if there's a redirect URL
                if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
                    $response['redirect'] = $_POST['redirect'];
                } else {
                    $response['redirect'] = 'account.html';
                }
            } else {
                // Login failed
                $response['message'] = 'Email sau parolă incorectă.';
                
                // Log failed login attempt
                logAction($conn, 'login_failed', 'Încercare de autentificare eșuată pentru: ' . $email);
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);