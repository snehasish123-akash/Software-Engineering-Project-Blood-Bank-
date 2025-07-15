<?php
// Database configuration for BBDMS
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bbdms');

// Site configuration
define('SITE_URL', 'http://localhost/bbdms/');
define('SITE_NAME', 'BBDMS');
define('ADMIN_EMAIL', 'admin@bbdms.com');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create database connection with error handling
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please check your configuration.");
    }
}

// Helper functions for user authentication
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isDonor() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'donor';
}

function isSeeker() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seeker';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?message=" . urlencode("Please login to access this page"));
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php?error=" . urlencode("Access denied. Admin privileges required."));
        exit();
    }
}

function redirectTo($url) {
    header("Location: " . $url);
    exit();
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get site settings
function getSiteSettings() {
    static $settings = null;
    if ($settings === null) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT setting_name, setting_value FROM site_settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_name']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error fetching site settings: " . $e->getMessage());
            $settings = [
                'site_name' => 'BBDMS - Blood Bank Donor Management System',
                'site_description' => 'Join our community of life-savers. Every drop counts, every donation matters. Connect with donors and help save lives in your community.'
            ];
        }
    }
    return $settings;
}

// Format date for display
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

// Get time ago format
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

// Error and success message handling
function setMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Blood group compatibility
function getCompatibleDonors($bloodGroup) {
    $compatibility = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-']
    ];
    
    return $compatibility[$bloodGroup] ?? [];
}

function getCompatibleRecipients($bloodGroup) {
    $compatibility = [
        'A+' => ['A+', 'AB+'],
        'A-' => ['A+', 'A-', 'AB+', 'AB-'],
        'B+' => ['B+', 'AB+'],
        'B-' => ['B+', 'B-', 'AB+', 'AB-'],
        'AB+' => ['AB+'],
        'AB-' => ['AB+', 'AB-'],
        'O+' => ['A+', 'B+', 'AB+', 'O+'],
        'O-' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
    ];
    
    return $compatibility[$bloodGroup] ?? [];
}
?>