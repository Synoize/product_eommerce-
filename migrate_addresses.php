<?php
/**
 * Database Migration - Add Addresses Table
 * Run this to add the addresses table to existing database
 */

require_once __DIR__ . '/includes/db_connect.php';

echo "<h2>Database Migration - Addresses Feature</h2>";

try {
    // Check if addresses table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'addresses'");
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✓ Addresses table already exists!</p>";
    } else {
        // Create addresses table
        $sql = "CREATE TABLE IF NOT EXISTS `addresses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `name` varchar(120) NOT NULL,
            `mobile` varchar(20) NOT NULL,
            `address` text NOT NULL,
            `city` varchar(100) NOT NULL,
            `state` varchar(100) NOT NULL,
            `pincode` varchar(10) NOT NULL,
            `is_default` tinyint(1) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Addresses table created successfully!</p>";
        
        // Migrate existing user addresses to addresses table
        $stmt = $pdo->query("SELECT id, name, mobile, address, city, state, pincode FROM users WHERE address IS NOT NULL AND address != ''");
        $users = $stmt->fetchAll();
        
        $migrated = 0;
        foreach ($users as $user) {
            $insert = $pdo->prepare("INSERT INTO addresses (user_id, name, mobile, address, city, state, pincode, is_default) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $insert->execute([
                $user['id'],
                $user['name'],
                $user['mobile'] ?? '9876543210',
                $user['address'],
                $user['city'] ?? 'Delhi',
                $user['state'] ?? 'Delhi',
                $user['pincode'] ?? '110001'
            ]);
            $migrated++;
        }
        
        if ($migrated > 0) {
            echo "<p style='color: green;'>✓ Migrated $migrated user addresses to new table!</p>";
        }
    }
    
    echo "<p><a href='user/addresses.php' style='color: blue;'>Go to Manage Addresses</a></p>";
    echo "<p><a href='checkout.php' style='color: blue;'>Go to Checkout</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
