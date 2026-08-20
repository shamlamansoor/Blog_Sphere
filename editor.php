<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$postId = $_GET['id'] ?? null;
$title = '';
$content = '';
$isEdit = false;

if ($postId) {
    // Edit mode
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT title, content FROM blogPost WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $postId, 'user_id' => getCurrentUserId()]);
    $post = $stmt->fetch();

    if (!$post) {
        die("Post not found or you don't have permission to edit it.");
    }
    $title = $post['title'];
    $content = $post['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit Post' : 'New Post'; ?> - Blog Sphere</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper" style="align-items: flex-start; padding-top: 50px;">
        <div class="auth-container" style="max-width: 600px;">
            <h2><?php echo $isEdit ? 'Edit Post' : 'Create New Post'; ?></h2>
            
            <form method="POST" action="api/<?php echo $isEdit ? 'update_post.php' : 'create_post.php'; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($postId); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required placeholder="Enter post title">
                </div>
                
                <div class="form-group">
                    <label for="content">Content (Markdown supported)</label>
                    <textarea id="content" name="content" rows="10" required placeholder="Write your post content here..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn"><?php echo $isEdit ? 'Update Post' : 'Publish Post'; ?></button>
                    <a href="index.php" class="btn" style="background-color: var(--text-muted); text-align: center; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
