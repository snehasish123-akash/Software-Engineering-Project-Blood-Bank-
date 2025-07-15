<?php
require_once '../config/database.php';

// Require admin login
requireAdmin();

try {
    $pdo = getDBConnection();
    
    // Get filters
    $blood_group_filter = $_GET['blood_group'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $where_conditions = ["user_type = 'donor'"];
    $params = [];
    
    if ($blood_group_filter) {
        $where_conditions[] = "blood_group = ?";
        $params[] = $blood_group_filter;
    }
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Get donors
    $stmt = $pdo->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC");
    $stmt->execute($params);
    $donors = $stmt->fetchAll();
    
    // Get statistics
    $stats = [];
    $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    foreach ($bloodGroups as $group) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'donor' AND blood_group = ? AND status = 'active'");
        $stmt->execute([$group]);
        $stats[$group] = $stmt->fetchColumn();
    }
    
    // Get status counts
    $status_counts = [];
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM users WHERE user_type = 'donor' GROUP BY status");
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("Admin donors error: " . $e->getMessage());
    $donors = [];
    $stats = [];
    $status_counts = [];
}

$message = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors - BBDMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .status-dropdown {
            min-width: 120px;
        }
        .status-badge {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .status-badge:hover {
            transform: scale(1.05);
        }
        .donor-card {
            transition: all 0.3s ease;
        }
        .donor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .last-activity {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .auto-inactive-warning {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            border-left: 4px solid #ffc107;
        }
    </style>
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
                        <a class="nav-link active" href="donors.php">
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
                            <a class="nav-link active" href="donors.php">
                                <i class="fas fa-heart me-2"></i>Donors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="requests.php">
                                <i class="fas fa-clipboard-list me-2"></i>Blood Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Blood Donors Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-warning me-2" onclick="checkInactiveDonors()">
                            <i class="fas fa-clock me-1"></i>Check Inactive Donors
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportDonors()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Auto-Inactive Warning -->
                <div class="alert auto-inactive-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Auto-Inactive Feature:</strong> Donors are automatically set to inactive after 3 months of no activity. 
                    Use the "Check Inactive Donors" button to manually run this process.
                </div>

                <!-- Status Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Active Donors</div>
                                        <div class="h5 mb-0"><?php echo $status_counts['active'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-heart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Donors</div>
                                        <div class="h5 mb-0"><?php echo $status_counts['pending'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Inactive Donors</div>
                                        <div class="h5 mb-0"><?php echo $status_counts['inactive'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-slash fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Donors</div>
                                        <div class="h5 mb-0"><?php echo array_sum($status_counts); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blood Group Statistics -->
                <div class="row mb-4">
                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-danger"><?php echo $group; ?></h3>
                                    <p class="mb-0"><?php echo $stats[$group] ?? 0; ?> Active Donors</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">All Blood Groups</option>
                                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?>
                                        <option value="<?php echo $group; ?>" <?php echo $blood_group_filter === $group ? 'selected' : ''; ?>><?php echo $group; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, email, or phone" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Donors Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-heart me-2"></i>Donors (<?php echo count($donors); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($donors)): ?>
                            <p class="text-muted text-center py-4">No donors found matching your criteria.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Blood Group</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                            <th>Status</th>
                                            <th>Last Activity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donors as $donor): ?>
                                            <tr id="donor-row-<?php echo $donor['id']; ?>">
                                                <td><?php echo htmlspecialchars($donor['full_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                                <td><?php echo htmlspecialchars($donor['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($donor['blood_group'] ?? 'N/A'); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($donor['gender'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($donor['date_of_birth']) {
                                                        $age = date_diff(date_create($donor['date_of_birth']), date_create('today'))->y;
                                                        echo $age . ' years';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm status-dropdown" 
                                                            onchange="updateDonorStatus(<?php echo $donor['id']; ?>, this.value)"
                                                            data-current-status="<?php echo $donor['status']; ?>">
                                                        <option value="active" <?php echo $donor['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="pending" <?php echo $donor['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="inactive" <?php echo $donor['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="last-activity">
                                                        <?php 
                                                        if ($donor['updated_at']) {
                                                            echo timeAgo($donor['updated_at']);
                                                            
                                                            // Check if donor should be auto-inactive
                                                            $three_months_ago = strtotime('-3 months');
                                                            $last_activity = strtotime($donor['updated_at']);
                                                            
                                                            if ($donor['status'] === 'active' && $last_activity < $three_months_ago) {
                                                                echo '<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Should be inactive</small>';
                                                            }
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewDonor(<?php echo $donor['id']; ?>)" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="messageDonor(<?php echo $donor['id']; ?>)" title="Send Message">
                                                            <i class="fas fa-envelope"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update donor status
        function updateDonorStatus(donorId, newStatus) {
            const dropdown = document.querySelector(`#donor-row-${donorId} .status-dropdown`);
            const originalStatus = dropdown.dataset.currentStatus;
            
            // Show loading state
            dropdown.disabled = true;
            
            fetch('update-donor-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `donor_id=${donorId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the current status
                    dropdown.dataset.currentStatus = newStatus;
                    
                    // Show success message
                    showAlert(data.message, 'success');
                    
                    // Update the row styling based on new status
                    updateRowStyling(donorId, newStatus);
                } else {
                    // Revert dropdown to original status
                    dropdown.value = originalStatus;
                    showAlert(data.message || 'Failed to update status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                dropdown.value = originalStatus;
                showAlert('An error occurred while updating status', 'danger');
            })
            .finally(() => {
                dropdown.disabled = false;
            });
        }
        
        // Update row styling based on status
        function updateRowStyling(donorId, status) {
            const row = document.getElementById(`donor-row-${donorId}`);
            
            // Remove existing status classes
            row.classList.remove('table-success', 'table-warning', 'table-secondary');
            
            // Add new status class
            switch(status) {
                case 'active':
                    row.classList.add('table-success');
                    break;
                case 'pending':
                    row.classList.add('table-warning');
                    break;
                case 'inactive':
                    row.classList.add('table-secondary');
                    break;
            }
        }
        
        // Check inactive donors
        function checkInactiveDonors() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Checking...';
            button.disabled = true;
            
            fetch('check-inactive-donors.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reload page to show updated statuses
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message || 'Failed to check inactive donors', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while checking inactive donors', 'danger');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        // Show alert function
        function showAlert(message, type = 'info') {
            const alertContainer = document.querySelector('main');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            alertContainer.insertBefore(alert, alertContainer.children[1]);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }
            }, 5000);
        }
        
        // View donor details (placeholder)
        function viewDonor(donorId) {
            // You can implement a modal or redirect to donor details page
            alert('View donor details functionality - Donor ID: ' + donorId);
        }
        
        // Message donor (placeholder)
        function messageDonor(donorId) {
            // You can implement messaging functionality
            alert('Message donor functionality - Donor ID: ' + donorId);
        }
        
        // Export donors (placeholder)
        function exportDonors() {
            alert('Export functionality will be implemented here');
        }
        
        // Initialize row styling on page load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('[id^="donor-row-"]');
            rows.forEach(row => {
                const dropdown = row.querySelector('.status-dropdown');
                if (dropdown) {
                    const status = dropdown.value;
                    updateRowStyling(row.id.replace('donor-row-', ''), status);
                }
            });
            
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