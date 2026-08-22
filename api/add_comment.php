<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $postId = $_POST['post_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    $userId = getCurrentUserId();

    if (!$postId || empty($content)) {
        die("Post ID and content are required.");
    }

    try {
        // Check if post exists
        $checkPost = $pdo->prepare("SELECT id FROM blogPost WHERE id = :id");
        $checkPost->execute(['id' => $postId]);
        if (!$checkPost->fetch()) {
            die("Post not found.");
        }

        // Insert comment
        $stmt = $pdo->prepare("INSERT INTO postComment (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => sanitize($content) // Using sanitize function to prevent XSS
        ]);
        
        // Redirect back to post
        header("Location: ../post.php?id=" . $postId . "#comments");
        exit;
    } catch (PDOException $e) {
        die("Error adding comment: " . $e->getMessage());
    }
}
?>
