<?php
// includes/functions.php

/**
 * Sanitize input to prevent XSS.
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token.
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token.
 */
function verifyCsrfToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}
/**
 * Upload an image safely.
 * Returns the path to the uploaded image on success, or an array with 'error' key on failure.
 */
function uploadImage($file, $uploadDir = '../assets/images/posts/') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload error: ' . $file['error']];
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['error' => 'File size exceeds 5MB limit.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, and WEBP are allowed.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($extension), $allowedExtensions)) {
        return ['error' => 'Invalid file extension.'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueName = 'blog_' . uniqid() . '.' . $extension;
    $destination = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Return relative path for database storage (adjust based on caller path)
        return 'assets/images/posts/' . $uniqueName;
    }

    return ['error' => 'Failed to move uploaded file.'];
}
?>
