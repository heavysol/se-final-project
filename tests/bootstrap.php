<?php

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define test environment
define('TEST_ENV', true);

// Mock session functions if needed
if (!function_exists('session_start')) {
    function session_start() {
        return true;
    }
}

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php'; 