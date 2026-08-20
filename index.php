<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Sphere - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div style="padding: 20px;">
        <h1>Welcome to Blog Sphere</h1>
        <?php if (isLoggedIn()): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="logout.php" class="btn" style="display:inline-block; width:auto;">Logout</a>
        <?php else: ?>
            <p>You are not logged in.</p>
            <a href="login.php" class="btn" style="display:inline-block; width:auto;">Login</a>
            <a href="register.php" class="btn" style="display:inline-block; width:auto; background-color:#6b7280;">Register</a>
        <?php endif; ?>
        
        <p style="margin-top: 20px;">(This is a placeholder for Part 4 - Home Page)</p>
    </div>
</body>
</html>
