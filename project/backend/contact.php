<?php
require_once 'config/database.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO contact_queries (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
                $success = 'Thank you for your message! We will get back to you soon.';
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = 'Failed to send message. Please try again.';
        }
    }
}

// Handle any messages
$message = getMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BBDMS</title>
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
                        <a class="nav-link" href="search-donor.php">
                            <i class="fas fa-search me-1"></i>Search Donor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">
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
                    <h1 class="display-4 fw-bold text-primary mb-3">Contact Us</h1>
                    <p class="lead text-muted">Get in touch with our team - we're here to help</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="h-100">
                        <h3 class="text-primary mb-4">Get In Touch</h3>
                        <p class="text-muted mb-4">
                            Have questions about blood donation or need assistance with our platform? 
                            We're here to help you 24/7.
                        </p>
                        
                        <div class="contact-info">
                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Address</h6>
                                        <p class="text-muted mb-0">123 Blood Bank Street<br>Medical District, City 12345</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Phone</h6>
                                        <p class="text-muted mb-0">
                                            <a href="tel:+1234567890" class="text-decoration-none">+1-234-567-8900</a><br>
                                            <small>24/7 Emergency Hotline</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Email</h6>
                                        <p class="text-muted mb-0">
                                            <a href="mailto:contact@bbdms.com" class="text-decoration-none">contact@bbdms.com</a><br>
                                            <a href="mailto:emergency@bbdms.com" class="text-decoration-none">emergency@bbdms.com</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Operating Hours</h6>
                                        <p class="text-muted mb-0">
                                            Mon - Fri: 8:00 AM - 8:00 PM<br>
                                            Sat - Sun: 9:00 AM - 6:00 PM<br>
                                            <strong>Emergency: 24/7</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="fas fa-paper-plane me-2"></i>Send us a Message
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                        <div class="invalid-feedback">Please provide your name.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                        <div class="invalid-feedback">Please provide a valid email.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">Select a subject</option>
                                            <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="Blood Donation" <?php echo ($_POST['subject'] ?? '') === 'Blood Donation' ? 'selected' : ''; ?>>Blood Donation</option>
                                            <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                            <option value="Emergency Request" <?php echo ($_POST['subject'] ?? '') === 'Emergency Request' ? 'selected' : ''; ?>>Emergency Request</option>
                                            <option value="Partnership" <?php echo ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                            <option value="Feedback" <?php echo ($_POST['subject'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                            <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a subject.</div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="message" class="form-label">Message *</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required 
                                                  placeholder="Please describe your inquiry in detail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                        <div class="invalid-feedback">Please provide your message.</div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Section -->
    <section class="py-5 bg-danger text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>Emergency Blood Needed?
                    </h3>
                    <p class="mb-0">
                        For urgent blood requirements, call our 24/7 emergency hotline immediately. 
                        Our team will help you find compatible donors in your area.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="tel:+1234567890" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Emergency Hotline
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Frequently Asked Questions</h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    How do I register as a blood donor?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click on "Register" and select "Blood Donor". Fill out the registration form with your personal and medical information. Your account will be reviewed by our admin team before activation.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    How can I search for blood donors?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Use our "Search Donor" feature to find donors by blood group and location. You can also browse the "Donor List" to see all available donors and contact them directly.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    Is my personal information secure?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we take privacy seriously. Only essential information (name, blood group, gender) is visible to other users. Your contact details are only shared when you choose to communicate with someone.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                    How often can I donate blood?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Generally, you can donate whole blood every 56 days (8 weeks). However, this may vary based on your health condition and local regulations. Always consult with medical professionals.
                                </div>
                            </div>
                        </div>
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
    
    <style>
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .contact-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
    </style>
</body>
</html>