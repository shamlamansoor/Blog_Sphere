<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Fetch all posts
$stmt = $pdo->query("
    SELECT p.id, p.title, p.content, p.created_at, p.user_id, p.image, u.username 
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
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="navbar-brand">
                <img src="assets/images/blog-logo.png" alt="Blog Sphere Logo" class="nav-logo">
                Blog Sphere
            </a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span class="nav-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <div class="hero-welcome">Welcome to Blog Sphere</div>
            <h1>Write. Share. Inspire.</h1>
            <p>Discover stories, share your ideas, and connect through words.</p>
            <?php if (isLoggedIn()): ?>
                <a href="editor.php" class="btn btn-primary">+ Create New Post</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h2>No posts yet.</h2>
                <p>Be the first to share your story.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="editor.php" class="btn btn-primary">Create Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Featured Image" class="post-card-image">
                        <?php endif; ?>
                        <h2 class="post-title"><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                        <div class="post-meta">
                            By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                        </div>
                        <div class="post-excerpt">
                            <?php echo htmlspecialchars(substr($post['content'], 0, 150)); ?><?php echo strlen($post['content']) > 150 ? '...' : ''; ?>
                        </div>
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">Read more &rarr;</a>
                        
                        <?php if (isLoggedIn() && getCurrentUserId() === $post['user_id']): ?>
                            <div class="post-actions">
                                <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn-small btn-edit">Edit</a>
                                <form method="POST" action="api/delete_post.php" class="form-delete" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn-small btn-delete">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
