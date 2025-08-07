<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Student Course Hub</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Discover comprehensive degree programmes in computing, AI, cybersecurity, and data science at Student Course Hub.'; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Skip to main content link for keyboard users -->
    <a href="#main-content" class="skip-link visually-hidden-focusable">Skip to main content</a>
    
    <!-- Skip to navigation link -->
    <a href="#main-navigation" class="skip-link visually-hidden-focusable">Skip to navigation</a>
    
    <header role="banner">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary" role="navigation" aria-label="Main navigation" id="main-navigation">
            <div class="container">
                <a class="navbar-brand" href="index.php" aria-label="Student Course Hub - Home">
                    <i class="fas fa-graduation-cap me-2" aria-hidden="true"></i>Student Course Hub
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto" role="menubar">
                        <li class="nav-item" role="none">
                            <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? ' active' : ''; ?>" 
                               href="index.php" role="menuitem"
                               <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'aria-current="page"' : ''; ?>>
                                Home
                            </a>
                        </li>
                        <li class="nav-item" role="none">
                            <a class="nav-link<?php echo (basename($_SERVER['PHP_SELF']) == 'programmes.php') ? ' active' : ''; ?>" 
                               href="programmes.php" role="menuitem"
                               <?php echo (basename($_SERVER['PHP_SELF']) == 'programmes.php') ? 'aria-current="page"' : ''; ?>>
                                Programmes
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav" role="menubar">
                        <li class="nav-item" role="none">
                            <a class="nav-link" href="admin/login.php" role="menuitem">
                                <span class="visually-hidden">Access </span>Admin Login
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main role="main" id="main-content" tabindex="-1">