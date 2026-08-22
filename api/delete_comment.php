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
    $userId = getCurrentUserId();

    if (!$commentId) {
        die("Comment ID is required.");
    }

    try {
        // Fetch the comment to verify ownership
        $stmt = $pdo->prepare("SELECT user_id, post_id FROM postComment WHERE id = :id");
        $stmt->execute(['id' => $commentId]);
        $comment = $stmt->fetch();

        if (!$comment) {
            die("Comment not found.");
        }

        // Verify ownership (or admin if implemented later)
        if ($comment['user_id'] != $userId) {
            die("You are not authorized to delete this comment.");
        }

        // Delete the comment
        $deleteStmt = $pdo->prepare("DELETE FROM postComment WHERE id = :id");
        $deleteStmt->execute(['id' => $commentId]);

        // Redirect back to the post
        header("Location: ../post.php?id=" . $comment['post_id'] . "#comments");
        exit;
    } catch (PDOException $e) {
        die("Error deleting comment: " . $e->getMessage());
    }
}
?>
