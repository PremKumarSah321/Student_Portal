<?php
/**
 * Security Configuration for Student Course Hub
 * This file contains security settings and constants
 */

// Security constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_NAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 255);
define('MAX_DESCRIPTION_LENGTH', 2000);

// Input validation patterns
define('NAME_PATTERN', '/^[a-zA-Z\s\-\'\.]+$/');
define('EMAIL_PATTERN', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

// Suspicious input patterns (for XSS detection)
$SUSPICIOUS_PATTERNS = [
    '/<script[^>]*>.*?<\/script>/is',
    '/<iframe[^>]*>.*?<\/iframe>/is',
    '/<object[^>]*>.*?<\/object>/is',
    '/<embed[^>]*>/i',
    '/<link[^>]*>/i',
    '/<meta[^>]*>/i',
    '/javascript:/i',
    '/vbscript:/i',
    '/data:text\/html/i',
    '/on\w+\s*=/i',
    '/<\s*\w+[^>]*\s+on\w+\s*=/i',
    '/expression\s*\(/i',
    '/@import/i',
    '/binding\s*:/i',
    '/<\s*style[^>]*>.*?<\/style>/is',
    '/<\s*form[^>]*>/i',
    '/<\s*input[^>]*>/i',
    '/<\s*textarea[^>]*>/i',
    '/<\s*select[^>]*>/i',
    '/<\s*button[^>]*>/i'
];

// File upload security (if implemented later)
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', 'uploads/');

// Security headers configuration
$SECURITY_HEADERS = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self';",
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
];

// Rate limiting configuration
$RATE_LIMITS = [
    'login' => ['attempts' => 5, 'window' => 900], // 5 attempts per 15 minutes
    'interest_registration' => ['attempts' => 3, 'window' => 300], // 3 attempts per 5 minutes
    'staff_save' => ['attempts' => 10, 'window' => 300], // 10 attempts per 5 minutes
    'programme_save' => ['attempts' => 10, 'window' => 300],
    'module_save' => ['attempts' => 10, 'window' => 300],
    'staff_details' => ['attempts' => 20, 'window' => 300], // 20 requests per 5 minutes
    'search' => ['attempts' => 50, 'window' => 300] // 50 searches per 5 minutes
];

/**
 * Initialize security settings (call before session_start)
 */
function initializeSecurity() {
    // Only set session parameters if session hasn't started yet
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    }
    
    // Disable PHP version disclosure
    ini_set('expose_php', 0);
    
    // Set error reporting for production
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    }
}

/**
 * Configure session security (call before session_start)
 */
function configureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        ini_set('session.name', 'STUDENT_PORTAL_SESSION');
    }
}

/**
 * Check if request is from allowed origin (CSRF protection)
 */
function validateOrigin() {
    if (!isset($_SERVER['HTTP_HOST'])) {
        return false;
    }
    
    $allowedHosts = [
        'localhost',
        '127.0.0.1',
        $_SERVER['HTTP_HOST']
    ];
    
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        return in_array($origin, $allowedHosts);
    }
    
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        return in_array($referer, $allowedHosts);
    }
    
    return true; // Allow if no origin/referer (direct access)
}

// Initialize basic security settings (not session-related)
ini_set('expose_php', 0);
if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}
?>