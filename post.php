<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$postId = $_GET['id'] ?? null;

if (!$postId) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM blogPost p 
    JOIN user u ON p.user_id = u.id 
    WHERE p.id = :id
");
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch();

if (!$post) {
    die("Post not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog Sphere</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="navbar-brand">Blog Sphere</a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="editor.php" class="btn btn-primary">New Post</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container">
        <a href="index.php" class="back-link">&larr; Back to all posts</a>
        <article class="post-full">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                <?php if ($post['created_at'] !== $post['updated_at']): ?>
                    (Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>)
                <?php endif; ?>
            </div>
            
            <div id="post-content-raw" style="display: none;">
                <?php echo htmlspecialchars($post['content']); ?>
            </div>
        </article>
    </main>
    <script src="assets/js/main.js"></script>
</body>
</html>
