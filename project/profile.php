<?php
require_once 'config/database.php';

// Require login
requireLogin();

$error = '';
$success = '';

try {
    $pdo = getDBConnection();
    
    // Get current user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirectTo('logout.php');
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $date_of_birth = sanitizeInput($_POST['date_of_birth'] ?? '');
        $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($full_name) || empty($email)) {
            $error = 'Full name and email are required';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $error = 'Email address is already taken by another user';
            } else {
                // Handle password change
                $password_update = '';
                $password_params = [];
                
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Current password is required to set a new password';
                    } elseif (!password_verify($current_password, $user['password'])) {
                        $error = 'Current password is incorrect';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'New password must be at least 6 characters long';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match';
                    } else {
                        $password_update = ', password = ?';
                        $password_params[] = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }
                
                if (!$error) {
                    // Update user profile
                    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, gender = ?, date_of_birth = ?, blood_group = ?" . $password_update . " WHERE id = ?";
                    $params = [$full_name, $email, $phone, $address, $gender, $date_of_birth, $blood_group];
                    $params = array_merge($params, $password_params);
                    $params[] = $_SESSION['user_id'];
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute($params)) {
                        // Update session data
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['email'] = $email;
                        $_SESSION['blood_group'] = $blood_group;
                        
                        $success = 'Profile updated successfully';
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                    } else {
                        $error = 'Failed to update profile';
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Profile error: " . $e->getMessage());
    $error = 'An error occurred while updating your profile';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BBDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-tint me-2"></i>BBDMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">
                                <i class="fas fa-cog me-1"></i>Admin Panel
                            </a>
                        </li>
                    <?php elseif (isDonor()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="donor/dashboard.php">
                                <i class="fas fa-heart me-1"></i>Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="seeker/dashboard.php">
                                <i class="fas fa-search me-1"></i>Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-user-edit me-2"></i>My Profile</h3>
                        <p class="mb-0 text-muted">Update your personal information</p>
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
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Profile Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-user me-2"></i>Profile Information
                                    </h5>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Full Name -->
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide your full name.</div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>

                                <!-- Gender -->
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <!-- Date of Birth -->
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                                </div>

                                <!-- Blood Group -->
                                <div class="col-md-6">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <?php
                                        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($bloodGroups as $group) {
                                            $selected = ($user['blood_group'] ?? '') === $group ? 'selected' : '';
                                            echo "<option value=\"$group\" $selected>$group</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Address -->
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div class="row mt-5 mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-lock me-2"></i>Change Password
                                        <small class="text-muted">(Leave blank to keep current password)</small>
                                    </h5>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Current Password -->
                                <div class="col-md-4">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>

                                <!-- New Password -->
                                <div class="col-md-4">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="row mt-5 mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-info-circle me-2"></i>Account Information
                                    </h5>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">User Type</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-<?php echo $user['user_type'] === 'donor' ? 'success' : ($user['user_type'] === 'admin' ? 'danger' : 'info'); ?> fs-6">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Status</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?> fs-6">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Member Since</label>
                                    <div class="form-control-plaintext">
                                        <?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>