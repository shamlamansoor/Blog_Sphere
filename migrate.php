<?php
require_once __DIR__ . '/config/db.php';

echo "Starting database migration...\n";

try {
    // 1. Comment Likes Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS commentLike (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_comment_like (comment_id, user_id),
            FOREIGN KEY (comment_id) REFERENCES postComment(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    echo "Created commentLike table (if not existed).\n";

    // 2. Questions Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS postQuestion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            question TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES blogPost(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    echo "Created postQuestion table (if not existed).\n";

    // 3. Question Likes Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questionLike (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_question_like (question_id, user_id),
            FOREIGN KEY (question_id) REFERENCES postQuestion(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    echo "Created questionLike table (if not existed).\n";

    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
