<?php
/**
 * Security and Database Helper Functions
 * Provides reusable functions for input validation, sanitization, and secure database operations
 */

// ============ PASSWORD SECURITY ============
/**
 * Hash a password using bcrypt algorithm
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a plain text password against a hashed password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============ INPUT VALIDATION & SANITIZATION ============
/**
 * Sanitize input to prevent XSS attacks
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Sanitize output for display
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate username format (alphanumeric, underscore, dash, 3-20 chars)
 */
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username);
}

/**
 * Validate password strength (minimum 6 characters)
 */
function validatePassword($password) {
    return strlen($password) >= 6;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============ FILE UPLOAD SECURITY ============
/**
 * Validate uploaded file
 * @param array $file - $_FILES array element
 * @param int $maxSize - maximum file size in bytes (default 5MB)
 * @param array $allowedExtensions - whitelist of allowed extensions
 * @return array - ['valid' => bool, 'error' => string or null]
 */
function validateFileUpload($file, $maxSize = 5242880, $allowedExtensions = ['pdf', 'doc', 'docx', 'txt']) {
    $result = ['valid' => false, 'error' => null];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] != 0) {
        $result['error'] = 'No file uploaded or upload error occurred.';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $result['error'] = 'File size exceeds maximum allowed size (' . ($maxSize / 1048576) . 'MB).';
        return $result;
    }
    
    // Check file extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedExtensions)) {
        $result['error'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions);
        return $result;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];
    
    if (!in_array($mimeType, $allowedMimes)) {
        $result['error'] = 'Invalid file MIME type.';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Generate a unique safe filename
 */
function generateSafeFilename($originalName) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    return $name . '_' . time() . '.' . $ext;
}

// ============ DATABASE HELPERS ============
/**
 * Execute a prepared statement safely
 * @param mysqli $conn - database connection
 * @param string $query - SQL query with placeholders
 * @param array $params - parameters to bind
 * @param string $types - parameter types (e.g., "ss" for two strings)
 * @return mysqli_result|bool - result or false on error
 */
function executeQuery($conn, $query, $params = [], $types = '') {
    if (empty($params)) {
        return $conn->query($query);
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error);
        return false;
    }
    
    return $stmt->get_result();
}

/**
 * Get a single row from database
 */
function getRow($conn, $query, $params = [], $types = '') {
    $result = executeQuery($conn, $query, $params, $types);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get multiple rows from database
 */
function getRows($conn, $query, $params = [], $types = '') {
    $result = executeQuery($conn, $query, $params, $types);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Execute insert/update/delete query
 * @return bool - true if successful, false otherwise
 */
function executeUpdate($conn, $query, $params = [], $types = '') {
    $result = executeQuery($conn, $query, $params, $types);
    return $result !== false;
}

// ============ CSRF PROTECTION ============
/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============ PAGINATION ============
/**
 * Calculate pagination offset and limit
 * @param int $page - current page number (1-indexed)
 * @param int $itemsPerPage - number of items per page
 * @return array - ['offset' => int, 'limit' => int]
 */
function getPaginationParams($page = 1, $itemsPerPage = 10) {
    $page = max(1, intval($page));
    $offset = ($page - 1) * $itemsPerPage;
    return ['offset' => $offset, 'limit' => $itemsPerPage];
}

/**
 * Calculate total pages
 */
function getTotalPages($totalItems, $itemsPerPage = 10) {
    return ceil($totalItems / $itemsPerPage);
}

// ============ MESSAGE FUNCTIONS ============
/**
 * Display success message
 */
function showSuccess($message) {
    return "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . sanitizeOutput($message) . 
           "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

/**
 * Display error message
 */
function showError($message) {
    return "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . sanitizeOutput($message) . 
           "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

/**
 * Display info message
 */
function showInfo($message) {
    return "<div class='alert alert-info alert-dismissible fade show' role='alert'>" . sanitizeOutput($message) . 
           "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

// ============ AUTHENTICATION ============
/**
 * Check if user is logged in as student
 */
function isStudentLoggedIn() {
    return isset($_SESSION['student']) && !empty($_SESSION['student']);
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin($type = 'student') {
    if ($type === 'admin' && !isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    } elseif ($type === 'student' && !isStudentLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

?>
