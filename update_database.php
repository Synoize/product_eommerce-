<?php
/**
 * Database Update Script
 * Adds reset_token and reset_expires columns for password reset functionality
 */

require_once __DIR__ . '/includes/db_connect.php';

echo "<h2>Database Update</h2>";

try {
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
    $tokenExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'reset_expires'");
    $expiresExists = $stmt->fetch();
    
    if ($tokenExists && $expiresExists) {
        echo "<p style='color: green;'>✓ Database columns already exist!</p>";
    } else {
        // Add columns
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME DEFAULT NULL");
        echo "<p style='color: green;'>✓ Database updated successfully!</p>";
    }
    
    echo "<p><a href='user/forgot-password.php' style='color: blue;'>Go to Forgot Password</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
