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
    <style>
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 24px; font-weight: 700; color: var(--primary); text-decoration: none; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .post-full { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .post-title { font-size: 32px; font-weight: 700; margin-bottom: 12px; color: var(--text-main); }
        .post-meta { font-size: 14px; color: var(--text-muted); margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
        .post-content { font-size: 16px; line-height: 1.8; color: #374151; }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Blog Sphere</a>
        <div>
            <?php if (isLoggedIn()): ?>
                <a href="editor.php" class="btn" style="padding: 8px 16px; width: auto;">New Post</a>
            <?php else: ?>
                <a href="login.php" class="btn" style="padding: 8px 16px; width: auto; background-color: white; color: var(--text-main); border: 1px solid var(--border-color);">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="back-link">&larr; Back to all posts</a>
        <article class="post-full">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                <?php if ($post['created_at'] !== $post['updated_at']): ?>
                    (Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>)
                <?php endif; ?>
            </div>
            <div class="post-content">
                <?php 
                // Simple markdown-like rendering for newlines
                echo nl2br(htmlspecialchars($post['content'])); 
                ?>
            </div>
        </article>
    </div>
</body>
</html>
