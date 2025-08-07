<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'programmes.php' ? 'active' : ''; ?>" 
                   href="programmes.php">
                    <i class="fas fa-graduation-cap me-2"></i>Programmes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'modules.php' ? 'active' : ''; ?>" 
                   href="modules.php">
                    <i class="fas fa-book me-2"></i>Modules
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>" 
                   href="staff.php">
                    <i class="fas fa-users me-2"></i>Staff
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'interested_students.php' ? 'active' : ''; ?>" 
                   href="interested_students.php">
                    <i class="fas fa-heart me-2"></i>Interested Students
                </a>
            </li>
        </ul>
    </div>
</nav>