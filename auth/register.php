<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $newsletter = isset($_POST['newsletter']) && $_POST['newsletter'] === 'on';
    $agreeTerms = isset($_POST['agreeTerms']) && $_POST['agreeTerms'] === 'on';
    
    // Validate input
    $errors = [];
    
    if (empty($firstName)) {
        $errors['firstName'] = 'Prenumele este obligatoriu.';
    }
    
    if (empty($lastName)) {
        $errors['lastName'] = 'Numele este obligatoriu.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email-ul este obligatoriu.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adresa de email nu este validă.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Parola este obligatorie.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Parola trebuie să aibă cel puțin 8 caractere.';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Parolele nu se potrivesc.';
    }
    
    if (!$agreeTerms) {
        $errors['agreeTerms'] = 'Trebuie să fiți de acord cu Termenii și Condițiile.';
    }
    
    // Check if email already exists
    if (empty($errors['email'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM utilizatori WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'Această adresă de email este deja înregistrată.';
        }
    }
    
    if (empty($errors)) {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Begin transaction
            $conn->beginTransaction();
            
            // Insert user
            $stmt = $conn->prepare("
                INSERT INTO utilizatori (prenume, nume, email, parola, telefon, newsletter)
                VALUES (:firstName, :lastName, :email, :password, :phone, :newsletter)
            ");
            
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':lastName', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':newsletter', $newsletter, PDO::PARAM_BOOL);
            
            $stmt->execute();
            $userId = $conn->lastInsertId();
            
            // Add to newsletter if opted in
            if ($newsletter) {
                $confirmationCode = generateToken(16);
                
                $stmt = $conn->prepare("
                    INSERT INTO newsletter (email, nume, cod_confirmare, confirmat)
                    VALUES (:email, :name, :code, 1)
                    ON DUPLICATE KEY UPDATE 
                    nume = :name, 
                    cod_confirmare = :code, 
                    activ = 1
                ");
                
                $fullName = $firstName . ' ' . $lastName;
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':name', $fullName);
                $stmt->bindParam(':code', $confirmationCode);
                $stmt->execute();
            }
            
            // Log the registration
            logAction($conn, 'register', 'Înregistrare nouă utilizator', $userId);
            
            // Commit transaction
            $conn->commit();
            
            $response['success'] = true;
            $response['message'] = 'Contul a fost creat cu succes! Te poți autentifica acum.';
            
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