<?php
/**
 * Database Connection
 * Includes session start and database connection
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';
