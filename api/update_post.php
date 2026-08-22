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
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$postId || empty($title) || empty($content)) {
        die("ID, Title, and content are required.");
    }

    $userId = getCurrentUserId();

    try {
        // Server-side ownership check
        $checkStmt = $pdo->prepare("SELECT id, image FROM blogPost WHERE id = :id AND user_id = :user_id");
        $checkStmt->execute(['id' => $postId, 'user_id' => $userId]);
        $existingPost = $checkStmt->fetch();
        
        if (!$existingPost) {
            http_response_code(403);
            die("Unauthorized access or post does not exist.");
        }

        $imagePath = $existingPost['image'];
        $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';

        // Check if removing image
        if ($removeImage && $imagePath) {
            if (file_exists('../' . $imagePath)) {
                unlink('../' . $imagePath);
            }
            $imagePath = null;
        }

        // Check if new image uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadImage($_FILES['image']);
            if (isset($uploadResult['error'])) {
                die($uploadResult['error']);
            }
            // Delete old image if exists
            if ($imagePath && file_exists('../' . $imagePath)) {
                unlink('../' . $imagePath);
            }
            $imagePath = $uploadResult;
        }

        $stmt = $pdo->prepare("UPDATE blogPost SET title = :title, content = :content, image = :image WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'image' => $imagePath,
            'id' => $postId,
            'user_id' => $userId
        ]);
        
        header("Location: ../post.php?id=" . $postId);
        exit;
    } catch (PDOException $e) {
        die("Error updating post: " . $e->getMessage());
    }
}
?>
