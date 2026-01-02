<?php
/**
 * Database Configuration
 * Perpinfo - Library Management System
 * 
 * @author Perpinfo Team
 * @version 2.0
 * @date 2026-01-02
 */

// Prevent direct access
if (!defined('DIRECT_ACCESS')) {
    define('DIRECT_ACCESS', true);
}

// Error Reporting (Development mode - ubah ke false di production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan_db');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Perpinfo');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/perpinfo-main'); // Sesuaikan dengan URL aplikasi Anda

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'PERPINFO_SESSION');

// File Upload Configuration
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination Configuration
define('ITEMS_PER_PAGE', 12);

// Security Configuration
define('HASH_COST', 12); // Password hash cost
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

/**
 * Database Connection with Error Handling
 */
try {
    // Create connection
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to UTF-8 (support for international characters)
    if (!mysqli_set_charset($conn, DB_CHARSET)) {
        throw new Exception("Error setting charset: " . mysqli_error($conn));
    }
    
    // Set timezone
    mysqli_query($conn, "SET time_zone = '+07:00'"); // WIB timezone
    
} catch (Exception $e) {
    // Display user-friendly error
    if (DEBUG_MODE) {
        die("
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 30px; background: #fee; border: 2px solid #c33; border-radius: 10px;'>
                <h2 style='color: #c33; margin-top: 0;'>⚠️ Database Connection Error</h2>
                <p style='color: #666;'><strong>Error:</strong> {$e->getMessage()}</p>
                <p style='color: #666; margin-bottom: 0;'><strong>File:</strong> " . __FILE__ . "</p>
                <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                <p style='color: #999; font-size: 12px; margin-bottom: 0;'>
                    <strong>Debug Info:</strong><br>
                    Host: " . DB_HOST . "<br>
                    Database: " . DB_NAME . "<br>
                    User: " . DB_USER . "
                </p>
            </div>
        ");
    } else {
        die("
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 30px; background: #fee; border: 2px solid #c33; border-radius: 10px; text-align: center;'>
                <h2 style='color: #c33;'>⚠️ Service Temporarily Unavailable</h2>
                <p style='color: #666;'>We're sorry, but we're unable to connect to the database at this time. Please try again later.</p>
                <p style='color: #999; font-size: 12px;'>If the problem persists, please contact the system administrator.</p>
            </div>
        ");
    }
}

/**
 * Secure Escape Function
 * Sanitizes user input to prevent SQL injection
 * 
 * @param mysqli $conn Database connection
 * @param string $data User input data
 * @return string Sanitized data
 */
function escape($conn, $data) {
    if ($data === null) {
        return null;
    }
    return mysqli_real_escape_string($conn, trim($data));
}

/**
 * Sanitize HTML Output
 * Prevents XSS attacks
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate Email
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Phone Number (Indonesia format)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    // Check if starts with 0 or +62 or 62 and has 10-13 digits
    return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $phone);
}

/**
 * Generate CSRF Token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Secure Session Start
 * Configures session with security best practices
 */
function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters
        $cookieParams = [
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set true jika menggunakan HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        session_set_cookie_params($cookieParams);
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate session every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Set session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            // Last request was more than SESSION_LIFETIME ago
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Check if User is Logged In
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check if User is Admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if User is Member
 * 
 * @return bool True if member, false otherwise
 */
function isMember() {
    return isLoggedIn() && $_SESSION['user_type'] === 'member';
}

/**
 * Redirect Function
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code (default: 302)
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Format Date to Indonesian
 * 
 * @param string $date Date string
 * @param string $format Format (short/long)
 * @return string Formatted date
 */
function formatDateIndo($date, $format = 'short') {
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $days = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    if ($format === 'long') {
        $dayName = $days[date('l', $timestamp)];
        return "$dayName, $day $month $year";
    }
    
    return "$day $month $year";
}

/**
 * Format Currency to Indonesian Rupiah
 * 
 * @param int $amount Amount to format
 * @return string Formatted currency
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Generate Random String
 * 
 * @param int $length Length of string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Create Directory with Permission Check
 * 
 * @param string $dir Directory path
 * @return bool True if created or exists, false otherwise
 */
function createDirectorySafe($dir) {
    if (is_dir($dir)) {
        return true;
    }
    
    try {
        if (@mkdir($dir, 0777, true)) {
            @chmod($dir, 0777); // Try to set permissions
            return true;
        }
    } catch (Exception $e) {
        // Silent fail - directory creation not critical
    }
    
    return false;
}

/**
 * Create Upload Directories
 * Silently fails if no permission - not critical for app to run
 */
function createUploadDirectories() {
    $baseDir = __DIR__ . '/../uploads';
    
    $dirs = [
        $baseDir,
        $baseDir . '/buku',
        $baseDir . '/temp'
    ];
    
    foreach ($dirs as $dir) {
        createDirectorySafe($dir);
    }
}

// Initialize secure session
secureSessionStart();

// Try to create upload directories (non-critical)
createUploadDirectories();

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

?>
