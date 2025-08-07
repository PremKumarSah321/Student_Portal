<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $programme_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM Programmes WHERE ProgrammeID = ?";
    $delete_stmt = $db->prepare($delete_query);
    if ($delete_stmt->execute([$programme_id])) {
        $success_message = "Programme deleted successfully.";
    } else {
        $error_message = "Error deleting programme.";
    }
}

// Handle add/edit programme
if ($_POST && isset($_POST['save_programme'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $programme_name = sanitizeInput($_POST['programme_name']);
        $level_id = (int)$_POST['level_id'];
        $programme_leader_id = (int)$_POST['programme_leader_id'];
        $description = sanitizeInput($_POST['description']);
        $programme_id = isset($_POST['programme_id']) ? (int)$_POST['programme_id'] : 0;
        
        if ($programme_name && $level_id) {
            if ($programme_id) {
                // Update existing programme
                $update_query = "UPDATE Programmes SET ProgrammeName = ?, LevelID = ?, ProgrammeLeaderID = ?, Description = ? WHERE ProgrammeID = ?";
                $update_stmt = $db->prepare($update_query);
                if ($update_stmt->execute([$programme_name, $level_id, $programme_leader_id, $description, $programme_id])) {
                    $success_message = "Programme updated successfully.";
                } else {
                    $error_message = "Error updating programme.";
                }
            } else {
                // Add new programme
                $insert_query = "INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description) VALUES (?, ?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                if ($insert_stmt->execute([$programme_name, $level_id, $programme_leader_id, $description])) {
                    $success_message = "Programme added successfully.";
                } else {
                    $error_message = "Error adding programme.";
                }
            }
        } else {
            $error_message = "Please fill in all required fields.";
        }
    }
}

// Get programmes
$query = "SELECT p.*, l.LevelName, s.Name as ProgrammeLeaderName 
          FROM Programmes p 
          JOIN Levels l ON p.LevelID = l.LevelID 
          LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID 
          ORDER BY l.LevelID, p.ProgrammeName";
$stmt = $db->prepare($query);
$stmt->execute();
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get levels for dropdown
$levels_query = "SELECT * FROM Levels ORDER BY LevelID";
$levels_stmt = $db->prepare($levels_query);
$levels_stmt->execute();
$levels = $levels_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get staff for dropdown
$staff_query = "SELECT * FROM Staff ORDER BY Name";
$staff_stmt = $db->prepare($staff_query);
$staff_stmt->execute();
$staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Programmes';
include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Programmes</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#programmeModal">
                    <i class="fas fa-plus me-1"></i>Add Programme
                </button>
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Programme Name</th>
                                    <th>Level</th>
                                    <th>Programme Leader</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programmes as $programme): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($programme['ProgrammeName']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $programme['LevelID'] == 1 ? 'primary' : 'success'; ?>">
                                            <?php echo htmlspecialchars($programme['LevelName']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($programme['ProgrammeLeaderName'] ?? 'Not assigned'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($programme['Description'], 0, 100)) . '...'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editProgramme(<?php echo htmlspecialchars(json_encode($programme)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=1&id=<?php echo $programme['ProgrammeID']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this programme?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Programme Modal -->
<div class="modal fade" id="programmeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Programme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="programme_id" id="programmeId">
                    
                    <div class="mb-3">
                        <label for="programmeName" class="form-label">Programme Name *</label>
                        <input type="text" class="form-control" id="programmeName" name="programme_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="levelId" class="form-label">Level *</label>
                        <select class="form-select" id="levelId" name="level_id" required>
                            <option value="">Select Level</option>
                            <?php foreach ($levels as $level): ?>
                            <option value="<?php echo $level['LevelID']; ?>">
                                <?php echo htmlspecialchars($level['LevelName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="programmeLeaderId" class="form-label">Programme Leader</label>
                        <select class="form-select" id="programmeLeaderId" name="programme_leader_id">
                            <option value="">Select Programme Leader</option>
                            <?php foreach ($staff as $member): ?>
                            <option value="<?php echo $member['StaffID']; ?>">
                                <?php echo htmlspecialchars($member['Name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_programme" class="btn btn-primary">Save Programme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProgramme(programme) {
    document.getElementById('modalTitle').textContent = 'Edit Programme';
    document.getElementById('programmeId').value = programme.ProgrammeID;
    document.getElementById('programmeName').value = programme.ProgrammeName;
    document.getElementById('levelId').value = programme.LevelID;
    document.getElementById('programmeLeaderId').value = programme.ProgrammeLeaderID || '';
    document.getElementById('description').value = programme.Description || '';
    
    var modal = new bootstrap.Modal(document.getElementById('programmeModal'));
    modal.show();
}

// Reset form when modal is closed
document.getElementById('programmeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Add Programme';
    document.querySelector('#programmeModal form').reset();
    document.getElementById('programmeId').value = '';
});
</script>

<?php include 'includes/admin_footer.php'; ?>