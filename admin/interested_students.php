<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $interest_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM InterestedStudents WHERE InterestID = ?";
    $delete_stmt = $db->prepare($delete_query);
    if ($delete_stmt->execute([$interest_id])) {
        $success_message = "Interest registration deleted successfully.";
    } else {
        $error_message = "Error deleting interest registration.";
    }
}

// Get filter parameters
$programme_filter = isset($_GET['programme']) ? (int)$_GET['programme'] : 0;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($programme_filter) {
    $where_conditions[] = "p.ProgrammeID = ?";
    $params[] = $programme_filter;
}

if ($search) {
    $where_conditions[] = "(ist.StudentName LIKE ? OR ist.Email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get interested students with programme information
$query = "SELECT ist.*, p.ProgrammeName, l.LevelName 
          FROM InterestedStudents ist 
          JOIN Programmes p ON ist.ProgrammeID = p.ProgrammeID 
          JOIN Levels l ON p.LevelID = l.LevelID 
          $where_clause
          ORDER BY ist.RegisteredAt DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$interested_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get programmes for filter dropdown
$programmes_query = "SELECT ProgrammeID, ProgrammeName FROM Programmes ORDER BY ProgrammeName";
$programmes_stmt = $db->prepare($programmes_query);
$programmes_stmt->execute();
$programmes = $programmes_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Interested Students';
include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Interested Students</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportToCSV()">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </button>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name or email">
                        </div>
                        <div class="col-md-4">
                            <label for="programme" class="form-label">Programme</label>
                            <select class="form-select" id="programme" name="programme">
                                <option value="">All Programmes</option>
                                <?php foreach ($programmes as $programme): ?>
                                <option value="<?php echo $programme['ProgrammeID']; ?>" 
                                        <?php echo $programme_filter == $programme['ProgrammeID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="interested_students.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Interest Registrations 
                        <span class="badge bg-primary"><?php echo count($interested_students); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($interested_students)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Programme</th>
                                    <th>Level</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($interested_students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($student['Email']); ?>">
                                            <?php echo htmlspecialchars($student['Email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['ProgrammeName']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $student['LevelName'] == 'Undergraduate' ? 'primary' : 'success'; ?>">
                                            <?php echo htmlspecialchars($student['LevelName']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($student['RegisteredAt'])); ?></td>
                                    <td>
                                        <a href="?delete=1&id=<?php echo $student['InterestID']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this interest registration?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No interest registrations found</h5>
                        <p class="text-muted">
                            <?php if ($search || $programme_filter): ?>
                            Try adjusting your search criteria.
                            <?php else: ?>
                            Students haven't registered interest in programmes yet.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function exportToCSV() {
    const table = document.getElementById('studentsTable');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // Exclude actions column
            let cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'interested_students_' + new Date().toISOString().split('T')[0] + '.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php include 'includes/admin_footer.php'; ?>