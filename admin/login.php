<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_POST && isset($_POST['login'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        // Simple admin authentication (in production, use proper user table with hashed passwords)
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Invalid username or password.';
        }
    } else {
        $error_message = 'Invalid request. Please try again.';
    }
}

$page_title = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Student Course Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <button type="submit" name="login" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">Demo credentials: admin / admin123</small>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Back to Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>