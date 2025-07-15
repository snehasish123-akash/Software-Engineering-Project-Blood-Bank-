<?php
require_once 'config/database.php';

$success = '';
$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Always show success message for security (don't reveal if email exists)
            $success = 'If an account with that email exists, we have sent password reset instructions to your email address.';
            
            // In a real application, you would:
            // 1. Generate a secure reset token
            // 2. Store it in the database with expiration
            // 3. Send email with reset link
            // For this demo, we'll just show the success message
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $success = 'If an account with that email exists, we have sent password reset instructions to your email address.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BBDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-tint me-2"></i>BBDMS
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Side - Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 400px; margin-top: 76px;">
                    <div class="text-center mb-4">
                        <i class="fas fa-key text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-2">Forgot Password?</h2>
                        <p class="text-muted">Enter your email to reset your password</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-sm btn-success">Back to Login</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                        </button>

                        <!-- Links -->
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Don't have an account?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side - Info -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <div class="text-center text-white p-5">
                        <i class="fas fa-shield-alt" style="font-size: 5rem; margin-bottom: 2rem; opacity: 0.8;"></i>
                        <h3 class="mb-4">Secure Password Reset</h3>
                        <p class="lead mb-4">We'll send you secure instructions to reset your password and get back to saving lives.</p>
                        <div class="alert alert-info bg-transparent border-light text-white">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                For security reasons, reset links expire after 1 hour.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>