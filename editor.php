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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- DOMPurify for XSS sanitization -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.8/purify.min.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="navbar-brand">
                <img src="assets/images/blog-logo.png" alt="Logo" class="nav-logo">
                Blog Sphere
            </a>
            <div class="nav-links">
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </nav>

    <main class="container editor-container">
        <div class="editor-header">
            <h2><?php echo $isEdit ? 'Edit Post' : 'Create New Post'; ?></h2>
        </div>
        
        <form method="POST" action="api/<?php echo $isEdit ? 'update_post.php' : 'create_post.php'; ?>" class="editor-form-grid">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($postId); ?>">
            <?php endif; ?>

            <div class="editor-column">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required placeholder="Enter post title">
                </div>
                
                <div class="form-group flex-grow">
                    <label for="content">Markdown Content</label>
                    <textarea id="content" name="content" required placeholder="Write your post content here using Markdown..."><?php echo htmlspecialchars($content); ?></textarea>
                </div>
            </div>

            <div class="preview-column">
                <label>Live Preview</label>
                <div id="preview-pane" class="markdown-body">
                    <!-- Preview HTML will be injected here by JS -->
                </div>
            </div>
            
            <div class="editor-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Post' : 'Publish Post'; ?></button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
