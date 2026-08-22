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
$questionId = $input['question_id'] ?? null;
$userId = getCurrentUserId();

if (!$questionId) {
    echo json_encode(['success' => false, 'error' => 'Question ID is required']);
    exit;
}

try {
    // Check if the question exists
    $checkQuestion = $pdo->prepare("SELECT id FROM postQuestion WHERE id = :id");
    $checkQuestion->execute(['id' => $questionId]);
    if (!$checkQuestion->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Question not found']);
        exit;
    }

    // Check if user already liked it
    $checkLike = $pdo->prepare("SELECT id FROM questionLike WHERE question_id = :question_id AND user_id = :user_id");
    $checkLike->execute(['question_id' => $questionId, 'user_id' => $userId]);
    $existingLike = $checkLike->fetch();

    $action = '';
    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM questionLike WHERE id = :id");
        $stmt->execute(['id' => $existingLike['id']]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO questionLike (question_id, user_id) VALUES (:question_id, :user_id)");
        $stmt->execute(['question_id' => $questionId, 'user_id' => $userId]);
        $action = 'liked';
    }

    // Get new count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM questionLike WHERE question_id = :question_id");
    $countStmt->execute(['question_id' => $questionId]);
    $newCount = $countStmt->fetchColumn();

    echo json_encode(['success' => true, 'action' => $action, 'like_count' => $newCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
