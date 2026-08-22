<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$postId = $_GET['id'] ?? null;

if (!$postId) {
    header("Location: index.php");
    exit;
}

$currentUserId = getCurrentUserId();
$isLoggedIn = isLoggedIn();

// Fetch post
$stmt = $pdo->prepare("
    SELECT p.*, u.username,
           (SELECT COUNT(*) FROM postLike WHERE post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM postLike WHERE post_id = p.id AND user_id = :user_id) as user_liked
    FROM blogPost p 
    JOIN user u ON p.user_id = u.id 
    WHERE p.id = :id
");
$stmt->execute(['id' => $postId, 'user_id' => $currentUserId ?: 0]);
$post = $stmt->fetch();

if (!$post) {
    die("Post not found.");
}

// Fetch comments
$stmtComments = $pdo->prepare("
    SELECT c.*, u.username,
           (SELECT COUNT(*) FROM commentLike WHERE comment_id = c.id) as like_count,
           (SELECT COUNT(*) FROM commentLike WHERE comment_id = c.id AND user_id = :user_id) as user_liked
    FROM postComment c
    JOIN user u ON c.user_id = u.id
    WHERE c.post_id = :post_id
    ORDER BY c.created_at ASC
");
$stmtComments->execute(['post_id' => $postId, 'user_id' => $currentUserId ?: 0]);
$comments = $stmtComments->fetchAll();

// Fetch questions
$stmtQuestions = $pdo->prepare("
    SELECT q.*, u.username,
           (SELECT COUNT(*) FROM questionLike WHERE question_id = q.id) as like_count,
           (SELECT COUNT(*) FROM questionLike WHERE question_id = q.id AND user_id = :user_id) as user_liked
    FROM postQuestion q
    JOIN user u ON q.user_id = u.id
    WHERE q.post_id = :post_id
    ORDER BY q.created_at ASC
");
$stmtQuestions->execute(['post_id' => $postId, 'user_id' => $currentUserId ?: 0]);
$questions = $stmtQuestions->fetchAll();
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.8/purify.min.js"></script>
    <meta name="csrf-token" content="<?php echo generateCsrfToken(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="navbar-brand">
                <img src="assets/images/blog-logo.png" alt="Logo" class="nav-logo">
                Blog Sphere
            </a>
            <div class="nav-links">
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="btn btn-secondary">My Profile</a>
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
            <h1 class="post-title" style="font-size: 36px; margin-bottom: 12px; color: var(--primary);"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta" style="font-size: 16px; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
                By <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
            </div>
            
            <?php if (!empty($post['image'])): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Featured Image" class="post-image">
            <?php endif; ?>
            
            <div id="post-content-raw" style="display: none;">
                <?php echo htmlspecialchars($post['content']); ?>
            </div>
            
            <div style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button id="btn-like-post" data-id="<?php echo $post['id']; ?>" class="btn-like <?php echo ($isLoggedIn && $post['user_liked']) ? 'liked' : ''; ?>" style="font-size: 18px; padding: 8px 16px; background: #f9fafb; border: 1px solid var(--border); border-radius: 8px;">
                    <?php if ($isLoggedIn && $post['user_liked']): ?>
                        ❤️ Liked <span class="like-count" style="margin-left: 4px;"><?php echo $post['like_count']; ?></span>
                    <?php else: ?>
                        ♡ Like <span class="like-count" style="margin-left: 4px;"><?php echo $post['like_count']; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <?php if ($isLoggedIn && $currentUserId == $post['user_id']): ?>
                <div class="post-actions" style="margin-top: 20px;">
                    <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn-small btn-edit">Edit Post</a>
                    <form method="POST" action="api/delete_post.php" class="form-delete" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                        <button type="submit" class="btn-small btn-delete">Delete Post</button>
                    </form>
                </div>
            <?php endif; ?>
            
        </article>

        <!-- COMMENTS SECTION -->
        <section id="comments" class="interaction-section">
            <h2>Comments (<?php echo count($comments); ?>)</h2>
            
            <?php if ($isLoggedIn): ?>
                <div style="margin-bottom: 30px; background: #fff; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border);">
                    <form method="POST" action="api/add_comment.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="form-group">
                            <textarea name="content" rows="3" placeholder="Write a comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="interaction-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="interaction-item" id="comment-<?php echo $comment['id']; ?>">
                        <div class="interaction-header">
                            <span class="interaction-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <span class="interaction-date"><?php echo date('M j, Y', strtotime($comment['created_at'])); ?></span>
                        </div>
                        
                        <div id="display-comment-<?php echo $comment['id']; ?>" class="interaction-body">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                        
                        <!-- Edit Form (Hidden by default) -->
                        <?php if ($isLoggedIn && $currentUserId == $comment['user_id']): ?>
                            <div id="form-comment-<?php echo $comment['id']; ?>" class="form-inline">
                                <form method="POST" action="api/edit_comment.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <div class="form-group">
                                        <textarea name="content" rows="2" required><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn-small btn-primary">Save</button>
                                    <button type="button" class="btn-small btn-secondary btn-toggle-edit" data-target="form-comment-<?php echo $comment['id']; ?>">Cancel</button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="interaction-actions">
                            <?php if ($isLoggedIn): ?>
                                <button data-id="<?php echo $comment['id']; ?>" class="btn-like btn-like-comment <?php echo $comment['user_liked'] ? 'liked' : ''; ?>">
                                    <?php echo $comment['user_liked'] ? '❤️' : '♡'; ?> <span class="like-count"><?php echo $comment['like_count']; ?></span>
                                </button>
                            <?php else: ?>
                                <button data-id="<?php echo $comment['id']; ?>" class="btn-like btn-like-comment">
                                    ♡ <span class="like-count"><?php echo $comment['like_count']; ?></span>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($isLoggedIn && $currentUserId == $comment['user_id']): ?>
                                <button type="button" class="btn-small btn-edit btn-toggle-edit" data-target="form-comment-<?php echo $comment['id']; ?>">Edit</button>
                                <form method="POST" action="api/delete_comment.php" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn-small btn-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- QUESTIONS SECTION -->
        <section id="questions" class="interaction-section">
            <h2>Questions (<?php echo count($questions); ?>)</h2>
            
            <?php if ($isLoggedIn): ?>
                <div style="margin-bottom: 30px; background: #fff; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border);">
                    <form method="POST" action="api/add_question.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="form-group">
                            <textarea name="question" rows="2" placeholder="Ask a question about this post..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Ask Question</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="interaction-list">
                <?php foreach ($questions as $question): ?>
                    <div class="interaction-item" id="question-<?php echo $question['id']; ?>">
                        <div class="interaction-header">
                            <span class="interaction-author">Asked by <?php echo htmlspecialchars($question['username']); ?></span>
                            <span class="interaction-date"><?php echo date('M j, Y', strtotime($question['created_at'])); ?></span>
                        </div>
                        
                        <div id="display-question-<?php echo $question['id']; ?>" class="interaction-body" style="font-weight: 500;">
                            ❓ <?php echo nl2br(htmlspecialchars($question['question'])); ?>
                        </div>
                        
                        <!-- Edit Form -->
                        <?php if ($isLoggedIn && $currentUserId == $question['user_id']): ?>
                            <div id="form-question-<?php echo $question['id']; ?>" class="form-inline">
                                <form method="POST" action="api/edit_question.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    <div class="form-group">
                                        <textarea name="question" rows="2" required><?php echo htmlspecialchars($question['question']); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn-small btn-primary">Save</button>
                                    <button type="button" class="btn-small btn-secondary btn-toggle-edit" data-target="form-question-<?php echo $question['id']; ?>">Cancel</button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="interaction-actions">
                            <?php if ($isLoggedIn): ?>
                                <button data-id="<?php echo $question['id']; ?>" class="btn-like btn-like-question <?php echo $question['user_liked'] ? 'liked' : ''; ?>">
                                    <?php echo $question['user_liked'] ? '❤️' : '♡'; ?> <span class="like-count"><?php echo $question['like_count']; ?></span>
                                </button>
                            <?php else: ?>
                                <button data-id="<?php echo $question['id']; ?>" class="btn-like btn-like-question">
                                    ♡ <span class="like-count"><?php echo $question['like_count']; ?></span>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($isLoggedIn && $currentUserId == $question['user_id']): ?>
                                <button type="button" class="btn-small btn-edit btn-toggle-edit" data-target="form-question-<?php echo $question['id']; ?>">Edit</button>
                                <form method="POST" action="api/delete_question.php" style="display:inline;" onsubmit="return confirm('Delete this question?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    <button type="submit" class="btn-small btn-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
    <script src="assets/js/main.js"></script>
</body>
</html>
