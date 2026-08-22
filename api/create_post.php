<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin(); // Ensure user is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        die("Title and content are required.");
    }

    $userId = getCurrentUserId();
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = uploadImage($_FILES['image']);
        if (isset($uploadResult['error'])) {
            die($uploadResult['error']);
        }
        $imagePath = $uploadResult;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO blogPost (user_id, title, content, image) VALUES (:user_id, :title, :content, :image)");
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'image' => $imagePath
        ]);
        
        // Redirect to home page on success
        header("Location: ../index.php");
        exit;
    } catch (PDOException $e) {
        die("Error creating post: " . $e->getMessage());
    }
}
?>
