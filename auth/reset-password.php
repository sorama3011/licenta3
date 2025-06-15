<?php
// Start session
session_start();

// Include database configuration
require_once 'db-config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'valid_token' => false
];

// Check if token is provided
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $response['message'] = 'Token invalid sau expirat.';
} else {
    try {
        // Check if token exists and is valid
        $stmt = $conn->prepare("
            SELECT r.*, u.email 
            FROM resetare_parola r
            JOIN utilizatori u ON r.utilizator_id = u.id
            WHERE r.token = :token 
            AND r.data_expirare > NOW() 
            AND r.folosit = 0
        ");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resetData) {
            $response['valid_token'] = true;
            $response['email'] = $resetData['email'];
            
            // If form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirmPassword'] ?? '';
                
                // Validate passwords
                if (empty($password)) {
                    $response['message'] = 'Parola este obligatorie.';
                } elseif (strlen($password) < 8) {
                    $response['message'] = 'Parola trebuie să aibă cel puțin 8 caractere.';
                } elseif ($password !== $confirmPassword) {
                    $response['message'] = 'Parolele nu se potrivesc.';
                } else {
                    // Update password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE utilizatori SET parola = :password WHERE id = :user_id");
                    $stmt->bindParam(':password', $hashedPassword);
                    $stmt->bindParam(':user_id', $resetData['utilizator_id']);
                    $stmt->execute();
                    
                    // Mark token as used
                    $stmt = $conn->prepare("UPDATE resetare_parola SET folosit = 1 WHERE id = :id");
                    $stmt->bindParam(':id', $resetData['id']);
                    $stmt->execute();
                    
                    // Log the action
                    logAction($conn, 'password_reset', 'Resetare parolă reușită', $resetData['utilizator_id']);
                    
                    $response['success'] = true;
                    $response['message'] = 'Parola a fost resetată cu succes. Acum vă puteți autentifica.';
                }
            }
        } else {
            $response['message'] = 'Token invalid sau expirat.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Eroare de bază de date: ' . $e->getMessage();
    }
}

// If it's an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!doctype html>
<html lang="ro">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resetare Parolă - Gusturi Românești</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.html">
                🇷🇴 Gusturi Românești
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Acasă</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../products.html">Produse</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../offers.html">Oferte</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../about.html">Despre Noi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Reset Password Section -->
    <section class="py-5" style="margin-top: 76px; min-height: calc(100vh - 76px);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <h3 class="mb-0">
                                <i class="bi bi-key-fill me-2"></i>
                                Resetare Parolă
                            </h3>
                        </div>
                        <div class="card-body p-5">
                            <?php if ($response['success']): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($response['message']); ?>
                                </div>
                                <div class="text-center mt-4">
                                    <a href="../login.html" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Mergi la Autentificare
                                    </a>
                                </div>
                            <?php elseif ($response['valid_token']): ?>
                                <?php if (!empty($response['message'])): ?>
                                    <div class="alert alert-danger">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <?php echo htmlspecialchars($response['message']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="mb-4">Introduceți noua parolă pentru contul asociat cu adresa de email: <strong><?php echo htmlspecialchars($response['email']); ?></strong></p>
                                
                                <form id="resetPasswordForm" method="post">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Parolă Nouă</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                <i class="bi bi-eye" id="toggleIcon1"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Parola trebuie să aibă cel puțin 8 caractere</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirmPassword" class="form-label">Confirmă Parola</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock-fill"></i>
                                            </span>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                                <i class="bi bi-eye" id="toggleIcon2"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-check-lg me-2"></i>
                                        Resetează Parola
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($response['message']); ?>
                                </div>
                                <div class="text-center mt-4">
                                    <a href="../login.html" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Mergi la Autentificare
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5>Gusturi Românești</h5>
                    <p>Cea mai mare platformă online de produse tradiționale românești.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6>Navigare</h6>
                    <ul class="list-unstyled">
                        <li><a href="../index.html" class="text-light text-decoration-none">Acasă</a></li>
                        <li><a href="../products.html" class="text-light text-decoration-none">Produse</a></li>
                        <li><a href="../offers.html" class="text-light text-decoration-none">Oferte</a></li>
                        <li><a href="../about.html" class="text-light text-decoration-none">Despre Noi</a></li>
                        <li><a href="../contact.php" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6>Cont</h6>
                    <ul class="list-unstyled">
                        <li><a href="../login.html" class="text-light text-decoration-none">Autentificare</a></li>
                        <li><a href="../cart.html" class="text-light text-decoration-none">Coșul Meu</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6>Informații</h6>
                    <ul class="list-unstyled">
                        <li><a href="../privacy.html" class="text-light text-decoration-none">Politica de Confidențialitate</a></li>
                        <li><a href="../terms.html" class="text-light text-decoration-none">Termeni și Condiții</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6>Contact</h6>
                    <p class="small mb-1">📍 București, România</p>
                    <p class="small mb-1">📞 +40 721 234 567</p>
                    <p class="small">✉️ contact@gusturi-romanesti.ro</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Gusturi Românești. Toate drepturile rezervate.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(inputId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>