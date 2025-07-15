<?php
require_once '../config/database.php';

// Require seeker login
requireLogin();
if (!isSeeker()) {
    redirectTo('../index.php');
}

try {
    $pdo = getDBConnection();
    
    // Get seeker's information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $seeker = $stmt->fetch();
    
    // Get seeker's sent messages (FIXED: using sender_id instead of seeker_id)
    try {
        $stmt = $pdo->prepare("SELECT m.*, u.full_name as receiver_name FROM messages m LEFT JOIN users u ON m.receiver_id = u.id WHERE m.sender_id = ? ORDER BY m.created_at DESC LIMIT 10");
        $stmt->execute([$_SESSION['user_id']]);
        $messages = $stmt->fetchAll();
    } catch (Exception $e) {
        $messages = [];
    }
    
} catch (Exception $e) {
    error_log("Seeker dashboard error: " . $e->getMessage());
    $seeker = null;
    $messages = [];
}

$message = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seeker Dashboard - BBDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-tint me-2"></i>BBDMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search-donor.php">
                            <i class="fas fa-search me-1"></i>Search Donors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../donor-list.php">
                            <i class="fas fa-users me-1"></i>Donor List
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

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show">
                <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <h1 class="h2 mb-4">
                    <i class="fas fa-search text-info me-2"></i>Blood Seeker Dashboard
                </h1>
            </div>
        </div>

        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($seeker): ?>
                            <div class="text-center mb-3">
                                <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                    <?php echo strtoupper(substr($seeker['full_name'] ?? 'U', 0, 1)); ?>
                                </div>
                            </div>
                            <h6 class="text-center"><?php echo htmlspecialchars($seeker['full_name'] ?? 'Unknown'); ?></h6>
                            <hr>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($seeker['email']); ?></p>
                            <p><strong>Blood Group:</strong> 
                                <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($seeker['blood_group'] ?? 'N/A'); ?></span>
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $seeker['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($seeker['status']); ?>
                                </span>
                            </p>
                            <p><strong>Member Since:</strong> 
                                <?php echo isset($seeker['created_at']) ? date('M Y', strtotime($seeker['created_at'])) : 'N/A'; ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">Profile information not available.</p>
                        <?php endif; ?>
                        <div class="d-grid">
                            <a href="../profile.php" class="btn btn-outline-info">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="col-md-8">
                <!-- Welcome Message -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h5>
                        <p class="card-text">Use this dashboard to search for blood donors and manage your requests.</p>
                        <?php if ($seeker && $seeker['status'] === 'pending'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>Your account is pending approval. You'll be able to contact donors once approved by an administrator.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="../search-donor.php" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search for Donors
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="../donor-list.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-users me-2"></i>Browse All Donors
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="../profile.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-user-edit me-2"></i>Update Profile
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="../contact.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sent Messages -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Sent Messages</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <p class="text-muted">No messages sent yet. Start by searching for donors and contacting them for blood donation.</p>
                            <a href="../search-donor.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search Donors Now
                            </a>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($messages as $msg): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">To: <?php echo htmlspecialchars($msg['receiver_name'] ?? 'Unknown Donor'); ?></h6>
                                            <small><?php echo isset($msg['created_at']) ? timeAgo($msg['created_at']) : 'Unknown'; ?></small>
                                        </div>
                                        <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject'] ?? 'No subject'); ?></p>
                                        <p class="mb-1"><?php echo htmlspecialchars($msg['message'] ?? 'No message content'); ?></p>
                                        <small class="text-muted">Status: <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Cards -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-header">
                        <i class="fas fa-search me-2"></i>Find Donors
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Search by Blood Group</h5>
                        <p class="card-text">Use our search feature to find donors with compatible blood types in your area.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-header">
                        <i class="fas fa-comments me-2"></i>Contact Donors
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Send Messages</h5>
                        <p class="card-text">Reach out to donors directly through our secure messaging system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-header">
                        <i class="fas fa-heart me-2"></i>Emergency Help
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Urgent Requests</h5>
                        <p class="card-text">For emergency blood needs, contact multiple donors and local blood banks immediately.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                    setTimeout(function() {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 500);
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>