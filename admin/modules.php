<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $module_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM Modules WHERE ModuleID = ?";
    $delete_stmt = $db->prepare($delete_query);
    if ($delete_stmt->execute([$module_id])) {
        $success_message = "Module deleted successfully.";
    } else {
        $error_message = "Error deleting module.";
    }
}

// Handle add/edit module
if ($_POST && isset($_POST['save_module'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $module_name = sanitizeInput($_POST['module_name']);
        $module_leader_id = (int)$_POST['module_leader_id'];
        $description = sanitizeInput($_POST['description']);
        $module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : 0;
        
        if ($module_name) {
            if ($module_id) {
                // Update existing module
                $update_query = "UPDATE Modules SET ModuleName = ?, ModuleLeaderID = ?, Description = ? WHERE ModuleID = ?";
                $update_stmt = $db->prepare($update_query);
                if ($update_stmt->execute([$module_name, $module_leader_id, $description, $module_id])) {
                    $success_message = "Module updated successfully.";
                } else {
                    $error_message = "Error updating module.";
                }
            } else {
                // Add new module - get next available ID
                $max_id_query = "SELECT COALESCE(MAX(ModuleID), 0) + 1 as next_id FROM Modules";
                $max_id_stmt = $db->prepare($max_id_query);
                $max_id_stmt->execute();
                $next_id = $max_id_stmt->fetch(PDO::FETCH_ASSOC)['next_id'];
                
                $insert_query = "INSERT INTO Modules (ModuleID, ModuleName, ModuleLeaderID, Description) VALUES (?, ?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                if ($insert_stmt->execute([$next_id, $module_name, $module_leader_id, $description])) {
                    $success_message = "Module added successfully.";
                } else {
                    $error_message = "Error adding module.";
                }
            }
        } else {
            $error_message = "Please fill in the module name.";
        }
    }
}

// Get modules
$query = "SELECT m.*, s.Name as ModuleLeaderName 
          FROM Modules m 
          LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID 
          ORDER BY m.ModuleName";
$stmt = $db->prepare($query);
$stmt->execute();
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get staff for dropdown
$staff_query = "SELECT * FROM Staff ORDER BY Name";
$staff_stmt = $db->prepare($staff_query);
$staff_stmt->execute();
$staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Modules';
include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Modules</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#moduleModal">
                    <i class="fas fa-plus me-1"></i>Add Module
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
                                    <th>Module Name</th>
                                    <th>Module Leader</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($module['ModuleName']); ?></td>
                                    <td><?php echo htmlspecialchars($module['ModuleLeaderName'] ?? 'Not assigned'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($module['Description'], 0, 100)) . '...'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editModule(<?php echo htmlspecialchars(json_encode($module)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=1&id=<?php echo $module['ModuleID']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this module?')">
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

<!-- Module Modal -->
<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="module_id" id="moduleId">
                    
                    <div class="mb-3">
                        <label for="moduleName" class="form-label">Module Name *</label>
                        <input type="text" class="form-control" id="moduleName" name="module_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="moduleLeaderId" class="form-label">Module Leader</label>
                        <select class="form-select" id="moduleLeaderId" name="module_leader_id">
                            <option value="">Select Module Leader</option>
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
                    <button type="submit" name="save_module" class="btn btn-primary">Save Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(module) {
    document.getElementById('modalTitle').textContent = 'Edit Module';
    document.getElementById('moduleId').value = module.ModuleID;
    document.getElementById('moduleName').value = module.ModuleName;
    document.getElementById('moduleLeaderId').value = module.ModuleLeaderID || '';
    document.getElementById('description').value = module.Description || '';
    
    var modal = new bootstrap.Modal(document.getElementById('moduleModal'));
    modal.show();
}

// Reset form when modal is closed
document.getElementById('moduleModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Add Module';
    document.querySelector('#moduleModal form').reset();
    document.getElementById('moduleId').value = '';
});
</script>

<?php include 'includes/admin_footer.php'; ?>