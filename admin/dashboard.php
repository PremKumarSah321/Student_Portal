<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total programmes
$query = "SELECT COUNT(*) as count FROM Programmes";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['programmes'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total modules
$query = "SELECT COUNT(*) as count FROM Modules";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['modules'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total interested students
$query = "SELECT COUNT(*) as count FROM InterestedStudents";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['interested_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent interest registrations
$query = "SELECT ist.*, p.ProgrammeName 
          FROM InterestedStudents ist 
          JOIN Programmes p ON ist.ProgrammeID = p.ProgrammeID 
          ORDER BY ist.RegisteredAt DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_interests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin Dashboard';
include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Programmes</h5>
                                    <h2 class="mb-0"><?php echo $stats['programmes']; ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Modules</h5>
                                    <h2 class="mb-0"><?php echo $stats['modules']; ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Interested Students</h5>
                                    <h2 class="mb-0"><?php echo $stats['interested_students']; ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Interest Registrations</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_interests)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>Programme</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_interests as $interest): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($interest['StudentName']); ?></td>
                                            <td><?php echo htmlspecialchars($interest['Email']); ?></td>
                                            <td><?php echo htmlspecialchars($interest['ProgrammeName']); ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($interest['RegisteredAt'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="interested_students.php" class="btn btn-outline-primary">
                                    View All Interested Students
                                </a>
                            </div>
                            <?php else: ?>
                            <p class="text-muted text-center">No interest registrations yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>