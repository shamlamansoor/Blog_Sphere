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
    $question = trim($_POST['question'] ?? '');
    $userId = getCurrentUserId();

    if (!$postId || empty($question)) {
        die("Post ID and question are required.");
    }

    try {
        // Check if post exists
        $checkPost = $pdo->prepare("SELECT id FROM blogPost WHERE id = :id");
        $checkPost->execute(['id' => $postId]);
        if (!$checkPost->fetch()) {
            die("Post not found.");
        }

        // Insert question
        $stmt = $pdo->prepare("INSERT INTO postQuestion (post_id, user_id, question) VALUES (:post_id, :user_id, :question)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'question' => sanitize($question)
        ]);
        
        // Redirect back to post
        header("Location: ../post.php?id=" . $postId . "#questions");
        exit;
    } catch (PDOException $e) {
        die("Error adding question: " . $e->getMessage());
    }
}
?>
