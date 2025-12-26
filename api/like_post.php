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
    $user_id = $_SESSION['user_id'];

    if ($post_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ogiltigt post-ID']);
        exit;
    }

    // Kontrollera om användaren redan gillat inlägget
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        // Ta bort gillning
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        // Uppdatera likes_count
        $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        $liked = false;
    } else {
        // Lägg till gillning
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);

        // Uppdatera likes_count
        $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        $liked = true;
    }

    // Hämta nya likes_count
    $stmt = $pdo->prepare("SELECT likes_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $post['likes_count']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
