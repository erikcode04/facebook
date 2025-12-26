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
    // Läs JSON-data från request body
    $input = json_decode(file_get_contents('php://input'), true);
    $post_id = isset($input['post_id']) ? (int)$input['post_id'] : 0;
    $content = isset($input['content']) ? trim($input['content']) : '';
    $user_id = $_SESSION['user_id'];

    if ($post_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltigt post-ID']);
        exit;
    }

    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Kommentaren kan inte vara tom']);
        exit;
    }

    // Lägg till kommentar
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);
    $comment_id = $pdo->lastInsertId();

    // Uppdatera comments_count
    $stmt = $pdo->prepare("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);

    // Hämta den skapade kommentaren med användarinfo
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.content,
            c.created_at,
            u.username,
            u.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    // Hämta nya comments_count
    $stmt = $pdo->prepare("SELECT comments_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'comment' => $comment,
        'comments_count' => $post['comments_count']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod vid tillägg av kommentar',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
