<?php require_once 'config/database.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BBDMS</title>
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
                        <a class="nav-link active" href="about.php">
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

    <!-- Page Header -->
    <section class="py-5 bg-light" style="margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-primary mb-3">About BBDMS</h1>
                    <p class="lead text-muted">Connecting hearts, saving lives through blood donation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold text-primary mb-4">Our Mission</h2>
                    <p class="lead mb-4">
                        BBDMS (Blood Bank Donor Management System) is dedicated to creating a seamless bridge between blood donors and those in critical need. Our mission is to save lives by making blood donation accessible, efficient, and impactful.
                    </p>
                    <p class="mb-4">
                        We believe that every drop of blood donated has the power to save up to three lives. Through our comprehensive platform, we're building a community of life-savers who are ready to make a difference when it matters most.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="text-primary">1000+</h3>
                                <p class="text-muted">Lives Saved</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="text-primary">500+</h3>
                                <p class="text-muted">Active Donors</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-heartbeat text-primary" style="font-size: 10rem; opacity: 0.1;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Our Core Values</h2>
                    <p class="lead text-muted">The principles that guide everything we do</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="process-icon mb-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Compassion</h5>
                        <p class="text-muted">We approach every interaction with empathy and understanding, recognizing the critical nature of blood donation.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="process-icon mb-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Safety</h5>
                        <p class="text-muted">We maintain the highest standards of safety and quality in all our processes and procedures.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="process-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Community</h5>
                        <p class="text-muted">We foster a strong community of donors and recipients working together to save lives.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">How BBDMS Works</h2>
                    <p class="lead text-muted">Simple steps to connect donors with those in need</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5>Register</h5>
                        <p>Donors and seekers register on our platform with their medical information and contact details.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>Search</h5>
                        <p>Seekers can search for compatible donors based on blood type, location, and availability.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5>Connect</h5>
                        <p>Our messaging system allows direct communication between donors and seekers to coordinate donation.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card text-center">
                        <div class="process-icon">
                            <i class="fas fa-life-ring"></i>
                        </div>
                        <h5>Save Lives</h5>
                        <p>Successful donations help save lives and strengthen our community of life-savers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Donation Facts -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Blood Donation Facts</h2>
                    <p class="lead text-muted">Important information about blood donation</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="fas fa-info-circle me-2"></i>Did You Know?
                            </h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>One donation can save up to 3 lives</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Only 3% of eligible people donate blood</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Blood cannot be manufactured artificially</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Every 2 seconds someone needs blood</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Donated blood has a shelf life of 42 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="fas fa-heart me-2"></i>Donation Benefits
                            </h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Free health screening</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Reduces risk of heart disease</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Burns calories (650 per donation)</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Stimulates new blood cell production</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Provides emotional satisfaction</li>
                            </ul>
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
                    <h2 class="display-5 fw-bold text-white mb-4">Ready to Save Lives?</h2>
                    <p class="lead text-white-50 mb-4">
                        Join our community of heroes and make a difference in someone's life today.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="register.php?type=donor" class="btn btn-accent btn-lg">
                            <i class="fas fa-heart me-2"></i>Become a Donor
                        </a>
                        <a href="search-donor.php" class="btn btn-outline-light btn-lg">
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
    <script src="assets/js/main.js"></script>
</body>
</html>