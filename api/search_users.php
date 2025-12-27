<?php
ob_start();
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
ob_end_clean();

header('Content-Type: application/json');

// Kontrollera om användaren är inloggad
if (!is_logged_in()) {
    error_log("search_users.php: Användare ej inloggad");
    http_response_code(401);
    echo json_encode(['error' => 'Ej inloggad']);
    exit;
}

error_log("search_users.php: Användare inloggad, user_id=" . $_SESSION['user_id']);

try {
    $current_user_id = $_SESSION['user_id'];
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

    error_log("search_users.php: Sökfråga='$search_query', user_id=$current_user_id");

    // Custom debug log
    file_put_contents(
        __DIR__ . '/../debug.log',
        date('Y-m-d H:i:s') . " - Sökfråga: $search_query\n",
        FILE_APPEND
    );

    // Minst 1 tecken för att söka
    if (strlen($search_query) < 1) {
        error_log("search_users.php: Tom sökfråga, returnerar tomt resultat");
        echo json_encode([
            'success' => true,
            'users' => []
        ]);
        exit;
    }

    // Sök användare baserat på användarnamn eller email
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.profile_picture,
            u.bio,
            (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers_count,
            (SELECT COUNT(*) FROM follows WHERE follower_id = u.id) as following_count,
            EXISTS(
                SELECT 1 FROM follows 
                WHERE follower_id = :current_user_id1 
                AND following_id = u.id
            ) as is_following
        FROM users u
        WHERE (u.username LIKE :search_pattern1 OR u.email LIKE :search_pattern2)
        ORDER BY u.username ASC
        LIMIT 20
    ");

    $search_pattern = '%' . $search_query . '%';
    $stmt->bindValue(':search_pattern1', $search_pattern, PDO::PARAM_STR);
    $stmt->bindValue(':search_pattern2', $search_pattern, PDO::PARAM_STR);
    $stmt->bindValue(':current_user_id1', $current_user_id, PDO::PARAM_INT);

    error_log("search_users.php: Kör SQL-fråga med pattern='$search_pattern'");
    $stmt->execute();

    $users = $stmt->fetchAll();
    error_log("search_users.php: Hittade " . count($users) . " användare");

    // Debug: logga antal resultat
    file_put_contents(
        __DIR__ . '/../debug.log',
        date('Y-m-d H:i:s') . " - Hittade " . count($users) . " användare\n",
        FILE_APPEND
    );

    // Konvertera is_following till boolean
    foreach ($users as &$user) {
        $user['is_following'] = (bool)$user['is_following'];
    }

    error_log("search_users.php: Returnerar JSON med " . count($users) . " användare");
    echo json_encode([
        'success' => true,
        'users' => $users,
        'query' => $search_query
    ]);
} catch (Exception $e) {
    error_log("search_users.php: FEL - " . $e->getMessage());
    error_log("search_users.php: Stack trace - " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod vid sökning',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
