<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $commentId = $_POST['comment_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    $userId = getCurrentUserId();

    if (!$commentId || empty($content)) {
        die("Comment ID and content are required.");
    }

    try {
        // Fetch comment
        $stmt = $pdo->prepare("SELECT user_id, post_id FROM postComment WHERE id = :id");
        $stmt->execute(['id' => $commentId]);
        $comment = $stmt->fetch();

        if (!$comment) {
            die("Comment not found.");
        }

        // Verify ownership
        if ($comment['user_id'] != $userId) {
            die("You are not authorized to edit this comment.");
        }

        // Update comment
        $updateStmt = $pdo->prepare("UPDATE postComment SET content = :content WHERE id = :id");
        $updateStmt->execute([
            'content' => sanitize($content),
            'id' => $commentId
        ]);

        header("Location: ../post.php?id=" . $comment['post_id'] . "#comments");
        exit;
    } catch (PDOException $e) {
        die("Error updating comment: " . $e->getMessage());
    }
}
?>
