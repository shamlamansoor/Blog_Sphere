<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $postId = $_POST['id'] ?? null;

    if (!$postId) {
        die("Post ID is required.");
    }

    $userId = getCurrentUserId();

    try {
        // Server-side ownership check
        $checkStmt = $pdo->prepare("SELECT id FROM blogPost WHERE id = :id AND user_id = :user_id");
        $checkStmt->execute(['id' => $postId, 'user_id' => $userId]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(403);
            die("Unauthorized access or post does not exist.");
        }

        $stmt = $pdo->prepare("DELETE FROM blogPost WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'id' => $postId,
            'user_id' => $userId
        ]);
        
        header("Location: ../index.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting post: " . $e->getMessage());
    }
}
?>
