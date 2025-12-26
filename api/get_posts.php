<?php
// Starta output buffering för att rensa bort oönskad output
ob_start();

session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Rensa output buffer (tar bort alla debug-meddelanden)
ob_end_clean();

// Sätt header för JSON-respons
header('Content-Type: application/json');

// Kontrollera om användaren är inloggad
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Ej inloggad']);
    exit;
}

try {
    // Hämta parametrar från förfrågan
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    // Säkerställ att limit är rimligt
    if ($limit > 50) {
        $limit = 50;
    }

    // Hämta posts med användarinformation
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.content,
            p.image,
            p.likes_count,
            p.comments_count,
            p.created_at,
            u.username,
            u.profile_picture
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $posts = $stmt->fetchAll();

    // Formatera tidsstämplar för bättre läsbarhet
    foreach ($posts as &$post) {
        $post['time_ago'] = time_ago($post['created_at']);
    }

    // Returnera posts som JSON
    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'offset' => $offset,
        'limit' => $limit,
        'count' => count($posts)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ett fel uppstod vid hämtning av inlägg',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}

/**
 * Konvertera tidsstämpel till "tid sedan" format
 */
function time_ago($datetime)
{
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just nu';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minut' . ($minutes > 1 ? 'er' : '') . ' sedan';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' timm' . ($hours > 1 ? 'ar' : 'e') . ' sedan';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' dag' . ($days > 1 ? 'ar' : '') . ' sedan';
    } else {
        return date('Y-m-d H:i', $timestamp);
    }
}
