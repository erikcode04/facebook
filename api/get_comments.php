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

    if ($post_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltigt post-ID']);
        exit;
    }

    // Hämta kommentarer för inlägget
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.content,
            c.created_at,
            u.username,
            u.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod vid hämtning av kommentarer',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
