<?php
// unauthorized.php

// Start session to access session variables
session_start();

// Set response code to 403 Forbidden
http_response_code(403);

// Include database connection if needed (optional, depending on your setup)
$rootPath = '/se-final-project/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .unauthorized-container {
            margin-top: 100px;
            max-width: 600px;
        }
        .unauthorized-icon {
            font-size: 5rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container unauthorized-container text-center">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="unauthorized-icon mb-4">
                    <i class="fas fa-ban"></i>
                </div>
                <h1 class="h3 mb-3">Access Denied</h1>
                <p class="mb-4">You don't have permission to access this page.</p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <p>You are logged in as: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong></p>
                    <p>Your role: <strong><?= htmlspecialchars($_SESSION['role'] ?? 'Unknown') ?></strong></p>
                <?php else: ?>
                    <p>You are not logged in.</p>
                <?php endif; ?>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Return to Dashboard
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Return Home
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>