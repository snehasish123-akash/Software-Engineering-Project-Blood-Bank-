<?php
require_once '../config/database.php';

// Require admin login
requireAdmin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

try {
    $pdo = getDBConnection();
    
    // Handle actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'approve') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $message = 'User approved successfully';
            } else {
                $error = 'Failed to approve user';
            }
        } elseif ($action === 'reject') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $message = 'User rejected successfully';
            } else {
                $error = 'Failed to reject user';
            }
        } elseif ($action === 'delete') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
            if ($stmt->execute([$user_id])) {
                $message = 'User deleted successfully';
            } else {
                $error = 'Failed to delete user';
            }
        }
    }
    
    // Get filters
    $status_filter = $_GET['status'] ?? '';
    $type_filter = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($type_filter) {
        $where_conditions[] = "user_type = ?";
        $params[] = $type_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get users
    $stmt = $pdo->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin users error: " . $e->getMessage());
    $error = 'An error occurred while loading users';
    $users = [];
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
    <title>Manage Users - BBDMS Admin</title>
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
                        <a class="nav-link active" href="users.php">
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
                            <a class="nav-link active" href="users.php">
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
                    <h1 class="h2">Manage Users</h1>
                </div>

                <?php if ($sessionMessage): ?>
                    <div class="alert alert-<?php echo $sessionMessage['type']; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($sessionMessage['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">User Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="donor" <?php echo $type_filter === 'donor' ? 'selected' : ''; ?>>Donor</option>
                                    <option value="seeker" <?php echo $type_filter === 'seeker' ? 'selected' : ''; ?>>Seeker</option>
                                    <option value="admin" <?php echo $type_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, email, or username" value="<?php echo htmlspecialchars($search); ?>">
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

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Users (<?php echo count($users); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <p class="text-muted text-center py-4">No users found matching your criteria.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Type</th>
                                            <th>Blood Group</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['user_type'] === 'donor' ? 'success' : ($user['user_type'] === 'admin' ? 'danger' : 'info'); ?>">
                                                        <?php echo ucfirst($user['user_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['blood_group'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($user['status'] === 'pending'): ?>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Approve this user?')">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this user?')">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="action" value="reject" class="btn btn-warning btn-sm">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['user_type'] !== 'admin' || $user['id'] != $_SESSION['user_id']): ?>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user? This action cannot be undone.')">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
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