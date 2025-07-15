<?php
require_once '../config/database.php';

// Require admin login
requireAdmin();

$message = '';
$error = '';

try {
    $pdo = getDBConnection();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $settings = [
            'site_name' => sanitizeInput($_POST['site_name'] ?? ''),
            'site_description' => sanitizeInput($_POST['site_description'] ?? ''),
            'contact_email' => sanitizeInput($_POST['contact_email'] ?? ''),
            'contact_phone' => sanitizeInput($_POST['contact_phone'] ?? ''),
            'emergency_phone' => sanitizeInput($_POST['emergency_phone'] ?? ''),
            'contact_address' => sanitizeInput($_POST['contact_address'] ?? ''),
            'office_hours' => sanitizeInput($_POST['office_hours'] ?? ''),
            'about_us' => sanitizeInput($_POST['about_us'] ?? ''),
            'emergency_message' => sanitizeInput($_POST['emergency_message'] ?? '')
        ];
        
        $success_count = 0;
        foreach ($settings as $name => $value) {
            if (!empty($value)) {
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                if ($stmt->execute([$name, $value, $value])) {
                    $success_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $message = 'Settings updated successfully';
        } else {
            $error = 'No settings were updated';
        }
    }
    
    // Get current settings
    $current_settings = getSiteSettings();
    
} catch (Exception $e) {
    error_log("Admin settings error: " . $e->getMessage());
    $error = 'An error occurred while loading settings';
    $current_settings = [];
}

if ($message) setMessage($message, 'success');
if ($error) setMessage($error, 'error');
$sessionMessage = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BBDMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-tint me-2"></i>BBDMS Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-1"></i>Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donors.php">
                            <i class="fas fa-heart me-1"></i>Donors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requests.php">
                            <i class="fas fa-clipboard-list me-1"></i>Blood Requests
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="donors.php">
                                <i class="fas fa-heart me-2"></i>Donors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="requests.php">
                                <i class="fas fa-clipboard-list me-2"></i>Blood Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">System Settings</h1>
                </div>

                <?php if ($sessionMessage): ?>
                    <div class="alert alert-<?php echo $sessionMessage['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($sessionMessage['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Site Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-globe me-2"></i>Site Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($current_settings['site_name'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">Site Description</label>
                                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($current_settings['site_description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="about_us" class="form-label">About Us</label>
                                        <textarea class="form-control" id="about_us" name="about_us" rows="4"><?php echo htmlspecialchars($current_settings['about_us'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="contact_email" class="form-label">Contact Email</label>
                                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                                       value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                                       value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="emergency_phone" class="form-label">Emergency Phone</label>
                                                <input type="text" class="form-control" id="emergency_phone" name="emergency_phone" 
                                                       value="<?php echo htmlspecialchars($current_settings['emergency_phone'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="office_hours" class="form-label">Office Hours</label>
                                                <input type="text" class="form-control" id="office_hours" name="office_hours" 
                                                       value="<?php echo htmlspecialchars($current_settings['office_hours'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_address" class="form-label">Contact Address</label>
                                        <textarea class="form-control" id="contact_address" name="contact_address" rows="2"><?php echo htmlspecialchars($current_settings['contact_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Settings -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Emergency Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="emergency_message" class="form-label">Emergency Message</label>
                                        <textarea class="form-control" id="emergency_message" name="emergency_message" rows="3"><?php echo htmlspecialchars($current_settings['emergency_message'] ?? ''); ?></textarea>
                                        <div class="form-text">This message will be displayed on emergency-related pages.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- System Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>System Version:</strong> BBDMS v1.0</p>
                                    <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                                    <p><strong>Database:</strong> MySQL</p>
                                    <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                    <p><strong>Last Updated:</strong> <?php echo date('M d, Y H:i:s'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>