<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get programme ID from URL
$programme_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$programme_id) {
    header('Location: programmes.php');
    exit();
}

// Handle interest registration
if ($_POST && isset($_POST['register_interest'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $student_name = sanitizeInput($_POST['student_name']);
        $student_email = sanitizeInput($_POST['student_email']);
        
        if ($student_name && validateEmail($student_email)) {
            // Check if already registered
            $check_query = "SELECT InterestID FROM InterestedStudents 
                           WHERE ProgrammeID = ? AND Email = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$programme_id, $student_email]);
            
            if ($check_stmt->rowCount() == 0) {
                $insert_query = "INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) 
                                VALUES (?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                if ($insert_stmt->execute([$programme_id, $student_name, $student_email])) {
                    $success_message = "Thank you for your interest! We'll keep you updated about this programme.";
                } else {
                    $error_message = "Sorry, there was an error processing your request. Please try again.";
                }
            } else {
                $info_message = "You have already registered interest in this programme.";
            }
        } else {
            $error_message = "Please provide a valid name and email address.";
        }
    } else {
        $error_message = "Invalid request. Please try again.";
    }
}

// Get programme details
$query = "SELECT p.*, l.LevelName, s.Name as ProgrammeLeaderName 
          FROM Programmes p 
          JOIN Levels l ON p.LevelID = l.LevelID 
          LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID 
          WHERE p.ProgrammeID = ?";
$stmt = $db->prepare($query);
$stmt->execute([$programme_id]);
$programme = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$programme) {
    header('Location: programmes.php');
    exit();
}

// Get modules for this programme grouped by year
$modules_query = "SELECT m.*, pm.Year, s.Name as ModuleLeaderName
                  FROM ProgrammeModules pm
                  JOIN Modules m ON pm.ModuleID = m.ModuleID
                  LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
                  WHERE pm.ProgrammeID = ?
                  ORDER BY pm.Year, m.ModuleName";
$modules_stmt = $db->prepare($modules_query);
$modules_stmt->execute([$programme_id]);
$modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group modules by year
$modules_by_year = [];
foreach ($modules as $module) {
    $modules_by_year[$module['Year']][] = $module;
}

$page_title = $programme['ProgrammeName'];
$page_description = 'Detailed information about ' . $programme['ProgrammeName'] . ' - ' . $programme['Description'];
include 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="Breadcrumb navigation">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php" aria-label="Go to home page">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="programmes.php" aria-label="Go to programmes listing">Programmes</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h1>
                    <span class="badge bg-<?php echo $programme['LevelID'] == 1 ? 'primary' : 'success'; ?> fs-6">
                        <?php echo htmlspecialchars($programme['LevelName']); ?>
                    </span>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Programme Overview</h5>
                    <p class="card-text"><?php echo htmlspecialchars($programme['Description']); ?></p>
                    <?php if ($programme['ProgrammeLeaderName']): ?>
                    <p class="text-muted">
                        <i class="fas fa-user me-2"></i>
                        <strong>Programme Leader:</strong> <?php echo htmlspecialchars($programme['ProgrammeLeaderName']); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($modules_by_year)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Programme Structure</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($modules_by_year as $year => $year_modules): ?>
                    <h6 class="text-primary mb-3">
                        <?php echo $programme['LevelID'] == 1 ? "Year $year" : "Modules"; ?>
                    </h6>
                    <div class="row mb-4">
                        <?php foreach ($year_modules as $module): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card module-card">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($module['ModuleName']); ?></h6>
                                    <?php if ($module['Description']): ?>
                                    <p class="card-text small"><?php echo htmlspecialchars($module['Description']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($module['ModuleLeaderName']): ?>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        <?php echo htmlspecialchars($module['ModuleLeaderName']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <aside aria-labelledby="interest-form-heading">
                <div class="card interest-form">
                    <div class="card-header bg-primary text-white">
                        <h2 id="interest-form-heading" class="mb-0 h5">
                            <i class="fas fa-heart me-2" aria-hidden="true"></i>Register Your Interest
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success" role="alert" aria-live="polite">
                            <i class="fas fa-check-circle me-2" aria-hidden="true"></i><?php echo $success_message; ?>
                        </div>
                        <?php elseif (isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert" aria-live="assertive">
                            <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i><?php echo $error_message; ?>
                        </div>
                        <?php elseif (isset($info_message)): ?>
                        <div class="alert alert-info" role="alert" aria-live="polite">
                            <i class="fas fa-info-circle me-2" aria-hidden="true"></i><?php echo $info_message; ?>
                        </div>
                        <?php endif; ?>

                        <p class="small text-muted mb-3">
                            Stay updated with programme information, open days, and application deadlines.
                        </p>

                        <form method="POST" id="interestForm" novalidate aria-describedby="form-instructions">
                            <div id="form-instructions" class="visually-hidden">
                                Fill out this form to register your interest in <?php echo htmlspecialchars($programme['ProgrammeName']); ?>. 
                                All fields marked with an asterisk are required.
                            </div>
                            
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="studentName" class="form-label">
                                    Full Name <span aria-label="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="studentName" name="student_name" 
                                       required aria-describedby="nameHelp" autocomplete="name">
                                <div id="nameHelp" class="form-text">Enter your full name as you'd like to be contacted.</div>
                            </div>

                            <div class="mb-3">
                                <label for="studentEmail" class="form-label">
                                    Email Address <span aria-label="required">*</span>
                                </label>
                                <input type="email" class="form-control" id="studentEmail" name="student_email" 
                                       required aria-describedby="emailHelp" autocomplete="email">
                                <div id="emailHelp" class="form-text">We'll use this to send you programme updates.</div>
                            </div>

                            <button type="submit" name="register_interest" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2" aria-hidden="true"></i>Register Interest
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h6">Need More Information?</h3>
                        <p class="card-text small">Contact our admissions team for detailed programme information and guidance.</p>
                        <a href="mailto:admissions@university.ac.uk" class="btn btn-outline-primary btn-sm"
                           aria-describedby="contact-help">
                            <i class="fas fa-envelope me-1" aria-hidden="true"></i>Contact Admissions
                        </a>
                        <div id="contact-help" class="visually-hidden">
                            Send an email to the admissions team for more information about this programme
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>