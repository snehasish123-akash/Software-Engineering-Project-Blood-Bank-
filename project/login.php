<?php
require_once 'config/database.php';

$error = '';
$message = '';

// Check for messages from URL parameters or session
if (isset($_GET['message'])) {
    $message = sanitizeInput($_GET['message']);
}

$sessionMessage = getMessage();
if ($sessionMessage) {
    if ($sessionMessage['type'] === 'error') {
        $error = $sessionMessage['message'];
    } else {
        $message = $sessionMessage['message'];
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirectTo('admin/dashboard.php');
    } elseif (isDonor()) {
        redirectTo('donor/dashboard.php');
    } else {
        redirectTo('seeker/dashboard.php');
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = sanitizeInput($_POST['user_type'] ?? '');
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND user_type = ?");
            $stmt->execute([$username, $username, $user_type]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['blood_group'] = $user['blood_group'];
                    
                    // Redirect based on user type
                    switch ($user['user_type']) {
                        case 'admin':
                            redirectTo('admin/dashboard.php');
                            break;
                        case 'donor':
                            redirectTo('donor/dashboard.php');
                            break;
                        case 'seeker':
                            redirectTo('seeker/dashboard.php');
                            break;
                        default:
                            redirectTo('index.php');
                    }
                } elseif ($user['status'] === 'pending') {
                    $error = 'Your account is pending approval. Please wait for admin approval.';
                } else {
                    $error = 'Your account has been deactivated. Please contact administrator.';
                }
            } else {
                $error = 'Invalid username/email or password';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BBDMS</title>
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
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Side - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 400px; margin-top: 76px;">
                    <div class="text-center mb-4">
                        <i class="fas fa-tint text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-2">Welcome Back</h2>
                        <p class="text-muted">Sign in to your BBDMS account</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="loginForm" class="needs-validation" novalidate>
                        <!-- User Type Selection -->
                        <div class="mb-3">
                            <label class="form-label">I am a:</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="user_type" id="seeker" value="seeker" required>
                                    <label class="btn btn-outline-primary w-100" for="seeker">
                                        <i class="fas fa-search d-block mb-1"></i>
                                        <small>Seeker</small>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="user_type" id="donor" value="donor" required>
                                    <label class="btn btn-outline-primary w-100" for="donor">
                                        <i class="fas fa-heart d-block mb-1"></i>
                                        <small>Donor</small>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="user_type" id="admin" value="admin" required>
                                    <label class="btn btn-outline-primary w-100" for="admin">
                                        <i class="fas fa-user-shield d-block mb-1"></i>
                                        <small>Admin</small>
                                    </label>
                                </div>
                            </div>
                            <div class="invalid-feedback">
                                Please select your user type.
                            </div>
                        </div>

                        <!-- Username/Email -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    Please enter your username or email.
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>

                        <!-- Links -->
                        <div class="text-center">
                            <a href="forgot-password.php" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>Forgot your password?
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

            <!-- Right Side - Hero Image -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <div class="text-center text-white p-5">
                        <i class="fas fa-heartbeat" style="font-size: 5rem; margin-bottom: 2rem; opacity: 0.8;"></i>
                        <h3 class="mb-4">Save Lives Through Blood Donation</h3>
                        <p class="lead mb-4">Join our community of life-savers and make a difference in someone's life today.</p>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4>1000+</h4>
                                <p>Lives Saved</p>
                            </div>
                            <div class="col-6">
                                <h4>500+</h4>
                                <p>Active Donors</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const password = document.getElementById('password');
                    const icon = this.querySelector('i');
                    
                    if (password.type === 'password') {
                        password.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        password.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }

            // Handle form submission
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            
            if (loginForm && loginBtn) {
                loginForm.addEventListener('submit', function(e) {
                    // Check if form is valid
                    if (this.checkValidity()) {
                        // Show loading state
                        const originalText = loginBtn.innerHTML;
                        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
                        loginBtn.disabled = true;
                        
                        // Re-enable button after 10 seconds as fallback
                        setTimeout(() => {
                            loginBtn.innerHTML = originalText;
                            loginBtn.disabled = false;
                        }, 10000);
                    } else {
                        // Prevent form submission if invalid
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    
                    // Add validation classes
                    this.classList.add('was-validated');
                });
            }

            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
</body>
</html>