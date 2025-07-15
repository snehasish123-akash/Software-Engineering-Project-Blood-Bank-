<?php
// Get site settings for footer
$settings = getSiteSettings();
?>

<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="text-primary mb-3">
                    <i class="fas fa-tint me-2"></i><?php echo $settings['site_name'] ?? 'BBDMS'; ?>
                </h5>
                <p class="text-light-50">
                    <?php echo $settings['site_description'] ?? 'Connecting hearts, saving lives through blood donation. Join our community of life-savers and make a difference.'; ?>
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-primary mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-light-50 text-decoration-none">Home</a></li>
                    <li><a href="about.php" class="text-light-50 text-decoration-none">About Us</a></li>
                    <li><a href="donor-list.php" class="text-light-50 text-decoration-none">Donor List</a></li>
                    <li><a href="search-donor.php" class="text-light-50 text-decoration-none">Search Donor</a></li>
                    <li><a href="contact.php" class="text-light-50 text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-primary mb-3">For Users</h6>
                <ul class="list-unstyled">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profile.php" class="text-light-50 text-decoration-none">My Profile</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php" class="text-light-50 text-decoration-none">Admin Panel</a></li>
                        <?php elseif (isDonor()): ?>
                            <li><a href="donor/dashboard.php" class="text-light-50 text-decoration-none">Donor Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="seeker/dashboard.php" class="text-light-50 text-decoration-none">My Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php" class="text-light-50 text-decoration-none">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="text-light-50 text-decoration-none">Login</a></li>
                        <li><a href="register.php" class="text-light-50 text-decoration-none">Register</a></li>
                        <li><a href="register.php?type=donor" class="text-light-50 text-decoration-none">Become a Donor</a></li>
                        <li><a href="register.php?type=seeker" class="text-light-50 text-decoration-none">Find Blood</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="col-lg-4 mb-4">
                <h6 class="text-primary mb-3">Contact Information</h6>
                <div class="contact-info">
                    <?php if (!empty($settings['contact_address'])): ?>
                        <p class="text-light-50 mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($settings['contact_address']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['contact_phone'])): ?>
                        <p class="text-light-50 mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?php echo htmlspecialchars($settings['contact_phone']); ?>" class="text-light-50 text-decoration-none">
                                <?php echo htmlspecialchars($settings['contact_phone']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['emergency_phone'])): ?>
                        <p class="text-light-50 mb-2">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            <strong>Emergency:</strong>
                            <a href="tel:<?php echo htmlspecialchars($settings['emergency_phone']); ?>" class="text-warning text-decoration-none">
                                <?php echo htmlspecialchars($settings['emergency_phone']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['contact_email'])): ?>
                        <p class="text-light-50 mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?php echo htmlspecialchars($settings['contact_email']); ?>" class="text-light-50 text-decoration-none">
                                <?php echo htmlspecialchars($settings['contact_email']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['office_hours'])): ?>
                        <p class="text-light-50 mb-0">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo htmlspecialchars($settings['office_hours']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-light-50 mb-0">
                    &copy; <?php echo date('Y'); ?> <?php echo $settings['site_name'] ?? 'BBDMS'; ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-light-50 mb-0">
                    Made with <i class="fas fa-heart text-danger"></i> for humanity
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
.text-light-50 {
    color: rgba(255, 255, 255, 0.7) !important;
}

.text-light-50:hover {
    color: rgba(255, 255, 255, 1) !important;
}

.social-links a {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.social-links a:hover {
    background-color: var(--primary-color);
    transform: translateY(-2px);
}

.contact-info i {
    width: 20px;
    text-align: center;
}
</style>