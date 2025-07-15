<?php
require_once 'config/database.php';

$donors = [];
$search_performed = false;
$blood_group = '';
$location = '';

try {
    $pdo = getDBConnection();
    
    // Handle search
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['blood_group']) || isset($_GET['location']))) {
        $blood_group = sanitizeInput($_GET['blood_group'] ?? '');
        $location = sanitizeInput($_GET['location'] ?? '');
        
        $search_performed = true;
        
        // Build query
        $query = "SELECT id, full_name, blood_group, gender, phone, address FROM users WHERE user_type = 'donor' AND status = 'active'";
        $params = [];
        
        if (!empty($blood_group)) {
            $query .= " AND blood_group = ?";
            $params[] = $blood_group;
        }
        
        if (!empty($location)) {
            $query .= " AND address LIKE ?";
            $params[] = "%$location%";
        }
        
        $query .= " ORDER BY full_name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $donors = $stmt->fetchAll();
    }
    
    // Get blood group counts
    $bloodGroupCounts = [];
    $stmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM users WHERE user_type = 'donor' AND status = 'active' AND blood_group IS NOT NULL GROUP BY blood_group ORDER BY blood_group");
    while ($row = $stmt->fetch()) {
        $bloodGroupCounts[$row['blood_group']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("Error in search-donor.php: " . $e->getMessage());
    $donors = [];
    $bloodGroupCounts = [];
}

// Handle any messages
$message = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Donor - BBDMS</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donor-list.php">
                            <i class="fas fa-users me-1"></i>Donor List
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="search-donor.php">
                            <i class="fas fa-search me-1"></i>Search Donor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact Us
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">
                                        <i class="fas fa-cog me-2"></i>Admin Panel
                                    </a></li>
                                <?php elseif (isDonor()): ?>
                                    <li><a class="dropdown-item" href="donor/dashboard.php">
                                        <i class="fas fa-heart me-2"></i>Donor Panel
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="seeker/dashboard.php">
                                        <i class="fas fa-search me-2"></i>My Dashboard
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-edit me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" style="margin-top: 76px;">
            <div class="container">
                <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <section class="py-5 bg-light" style="margin-top: <?php echo $message ? '120px' : '76px'; ?>;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-primary mb-3">
                        <i class="fas fa-search me-3"></i>Search Blood Donors
                    </h1>
                    <p class="lead text-muted">Find compatible blood donors in your area</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="fas fa-filter me-2"></i>Search Filters
                            </h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select" id="blood_group" name="blood_group">
                                        <option value="">All Blood Groups</option>
                                        <?php foreach ($bloodGroupCounts as $group => $count): ?>
                                            <option value="<?php echo htmlspecialchars($group); ?>" <?php echo $blood_group === $group ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($group); ?> (<?php echo $count; ?> donors)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           placeholder="Enter city, area, or address" 
                                           value="<?php echo htmlspecialchars($location); ?>">
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Search Donors
                                        </button>
                                        <a href="search-donor.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <?php if ($search_performed): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-primary">
                            <i class="fas fa-list me-2"></i>Search Results
                        </h3>
                        <span class="badge bg-primary fs-6"><?php echo count($donors); ?> donors found</span>
                    </div>
                    
                    <?php if (empty($donors)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No donors found</h4>
                            <p class="text-muted">Try adjusting your search criteria or browse all donors.</p>
                            <a href="donor-list.php" class="btn btn-primary">
                                <i class="fas fa-users me-2"></i>View All Donors
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($donors as $donor): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="donor-card">
                                        <!-- Donor Avatar -->
                                        <div class="donor-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        
                                        <!-- Donor Info -->
                                        <div class="text-center">
                                            <h5 class="mb-2"><?php echo htmlspecialchars($donor['full_name']); ?></h5>
                                            
                                            <div class="mb-3">
                                                <span class="blood-type-badge"><?php echo htmlspecialchars($donor['blood_group']); ?></span>
                                            </div>
                                            
                                            <div class="donor-details">
                                                <p class="mb-2">
                                                    <i class="fas fa-venus-mars me-2 text-muted"></i>
                                                    <?php echo htmlspecialchars($donor['gender']); ?>
                                                </p>
                                                
                                                <?php if ($donor['address']): ?>
                                                    <p class="mb-3 text-muted">
                                                        <i class="fas fa-map-marker-alt me-2"></i>
                                                        <?php echo htmlspecialchars(substr($donor['address'], 0, 30)) . (strlen($donor['address']) > 30 ? '...' : ''); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Action Button -->
                                            <button class="btn btn-primary message-btn" 
                                                    data-donor-id="<?php echo $donor['id']; ?>"
                                                    data-donor-name="<?php echo htmlspecialchars($donor['full_name']); ?>">
                                                <i class="fas fa-envelope me-2"></i>Message
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Blood Compatibility Guide -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Blood Compatibility Guide</h2>
                    <p class="lead text-muted">Understanding who can donate to whom</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Can Donate To</th>
                                    <th>Can Receive From</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>O-</strong></td>
                                    <td>Everyone (Universal Donor)</td>
                                    <td>O-</td>
                                </tr>
                                <tr>
                                    <td><strong>O+</strong></td>
                                    <td>O+, A+, B+, AB+</td>
                                    <td>O-, O+</td>
                                </tr>
                                <tr>
                                    <td><strong>A-</strong></td>
                                    <td>A-, A+, AB-, AB+</td>
                                    <td>O-, A-</td>
                                </tr>
                                <tr>
                                    <td><strong>A+</strong></td>
                                    <td>A+, AB+</td>
                                    <td>O-, O+, A-, A+</td>
                                </tr>
                                <tr>
                                    <td><strong>B-</strong></td>
                                    <td>B-, B+, AB-, AB+</td>
                                    <td>O-, B-</td>
                                </tr>
                                <tr>
                                    <td><strong>B+</strong></td>
                                    <td>B+, AB+</td>
                                    <td>O-, O+, B-, B+</td>
                                </tr>
                                <tr>
                                    <td><strong>AB-</strong></td>
                                    <td>AB-, AB+</td>
                                    <td>O-, A-, B-, AB-</td>
                                </tr>
                                <tr>
                                    <td><strong>AB+</strong></td>
                                    <td>AB+</td>
                                    <td>Everyone (Universal Recipient)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="messageForm">
                    <div class="modal-body">
                        <input type="hidden" id="donorId" name="donor_id">
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="messageText" class="form-label">Message</label>
                            <textarea class="form-control" id="messageText" name="message" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Set user login status for JavaScript
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        
        // Handle message button clicks
        document.querySelectorAll('.message-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!userLoggedIn) {
                    window.location.href = 'login.php?message=' + encodeURIComponent('Please login to message donors');
                    return;
                }
                
                const donorId = this.getAttribute('data-donor-id');
                const donorName = this.getAttribute('data-donor-name');
                
                document.getElementById('donorId').value = donorId;
                document.querySelector('#messageModal .modal-title').textContent = `Message ${donorName}`;
                
                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                modal.show();
            });
        });
        
        // Handle message form submission
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('send-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message sent successfully!');
                    this.reset();
                    bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
                } else {
                    alert(data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the message');
            });
        });
    </script>
</body>
</html>