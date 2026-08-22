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
    $userId = getCurrentUserId();

    if (!$questionId) {
        die("Question ID is required.");
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, post_id FROM postQuestion WHERE id = :id");
        $stmt->execute(['id' => $questionId]);
        $question = $stmt->fetch();

        if (!$question) {
            die("Question not found.");
        }

        if ($question['user_id'] != $userId) {
            die("You are not authorized to delete this question.");
        }

        $deleteStmt = $pdo->prepare("DELETE FROM postQuestion WHERE id = :id");
        $deleteStmt->execute(['id' => $questionId]);

        header("Location: ../post.php?id=" . $question['post_id'] . "#questions");
        exit;
    } catch (PDOException $e) {
        die("Error deleting question: " . $e->getMessage());
    }
}
?>
