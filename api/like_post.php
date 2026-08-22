<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !verifyCsrfToken($input['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
    exit;
}
$postId = $input['post_id'] ?? null;
$userId = getCurrentUserId();

if (!$postId) {
    echo json_encode(['success' => false, 'error' => 'Post ID is required']);
    exit;
}

try {
    // Check if the post exists
    $checkPost = $pdo->prepare("SELECT id FROM blogPost WHERE id = :id");
    $checkPost->execute(['id' => $postId]);
    if (!$checkPost->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }

    // Check if user already liked it
    $checkLike = $pdo->prepare("SELECT id FROM postLike WHERE post_id = :post_id AND user_id = :user_id");
    $checkLike->execute(['post_id' => $postId, 'user_id' => $userId]);
    $existingLike = $checkLike->fetch();

    $action = '';
    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM postLike WHERE id = :id");
        $stmt->execute(['id' => $existingLike['id']]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO postLike (post_id, user_id) VALUES (:post_id, :user_id)");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
        $action = 'liked';
    }

    // Get new count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM postLike WHERE post_id = :post_id");
    $countStmt->execute(['post_id' => $postId]);
    $newCount = $countStmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'like_count' => $newCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
