<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kontrollera om användaren är inloggad
if (!is_logged_in()) {
    redirect('login.php');
}

// Hämta användarinformation
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'views/header.php';
?>

<div class="posts-container">
    <h1>Senaste inläggen</h1>

    <!-- Container för inlägg -->
    <div id="posts-feed">
        <!-- Posts kommer laddas här via JavaScript -->
    </div>

    <!-- Laddningsindikator -->
    <div id="loading" class="loading-spinner" style="display: none;">
        <p>Laddar fler inlägg...</p>
    </div>

    <!-- Slutmeddelande -->
    <div id="no-more-posts" style="display: none; text-align: center; padding: 20px; color: #666;">
        <p>Inga fler inlägg att visa</p>
    </div>
</div>

<script>
    // Gör BASE_URL tillgänglig för JavaScript
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>public/js/posts.js?v=<?php echo time(); ?>"></script>

<?php include 'views/footer.php'; ?>