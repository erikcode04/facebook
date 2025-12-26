<?php
// Starta output buffering för att rensa bort oönskad output
ob_start();

session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Rensa output buffer
ob_end_clean();

header('Content-Type: application/json');

// Kontrollera om användaren är inloggad
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Ej inloggad']);
    exit;
}

try {
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    $user_id = $_SESSION['user_id'];

    if ($post_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltigt post-ID']);
        exit;
    }

    // Kontrollera om användaren gillat inlägget
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $liked = $stmt->fetch() ? true : false;

    echo json_encode([
        'success' => true,
        'liked' => $liked
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
