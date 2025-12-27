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
    http_response_code(401);
    echo json_encode(['error' => 'Ej inloggad']);
    exit;
}

// Endast POST-metod
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Endast POST tillåten']);
    exit;
}

try {
    $current_user_id = $_SESSION['user_id'];

    // Hämta JSON-data
    $input = json_decode(file_get_contents('php://input'), true);
    $target_user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    $action = isset($input['action']) ? $input['action'] : ''; // 'follow' eller 'unfollow'

    // Validering
    if ($target_user_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltigt användar-ID']);
        exit;
    }

    if ($target_user_id == $current_user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Du kan inte följa dig själv']);
        exit;
    }

    // Verifiera att användaren existerar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Användaren hittades inte']);
        exit;
    }

    if ($action === 'follow') {
        // Följ användaren (INSERT IGNORE för att undvika duplicat)
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO follows (follower_id, following_id) 
            VALUES (:follower_id, :following_id)
        ");
        $stmt->bindValue(':follower_id', $current_user_id, PDO::PARAM_INT);
        $stmt->bindValue(':following_id', $target_user_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'action' => 'followed',
            'message' => 'Du följer nu denna användare'
        ]);
    } elseif ($action === 'unfollow') {
        // Sluta följa användaren
        $stmt = $pdo->prepare("
            DELETE FROM follows 
            WHERE follower_id = :follower_id 
            AND following_id = :following_id
        ");
        $stmt->bindValue(':follower_id', $current_user_id, PDO::PARAM_INT);
        $stmt->bindValue(':following_id', $target_user_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'action' => 'unfollowed',
            'message' => 'Du följer inte längre denna användare'
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltig action (använd follow eller unfollow)']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
