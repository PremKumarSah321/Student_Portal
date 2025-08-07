<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Programmes';
$page_description = 'Browse our comprehensive range of undergraduate and postgraduate degree programmes in computing, AI, cybersecurity, and data science.';
$database = new Database();
$db = $database->getConnection();

// Get all programmes with level information
$query = "SELECT p.*, l.LevelName, s.Name as ProgrammeLeaderName 
          FROM Programmes p 
          JOIN Levels l ON p.LevelID = l.LevelID 
          LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID 
          ORDER BY l.LevelID, p.ProgrammeName";
$stmt = $db->prepare($query);
$stmt->execute();
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get levels for filter
$level_query = "SELECT * FROM Levels ORDER BY LevelID";
$level_stmt = $db->prepare($level_query);
$level_stmt->execute();
$levels = $level_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<section class="search-filter-section bg-light py-4" aria-labelledby="search-heading">
    <div class="container">
        <h2 id="search-heading" class="visually-hidden">Search and Filter Programmes</h2>
        <form role="search" aria-label="Programme search and filter">
            <div class="row">
                <div class="col-md-8">
                    <label for="searchInput" class="form-label fw-semibold">Search Programmes</label>
                    <input type="search" class="form-control" id="searchInput" 
                           placeholder="Search by programme name or keywords..." 
                           aria-describedby="searchHelp"
                           autocomplete="off"
                           spellcheck="false">
                    <div id="searchHelp" class="form-text">
                        Enter keywords like 'Cyber Security', 'AI', or 'Data Science'. Press Escape to clear search.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="levelFilter" class="form-label fw-semibold">Filter by Level</label>
                    <select class="form-select" id="levelFilter" aria-describedby="filterHelp">
                        <option value="">All Levels</option>
                        <?php foreach ($levels as $level): ?>
                        <option value="<?php echo $level['LevelID']; ?>">
                            <?php echo htmlspecialchars($level['LevelName']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="filterHelp" class="form-text">Filter programmes by academic level</div>
                </div>
            </div>
        </form>
        <div class="mt-2">
            <p class="small text-muted mb-0">
                <strong>Keyboard shortcuts:</strong> Alt+S to focus search, Alt+M for main content, Alt+N for navigation
            </p>
        </div>
    </div>
</section>

<div class="container my-5">
    <header class="mb-5">
        <h1 class="mb-4">Our Programmes</h1>
        <p class="lead">Discover the perfect programme to launch your career in technology and computing.</p>
    </header>
    
    <section aria-labelledby="programmes-list-heading" aria-live="polite">
        <h2 id="programmes-list-heading" class="visually-hidden">Available Programmes</h2>
        <div class="row" id="programmesContainer" role="region" aria-label="Programme listings">
            <?php foreach ($programmes as $programme): ?>
            <div class="col-md-6 mb-4">
                <article class="card programme-card h-100" 
                         data-level="<?php echo $programme['LevelID']; ?>"
                         aria-labelledby="programme-<?php echo $programme['ProgrammeID']; ?>-title">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 id="programme-<?php echo $programme['ProgrammeID']; ?>-title" class="card-title h5">
                                <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
                            </h3>
                            <span class="badge bg-<?php echo $programme['LevelID'] == 1 ? 'primary' : 'success'; ?> level-badge"
                                  aria-label="Academic level">
                                <?php echo htmlspecialchars($programme['LevelName']); ?>
                            </span>
                        </div>
                        <p class="card-text"><?php echo htmlspecialchars($programme['Description']); ?></p>
                        <?php if ($programme['ProgrammeLeaderName']): ?>
                        <p class="text-muted small">
                            <i class="fas fa-user me-1" aria-hidden="true"></i>
                            <span class="visually-hidden">Programme Leader: </span>
                            <?php echo htmlspecialchars($programme['ProgrammeLeaderName']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="programme-details.php?id=<?php echo $programme['ProgrammeID']; ?>" 
                           class="btn btn-outline-primary"
                           aria-describedby="programme-<?php echo $programme['ProgrammeID']; ?>-desc">
                            <i class="fas fa-info-circle me-1" aria-hidden="true"></i>View Details
                        </a>
                        <div id="programme-<?php echo $programme['ProgrammeID']; ?>-desc" class="visually-hidden">
                            View detailed information about <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($programmes)): ?>
        <div class="text-center py-5" role="status" aria-live="polite">
            <i class="fas fa-search fa-3x text-muted mb-3" aria-hidden="true"></i>
            <h3>No programmes found</h3>
            <p class="text-muted">Please check back later for available programmes.</p>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>