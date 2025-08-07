<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $staff_id = (int)$_GET['id'];
    
    // Check if staff member is assigned to any programmes or modules
    $check_programmes = "SELECT COUNT(*) as count FROM Programmes WHERE ProgrammeLeaderID = ?";
    $check_programmes_stmt = $db->prepare($check_programmes);
    $check_programmes_stmt->execute([$staff_id]);
    $programme_count = $check_programmes_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $check_modules = "SELECT COUNT(*) as count FROM Modules WHERE ModuleLeaderID = ?";
    $check_modules_stmt = $db->prepare($check_modules);
    $check_modules_stmt->execute([$staff_id]);
    $module_count = $check_modules_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($programme_count > 0 || $module_count > 0) {
        $error_message = "Cannot delete this staff member. They are assigned as a leader for $programme_count programme(s) and $module_count module(s). Please reassign their responsibilities first.";
    } else {
        $delete_query = "DELETE FROM Staff WHERE StaffID = ?";
        $delete_stmt = $db->prepare($delete_query);
        if ($delete_stmt->execute([$staff_id])) {
            $success_message = "Staff member deleted successfully.";
        } else {
            $error_message = "Error deleting staff member.";
        }
    }
}

// Handle add/edit staff
if ($_POST && isset($_POST['save_staff'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $name = sanitizeInput($_POST['name']);
        $staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
        
        if ($name) {
            if ($staff_id) {
                // Update existing staff
                $update_query = "UPDATE Staff SET Name = ? WHERE StaffID = ?";
                $update_stmt = $db->prepare($update_query);
                if ($update_stmt->execute([$name, $staff_id])) {
                    $success_message = "Staff member updated successfully.";
                } else {
                    $error_message = "Error updating staff member.";
                }
            } else {
                // Add new staff - get next available ID
                $max_id_query = "SELECT COALESCE(MAX(StaffID), 0) + 1 as next_id FROM Staff";
                $max_id_stmt = $db->prepare($max_id_query);
                $max_id_stmt->execute();
                $next_id = $max_id_stmt->fetch(PDO::FETCH_ASSOC)['next_id'];
                
                $insert_query = "INSERT INTO Staff (StaffID, Name) VALUES (?, ?)";
                $insert_stmt = $db->prepare($insert_query);
                if ($insert_stmt->execute([$next_id, $name])) {
                    $success_message = "Staff member added successfully.";
                } else {
                    $error_message = "Error adding staff member.";
                }
            }
        } else {
            $error_message = "Please fill in the staff name.";
        }
    }
}

// Get staff with their responsibilities
$query = "SELECT s.*, 
          COUNT(DISTINCT p.ProgrammeID) as programmes_led,
          COUNT(DISTINCT m.ModuleID) as modules_led
          FROM Staff s
          LEFT JOIN Programmes p ON s.StaffID = p.ProgrammeLeaderID
          LEFT JOIN Modules m ON s.StaffID = m.ModuleLeaderID
          GROUP BY s.StaffID, s.Name
          ORDER BY s.Name";
$stmt = $db->prepare($query);
$stmt->execute();
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Staff';
include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Staff</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staffModal">
                    <i class="fas fa-plus me-1"></i>Add Staff Member
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
                                    <th>Name</th>
                                    <th>Programmes Led</th>
                                    <th>Modules Led</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $staff): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($staff['Name']); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $staff['programmes_led']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $staff['modules_led']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info" onclick="viewStaffDetails(<?php echo $staff['StaffID']; ?>, '<?php echo htmlspecialchars($staff['Name']); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editStaff(<?php echo htmlspecialchars(json_encode($staff)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=1&id=<?php echo $staff['StaffID']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirmDelete('<?php echo htmlspecialchars($staff['Name']); ?>', <?php echo $staff['programmes_led']; ?>, <?php echo $staff['modules_led']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
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

<!-- Staff Modal -->
<div class="modal fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="staff_id" id="staffId">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_staff" class="btn btn-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Staff Details Modal -->
<div class="modal fade" id="staffDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalTitle">Staff Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="staffDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading staff details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editStaff(staff) {
    document.getElementById('modalTitle').textContent = 'Edit Staff Member';
    document.getElementById('staffId').value = staff.StaffID;
    document.getElementById('name').value = staff.Name;
    
    var modal = new bootstrap.Modal(document.getElementById('staffModal'));
    modal.show();
}

function viewStaffDetails(staffId, staffName) {
    document.getElementById('detailsModalTitle').textContent = `${staffName} - Responsibilities`;
    document.getElementById('staffDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading staff details...</p>
        </div>
    `;
    
    var modal = new bootstrap.Modal(document.getElementById('staffDetailsModal'));
    modal.show();
    
    // Fetch staff details via AJAX
    fetch(`get_staff_details.php?id=${staffId}`)
        .then(response => response.json())
        .then(data => {
            let content = '';
            
            if (data.programmes && data.programmes.length > 0) {
                content += `
                    <div class="mb-4">
                        <h6 class="text-primary"><i class="fas fa-graduation-cap me-2"></i>Programmes Led</h6>
                        <div class="list-group">
                `;
                data.programmes.forEach(programme => {
                    content += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${programme.ProgrammeName}</h6>
                                <small class="badge bg-${programme.LevelID == 1 ? 'primary' : 'success'}">${programme.LevelName}</small>
                            </div>
                            <p class="mb-1">${programme.Description || 'No description available'}</p>
                        </div>
                    `;
                });
                content += '</div></div>';
            }
            
            if (data.modules && data.modules.length > 0) {
                content += `
                    <div class="mb-4">
                        <h6 class="text-success"><i class="fas fa-book me-2"></i>Modules Led</h6>
                        <div class="list-group">
                `;
                data.modules.forEach(module => {
                    content += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${module.ModuleName}</h6>
                            </div>
                            <p class="mb-1">${module.Description || 'No description available'}</p>
                        </div>
                    `;
                });
                content += '</div></div>';
            }
            
            if ((!data.programmes || data.programmes.length === 0) && (!data.modules || data.modules.length === 0)) {
                content = `
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5>No Responsibilities Assigned</h5>
                        <p class="text-muted">This staff member is not currently assigned as a leader for any programmes or modules.</p>
                    </div>
                `;
            }
            
            document.getElementById('staffDetailsContent').innerHTML = content;
        })
        .catch(error => {
            document.getElementById('staffDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading staff details. Please try again.
                </div>
            `;
        });
}

function confirmDelete(name, programmesLed, modulesLed) {
    if (programmesLed > 0 || modulesLed > 0) {
        alert(`Cannot delete ${name}. They are currently assigned as:\n- Programme Leader for ${programmesLed} programme(s)\n- Module Leader for ${modulesLed} module(s)\n\nPlease reassign their responsibilities first.`);
        return false;
    }
    return confirm(`Are you sure you want to delete ${name}?`);
}

// Reset form when modal is closed
document.getElementById('staffModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Add Staff Member';
    document.querySelector('#staffModal form').reset();
    document.getElementById('staffId').value = '';
});
</script>

<?php include 'includes/admin_footer.php'; ?>