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
        if ($action === 'update_status') {
            $request_id = (int)($_POST['request_id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');
            
            if (in_array($status, ['pending', 'fulfilled', 'cancelled'])) {
                $stmt = $pdo->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
                if ($stmt->execute([$status, $request_id])) {
                    $message = 'Request status updated successfully';
                } else {
                    $error = 'Failed to update request status';
                }
            }
        }
    }
    
    // Get filters
    $status_filter = $_GET['status'] ?? '';
    $blood_group_filter = $_GET['blood_group'] ?? '';
    $urgency_filter = $_GET['urgency'] ?? '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "br.status = ?";
        $params[] = $status_filter;
    }
    
    if ($blood_group_filter) {
        $where_conditions[] = "br.blood_group = ?";
        $params[] = $blood_group_filter;
    }
    
    if ($urgency_filter) {
        $where_conditions[] = "br.urgency = ?";
        $params[] = $urgency_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get blood requests with seeker information
    $stmt = $pdo->prepare("
        SELECT br.*, u.full_name as seeker_name, u.email as seeker_email, u.phone as seeker_phone 
        FROM blood_requests br 
        JOIN users u ON br.seeker_id = u.id 
        $where_clause 
        ORDER BY br.created_at DESC
    ");
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin requests error: " . $e->getMessage());
    $error = 'An error occurred while loading requests';
    $requests = [];
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
    <title>Blood Requests - BBDMS Admin</title>
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
                        <a class="nav-link active" href="requests.php">
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
                            <a class="nav-link active" href="requests.php">
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
                    <h1 class="h2">Blood Requests</h1>
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
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="fulfilled" <?php echo $status_filter === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
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
                                <label class="form-label">Urgency</label>
                                <select name="urgency" class="form-select">
                                    <option value="">All Urgency</option>
                                    <option value="Low" <?php echo $urgency_filter === 'Low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="Medium" <?php echo $urgency_filter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="High" <?php echo $urgency_filter === 'High' ? 'selected' : ''; ?>>High</option>
                                    <option value="Critical" <?php echo $urgency_filter === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Blood Requests (<?php echo count($requests); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($requests)): ?>
                            <p class="text-muted text-center py-4">No blood requests found matching your criteria.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Seeker</th>
                                            <th>Blood Group</th>
                                            <th>Units</th>
                                            <th>Urgency</th>
                                            <th>Hospital</th>
                                            <th>Needed Date</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['seeker_name'] ?? 'N/A'); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['seeker_email']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($request['blood_group']); ?></span>
                                                </td>
                                                <td><?php echo $request['units_needed']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $request['urgency'] === 'Critical' ? 'danger' : 
                                                            ($request['urgency'] === 'High' ? 'warning' : 
                                                            ($request['urgency'] === 'Medium' ? 'info' : 'secondary')); 
                                                    ?>">
                                                        <?php echo $request['urgency']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['hospital_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['contact_person']); ?></small>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['needed_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $request['status'] === 'fulfilled' ? 'success' : ($request['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($request['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                    <input type="hidden" name="status" value="fulfilled">
                                                                    <button type="submit" name="action" value="update_status" class="dropdown-item">
                                                                        <i class="fas fa-check me-2"></i>Mark Fulfilled
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                    <input type="hidden" name="status" value="cancelled">
                                                                    <button type="submit" name="action" value="update_status" class="dropdown-item">
                                                                        <i class="fas fa-times me-2"></i>Mark Cancelled
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                    <input type="hidden" name="status" value="pending">
                                                                    <button type="submit" name="action" value="update_status" class="dropdown-item">
                                                                        <i class="fas fa-clock me-2"></i>Mark Pending
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
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
</body>
</html>