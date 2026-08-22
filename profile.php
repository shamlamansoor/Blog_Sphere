<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$userId = getCurrentUserId();
$username = $_SESSION['username'];
$email = ''; // Fetch from db if available
$joinedDate = '';

// Fetch user info
$stmtUser = $pdo->prepare("SELECT email, created_at FROM user WHERE id = :id");
$stmtUser->execute(['id' => $userId]);
if ($userRow = $stmtUser->fetch()) {
    $email = $userRow['email'];
    $joinedDate = date('M j, Y', strtotime($userRow['created_at']));
}

// 1. Fetch Stats
$stats = [];
$stats['posts'] = $pdo->query("SELECT COUNT(*) FROM blogPost WHERE user_id = $userId")->fetchColumn();
$stats['comments'] = $pdo->query("SELECT COUNT(*) FROM postComment WHERE user_id = $userId")->fetchColumn();
$stats['questions'] = $pdo->query("SELECT COUNT(*) FROM postQuestion WHERE user_id = $userId")->fetchColumn();
$stats['liked_posts'] = $pdo->query("SELECT COUNT(*) FROM postLike WHERE user_id = $userId")->fetchColumn();
$stats['liked_comments'] = $pdo->query("SELECT COUNT(*) FROM commentLike WHERE user_id = $userId")->fetchColumn();
$stats['liked_questions'] = $pdo->query("SELECT COUNT(*) FROM questionLike WHERE user_id = $userId")->fetchColumn();

// 2. Fetch My Blogs
$stmtBlogs = $pdo->prepare("
    SELECT p.id, p.title, p.content, p.created_at, p.image,
           (SELECT COUNT(*) FROM postLike WHERE post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM postComment WHERE post_id = p.id) as comment_count,
           (SELECT COUNT(*) FROM postQuestion WHERE post_id = p.id) as question_count
    FROM blogPost p 
    WHERE p.user_id = :user_id 
    ORDER BY p.created_at DESC
");
$stmtBlogs->execute(['user_id' => $userId]);
$myBlogs = $stmtBlogs->fetchAll();

// 3. Fetch Liked Posts
$stmtLikedPosts = $pdo->prepare("
    SELECT p.id, p.title, p.content, p.created_at, p.image, u.username as author_name
    FROM blogPost p
    JOIN postLike pl ON p.id = pl.post_id
    JOIN user u ON p.user_id = u.id
    WHERE pl.user_id = :user_id
    ORDER BY pl.created_at DESC
");
$stmtLikedPosts->execute(['user_id' => $userId]);
$likedPosts = $stmtLikedPosts->fetchAll();

// 4. Fetch My Comments
$stmtMyComments = $pdo->prepare("
    SELECT c.id, c.content, c.created_at, c.post_id, p.title as post_title,
           (SELECT COUNT(*) FROM commentLike WHERE comment_id = c.id) as like_count
    FROM postComment c
    JOIN blogPost p ON c.post_id = p.id
    WHERE c.user_id = :user_id
    ORDER BY c.created_at DESC
");
$stmtMyComments->execute(['user_id' => $userId]);
$myComments = $stmtMyComments->fetchAll();

// 5. Fetch My Questions
$stmtMyQuestions = $pdo->prepare("
    SELECT q.id, q.question, q.created_at, q.post_id, p.title as post_title,
           (SELECT COUNT(*) FROM questionLike WHERE question_id = q.id) as like_count
    FROM postQuestion q
    JOIN blogPost p ON q.post_id = p.id
    WHERE q.user_id = :user_id
    ORDER BY q.created_at DESC
");
$stmtMyQuestions->execute(['user_id' => $userId]);
$myQuestions = $stmtMyQuestions->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Blog Sphere</title>
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
                <span class="nav-welcome">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="profile.php" class="btn btn-primary">My Profile</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <header class="profile-hero" style="background: var(--card-bg); padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border); margin-bottom: 40px;">
        <div class="container">
            <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin: 0 auto 16px;">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <h1 style="color: var(--primary); margin-bottom: 8px;"><?php echo htmlspecialchars($username); ?></h1>
            <p style="color: var(--muted); margin-bottom: 8px;"><?php echo htmlspecialchars($email); ?></p>
            <p style="color: var(--text); margin-bottom: 16px;">Joined: <?php echo $joinedDate; ?></p>
            
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value"><?php echo $stats['posts']; ?></div><div class="stat-label">Posts</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['comments']; ?></div><div class="stat-label">Comments</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['questions']; ?></div><div class="stat-label">Questions</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['liked_posts']; ?></div><div class="stat-label">Liked Posts</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['liked_comments']; ?></div><div class="stat-label">Liked Comments</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['liked_questions']; ?></div><div class="stat-label">Liked Questions</div></div>
            </div>
        </div>
    </header>

    <main class="container">
        
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="tab-blogs">My Blogs</button>
                <button class="tab-btn" data-tab="tab-liked-posts">Liked Posts</button>
                <button class="tab-btn" data-tab="tab-comments">My Comments</button>
                <button class="tab-btn" data-tab="tab-questions">My Questions</button>
            </div>

            <!-- TAB: MY BLOGS -->
            <div class="tab-content active" id="tab-blogs">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2>Your Posts</h2>
                    <a href="editor.php" class="btn btn-primary">+ Create New Post</a>
                </div>
                
                <?php if (empty($myBlogs)): ?>
                    <div class="empty-state">
                        <h2>No posts yet.</h2>
                        <p>You haven't written any blog posts yet. Share your first story!</p>
                        <a href="editor.php" class="btn btn-primary">Create Post</a>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($myBlogs as $post): ?>
                            <article class="post-card">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Featured Image" class="post-card-image">
                                <?php endif; ?>
                                <h2 class="post-title"><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                                <div class="post-meta" style="margin-bottom: 10px;">
                                    Published on <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </div>
                                <div class="post-stats" style="font-size: 13px; color: var(--muted); margin-bottom: 16px; display: flex; gap: 12px;">
                                    <span>❤️ <?php echo $post['like_count']; ?></span>
                                    <span>💬 <?php echo $post['comment_count']; ?></span>
                                    <span>❓ <?php echo $post['question_count']; ?></span>
                                </div>
                                
                                <div class="post-actions" style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border); display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn-small btn-secondary" style="flex: 1; text-align: center;">View</a>
                                    <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn-small btn-edit" style="flex: 1; text-align: center;">Edit</a>
                                    <form method="POST" action="api/delete_post.php" class="form-delete" style="flex: 1; display: flex;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn-small btn-delete" style="width: 100%;">Delete</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: LIKED POSTS -->
            <div class="tab-content" id="tab-liked-posts">
                <h2>Liked Posts</h2>
                <?php if (empty($likedPosts)): ?>
                    <div class="empty-state">
                        <p>You haven't liked any posts yet.</p>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($likedPosts as $post): ?>
                            <article class="post-card">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Featured Image" class="post-card-image">
                                <?php endif; ?>
                                <h2 class="post-title"><a href="post.php?id=<?php echo $post['id']; ?>">❤️ <?php echo htmlspecialchars($post['title']); ?></a></h2>
                                <div class="post-meta" style="margin-bottom: 10px;">
                                    By <?php echo htmlspecialchars($post['author_name']); ?>
                                </div>
                                <div class="post-actions" style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border);">
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn-small btn-secondary">View Post</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: MY COMMENTS -->
            <div class="tab-content" id="tab-comments">
                <h2>My Comments</h2>
                <?php if (empty($myComments)): ?>
                    <div class="empty-state">
                        <p>You haven't made any comments yet.</p>
                    </div>
                <?php else: ?>
                    <div class="interaction-list">
                        <?php foreach ($myComments as $comment): ?>
                            <div class="interaction-item">
                                <div class="interaction-body">
                                    "<?php echo nl2br(htmlspecialchars($comment['content'])); ?>"
                                </div>
                                <div class="interaction-header" style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 10px;">
                                    <span class="interaction-date">On: <a href="post.php?id=<?php echo $comment['post_id']; ?>"><?php echo htmlspecialchars($comment['post_title']); ?></a></span>
                                    <span>❤️ <?php echo $comment['like_count']; ?></span>
                                </div>
                                <div class="interaction-actions" style="margin-top: 10px;">
                                    <a href="post.php?id=<?php echo $comment['post_id']; ?>#comments" class="btn-small btn-secondary">View Blog</a>
                                    <!-- Delete is just going to post.php for full edit/delete functionality to avoid duplicating forms -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: MY QUESTIONS -->
            <div class="tab-content" id="tab-questions">
                <h2>My Questions</h2>
                <?php if (empty($myQuestions)): ?>
                    <div class="empty-state">
                        <p>You haven't asked any questions yet.</p>
                    </div>
                <?php else: ?>
                    <div class="interaction-list">
                        <?php foreach ($myQuestions as $question): ?>
                            <div class="interaction-item">
                                <div class="interaction-body">
                                    ❓ <?php echo nl2br(htmlspecialchars($question['question'])); ?>
                                </div>
                                <div class="interaction-header" style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 10px;">
                                    <span class="interaction-date">On: <a href="post.php?id=<?php echo $question['post_id']; ?>"><?php echo htmlspecialchars($question['post_title']); ?></a></span>
                                    <span>❤️ <?php echo $question['like_count']; ?></span>
                                </div>
                                <div class="interaction-actions" style="margin-top: 10px;">
                                    <a href="post.php?id=<?php echo $question['post_id']; ?>#questions" class="btn-small btn-secondary">View Blog</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
