<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get statistics with error handling for missing tables
    try {
        $donorCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'donor' AND status = 'active'")->fetchColumn();
    } catch (Exception $e) {
        $donorCount = 0;
    }
    
    try {
        $seekerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'seeker' AND status = 'active'")->fetchColumn();
    } catch (Exception $e) {
        $seekerCount = 0;
    }
    
    try {
        $requestCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        $requestCount = 0;
    }
    
    try {
        $messageCount = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    } catch (Exception $e) {
        $messageCount = 0;
    }
    
    // Get site settings
    $settings = getSiteSettings();
    
} catch (Exception $e) {
    error_log("Error on homepage: " . $e->getMessage());
    $donorCount = $seekerCount = $requestCount = $messageCount = 0;
    $settings = [];
}

// Handle any messages
$message = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_name'] ?? 'BBDMS - Blood Bank Donor Management System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
    /* Fix hero section buttons not clickable */
    .hero-section .col-lg-6:first-child {
        position: relative;
        z-index: 2;
    }

    .hero-section .col-lg-6:last-child {
        position: relative;
        z-index: 1;
    }
    </style>

    <script>
        var userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
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
                        <a class="nav-link active" href="index.php">
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
                        <a class="nav-link" href="search-donor.php">
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Save Lives Through <span class="text-accent">Blood Donation</span>
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        <?php echo htmlspecialchars($settings['site_description'] ?? 'Join our community of life-savers. Every drop counts, every donation matters. Connect with donors and help save lives in your community.'); ?>
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                    <a href="register.php?type=donor" class="btn btn-accent btn-lg">
                            <i class="fas fa-heart me-2"></i>Become a Donor
                        </a>
                        <a href="register.php?type=seeker" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>Find Blood
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-stats">
                        <div class="stat-card">
                            <h3><?php echo $donorCount; ?></h3>
                            <p>Active Donors</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $requestCount; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $messageCount; ?></h3>
                            <p>Messages Sent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Donation Process -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Blood Donation Process</h2>
                    <p class="lead text-muted">Simple steps to save lives</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5>1. Register</h5>
                        <p>Sign up as a donor and complete your profile with medical information and contact details.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5>2. Get Approved</h5>
                        <p>Our admin team reviews your application to ensure you meet donation requirements.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5>3. Connect</h5>
                        <p>Receive messages from seekers and coordinate donation times and locations.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-life-ring"></i>
                        </div>
                        <h5>4. Save Lives</h5>
                        <p>Your donation helps save up to 3 lives and makes a real difference in the community.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Groups Info -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Blood Group Statistics</h2>
                    <p class="lead text-muted">Current donor availability by blood type</p>
                </div>
            </div>
            <div class="row g-3">
                <?php
                $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($bloodGroups as $group) {
                    try {
                        $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE blood_group = ? AND user_type = 'donor' AND status = 'active'");
                        $count->execute([$group]);
                        $donorCount = $count->fetchColumn();
                    } catch (Exception $e) {
                        $donorCount = 0;
                    }
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="blood-group-card">
                        <div class="blood-group-icon"><?php echo $group; ?></div>
                        <div class="blood-group-count"><?php echo $donorCount; ?> Donors</div>
                        <?php if ($donorCount > 0): ?>
                            <a href="search-donor.php?blood_group=<?php echo urlencode($group); ?>" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-search me-1"></i>Find Donors
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- Why Donate Blood -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Why Donate Blood?</h2>
                    <p class="lead text-muted">The impact of your donation</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-heart text-danger mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Save Lives</h5>
                            <p class="card-text">One donation can save up to 3 lives. Your blood can help accident victims, surgery patients, and those with chronic illnesses.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-shield-alt text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Health Benefits</h5>
                            <p class="card-text">Regular donation can reduce the risk of heart disease, help maintain healthy iron levels, and provide free health screenings.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-users text-info mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Community Impact</h5>
                            <p class="card-text">Join a community of heroes making a difference. Your donation stays local and helps people in your own community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold text-white mb-4">Ready to Make a Difference?</h2>
                    <p class="lead text-white-50 mb-4">
                        Join thousands of donors who have already saved lives through blood donation.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="register.php?type=donor" class="btn btn-accent btn-lg">
                            <i class="fas fa-heart me-2"></i>Become a Donor
                        </a>
                        <a href="register.php?type=seeker" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>Find Blood
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
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

            // Handle responsive navigation
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            if (navbarToggler && navbarCollapse) {
                // Close mobile menu when clicking on a link
                const navLinks = navbarCollapse.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    });
                });
            }

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>