<?php
require_once 'config/database.php';

$error = '';
$success = '';
$user_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'seeker';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $date_of_birth = sanitizeInput($_POST['date_of_birth'] ?? '');
    $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
    $user_type = sanitizeInput($_POST['user_type'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($user_type)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username or email already exists';
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $status = ($user_type === 'donor') ? 'pending' : 'active'; // Donors need approval
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, date_of_birth, blood_group, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashed_password, $user_type, $full_name, $phone, $address, $gender, $date_of_birth, $blood_group, $status])) {
                    if ($user_type === 'donor') {
                        $success = 'Registration successful! Your account is pending admin approval. You will be notified once approved.';
                    } else {
                        $success = 'Registration successful! You can now login to your account.';
                    }
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BBDMS</title>
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

    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-user-plus me-2"></i>Create Your Account</h3>
                        <p class="mb-0 text-muted">Join the BBDMS community and help save lives</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-success">Go to Login</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <!-- User Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">I want to register as:</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="user_type" id="seeker_reg" value="seeker" <?php echo $user_type === 'seeker' ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary w-100 p-3" for="seeker_reg">
                                            <i class="fas fa-search d-block mb-2" style="font-size: 2rem;"></i>
                                            <strong>Blood Seeker</strong>
                                            <small class="d-block text-muted">Looking for blood donors</small>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="user_type" id="donor_reg" value="donor" <?php echo $user_type === 'donor' ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary w-100 p-3" for="donor_reg">
                                            <i class="fas fa-heart d-block mb-2" style="font-size: 2rem;"></i>
                                            <strong>Blood Donor</strong>
                                            <small class="d-block text-muted">Want to donate blood</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please choose a username.</div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>

                                <!-- Full Name -->
                                <div class="col-12">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide your full name.</div>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>

                                <!-- Gender -->
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <!-- Date of Birth -->
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                                </div>

                                <!-- Blood Group -->
                                <div class="col-md-6">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <?php
                                        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($bloodGroups as $group) {
                                            $selected = ($_POST['blood_group'] ?? '') === $group ? 'selected' : '';
                                            echo "<option value=\"$group\" $selected>$group</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Address -->
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                    <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="invalid-feedback">Please confirm your password.</div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>

                            <!-- Login Link -->
                            <div class="text-center mt-3">
                                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Sign in here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>