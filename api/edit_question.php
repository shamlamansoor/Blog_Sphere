<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $questionId = $_POST['question_id'] ?? null;
    $questionContent = trim($_POST['question'] ?? '');
    $userId = getCurrentUserId();

    if (!$questionId || empty($questionContent)) {
        die("Question ID and content are required.");
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, post_id FROM postQuestion WHERE id = :id");
        $stmt->execute(['id' => $questionId]);
        $question = $stmt->fetch();

        if (!$question) {
            die("Question not found.");
        }

        if ($question['user_id'] != $userId) {
            die("You are not authorized to edit this question.");
        }

        $updateStmt = $pdo->prepare("UPDATE postQuestion SET question = :question WHERE id = :id");
        $updateStmt->execute([
            'question' => sanitize($questionContent),
            'id' => $questionId
        ]);

        header("Location: ../post.php?id=" . $question['post_id'] . "#questions");
        exit;
    } catch (PDOException $e) {
        die("Error updating question: " . $e->getMessage());
    }
}
?>
