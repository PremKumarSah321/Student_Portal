<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

header('Content-Type: application/json');

$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$staff_id) {
    echo json_encode(['error' => 'Invalid staff ID']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$response = [
    'programmes' => [],
    'modules' => []
];

// Get programmes led by this staff member
$programmes_query = "SELECT p.*, l.LevelName 
                     FROM Programmes p 
                     JOIN Levels l ON p.LevelID = l.LevelID 
                     WHERE p.ProgrammeLeaderID = ? 
                     ORDER BY p.ProgrammeName";
$programmes_stmt = $db->prepare($programmes_query);
$programmes_stmt->execute([$staff_id]);
$response['programmes'] = $programmes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get modules led by this staff member
$modules_query = "SELECT m.* 
                  FROM Modules m 
                  WHERE m.ModuleLeaderID = ? 
                  ORDER BY m.ModuleName";
$modules_stmt = $db->prepare($modules_query);
$modules_stmt->execute([$staff_id]);
$response['modules'] = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($response);
?>