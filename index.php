<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Home';
$page_description = 'Discover comprehensive degree programmes in computing, AI, cybersecurity, and data science at Student Course Hub.';
$database = new Database();
$db = $database->getConnection();

// Get programme counts
$query = "SELECT l.LevelName, COUNT(p.ProgrammeID) as count 
          FROM Levels l 
          LEFT JOIN Programmes p ON l.LevelID = p.LevelID 
          GROUP BY l.LevelID, l.LevelName";
$stmt = $db->prepare($query);
$stmt->execute();
$programme_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<section class="hero-section bg-primary text-white py-5" aria-labelledby="hero-heading">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 id="hero-heading" class="display-4 fw-bold mb-4">Discover Your Future</h1>
                <p class="lead mb-4">Explore our comprehensive range of undergraduate and postgraduate degree programmes designed to prepare you for success in the digital age.</p>
                <a href="programmes.php" class="btn btn-light btn-lg" aria-describedby="browse-help">
                    <i class="fas fa-search me-2" aria-hidden="true"></i>Browse Programmes
                </a>
                <div id="browse-help" class="visually-hidden">View all available degree programmes</div>
            </div>
            <div class="col-lg-6 text-center" aria-hidden="true">
                <i class="fas fa-graduation-cap" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <section aria-labelledby="features-heading">
                <h2 id="features-heading" class="mb-4">Why Choose Our Programmes?</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <article class="card h-100" tabindex="0">
                            <div class="card-body">
                                <div class="text-primary mb-3" aria-hidden="true">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h3 class="card-title h5">Expert Faculty</h3>
                                <p class="card-text">Learn from industry experts and renowned academics who bring real-world experience to the classroom.</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-6 mb-4">
                        <article class="card h-100" tabindex="0">
                            <div class="card-body">
                                <div class="text-primary mb-3" aria-hidden="true">
                                    <i class="fas fa-laptop-code fa-2x"></i>
                                </div>
                                <h3 class="card-title h5">Cutting-Edge Technology</h3>
                                <p class="card-text">Access state-of-the-art facilities and the latest technology to enhance your learning experience.</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-6 mb-4">
                        <article class="card h-100" tabindex="0">
                            <div class="card-body">
                                <div class="text-primary mb-3" aria-hidden="true">
                                    <i class="fas fa-briefcase fa-2x"></i>
                                </div>
                                <h3 class="card-title h5">Career Support</h3>
                                <p class="card-text">Benefit from our strong industry connections and comprehensive career guidance services.</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-6 mb-4">
                        <article class="card h-100" tabindex="0">
                            <div class="card-body">
                                <div class="text-primary mb-3" aria-hidden="true">
                                    <i class="fas fa-globe fa-2x"></i>
                                </div>
                                <h3 class="card-title h5">Global Perspective</h3>
                                <p class="card-text">Gain international exposure through our partnerships and exchange programmes.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <aside aria-labelledby="overview-heading">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 id="overview-heading" class="mb-0 h5">
                            <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>Programme Overview
                        </h2>
                    </div>
                    <div class="card-body">
                        <dl class="mb-3">
                            <?php foreach ($programme_counts as $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <dt><?php echo htmlspecialchars($count['LevelName']); ?></dt>
                                <dd class="badge bg-primary mb-0">
                                    <?php echo $count['count']; ?> programme<?php echo $count['count'] !== 1 ? 's' : ''; ?>
                                </dd>
                            </div>
                            <?php endforeach; ?>
                        </dl>
                        <hr>
                        <a href="programmes.php" class="btn btn-outline-primary w-100" aria-describedby="view-all-help">
                            View All Programmes
                        </a>
                        <div id="view-all-help" class="visually-hidden">Browse detailed information about all available degree programmes</div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>