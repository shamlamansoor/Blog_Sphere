<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Fetch all posts
$stmt = $pdo->query("
    SELECT p.id, p.title, p.content, p.created_at, p.user_id, u.username 
    FROM blogPost p 
    JOIN user u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Sphere - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 24px; font-weight: 700; color: var(--primary); text-decoration: none; }
        .nav-links { display: flex; gap: 15px; align-items: center; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .post-card { background: white; padding: 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .post-title { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
        .post-title a { color: var(--text-main); text-decoration: none; }
        .post-title a:hover { color: var(--primary); }
        .post-meta { font-size: 14px; color: var(--text-muted); margin-bottom: 16px; }
        .post-excerpt { color: #4b5563; margin-bottom: 16px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .post-actions { display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color); }
        .btn-small { padding: 6px 12px; font-size: 14px; background: white; border: 1px solid var(--border-color); border-radius: 4px; cursor: pointer; text-decoration: none; color: var(--text-main); }
        .btn-small:hover { background: var(--bg-color); }
        .btn-delete { color: var(--error-text); border-color: #fecaca; }
        .btn-delete:hover { background: var(--error-bg); }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Blog Sphere</a>
        <div class="nav-links">
            <?php if (isLoggedIn()): ?>
                <span style="font-weight: 500;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="editor.php" class="btn" style="padding: 8px 16px; width: auto;">New Post</a>
                <a href="logout.php" class="btn" style="padding: 8px 16px; width: auto; background-color: var(--text-muted);">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn" style="padding: 8px 16px; width: auto; background-color: white; color: var(--text-main); border: 1px solid var(--border-color);">Login</a>
                <a href="register.php" class="btn" style="padding: 8px 16px; width: auto;">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <?php if (empty($posts)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <h2>No posts yet.</h2>
                <?php if (isLoggedIn()): ?>
                    <p>Be the first to <a href="editor.php" style="color: var(--primary);">write a post</a>!</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <h2 class="post-title"><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <div class="post-meta">
                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                    </div>
                    <div class="post-excerpt">
                        <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...
                    </div>
                    <a href="post.php?id=<?php echo $post['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 500;">Read more &rarr;</a>
                    
                    <?php if (isLoggedIn() && getCurrentUserId() === $post['user_id']): ?>
                        <div class="post-actions">
                            <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn-small">Edit</a>
                            <form method="POST" action="api/delete_post.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn-small btn-delete">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
