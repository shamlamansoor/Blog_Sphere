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
$commentId = $input['comment_id'] ?? null;
$userId = getCurrentUserId();

if (!$commentId) {
    echo json_encode(['success' => false, 'error' => 'Comment ID is required']);
    exit;
}

try {
    // Check if the comment exists
    $checkComment = $pdo->prepare("SELECT id FROM postComment WHERE id = :id");
    $checkComment->execute(['id' => $commentId]);
    if (!$checkComment->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Comment not found']);
        exit;
    }

    // Check if user already liked it
    $checkLike = $pdo->prepare("SELECT id FROM commentLike WHERE comment_id = :comment_id AND user_id = :user_id");
    $checkLike->execute(['comment_id' => $commentId, 'user_id' => $userId]);
    $existingLike = $checkLike->fetch();

    $action = '';
    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM commentLike WHERE id = :id");
        $stmt->execute(['id' => $existingLike['id']]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO commentLike (comment_id, user_id) VALUES (:comment_id, :user_id)");
        $stmt->execute(['comment_id' => $commentId, 'user_id' => $userId]);
        $action = 'liked';
    }

    // Get new count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM commentLike WHERE comment_id = :comment_id");
    $countStmt->execute(['comment_id' => $commentId]);
    $newCount = $countStmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'like_count' => $newCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
