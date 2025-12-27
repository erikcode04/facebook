<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kontrollera om användaren är inloggad
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

include 'views/header.php';
?>

<div class="search-container">
    <h1>Sök användare</h1>

    <div class="search-box">
        <input type="text" id="search-input" placeholder="Sök efter användare..." autocomplete="off">
        <button id="search-btn" onclick="searchUsers()">🔍 Sök</button>
    </div>

    <div id="search-results" class="search-results">
        <!-- Sökresultat kommer att visas här -->
    </div>

    <div id="loading-search" class="loading-spinner" style="display: none;">
        <p>Söker...</p>
    </div>

    <div id="no-results" style="display: none; text-align: center; padding: 40px; color: #666;">
        <p>Inga användare hittades</p>
    </div>
</div>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const currentUserId = <?php echo $user_id; ?>;
</script>
<script src="<?php echo BASE_URL; ?>public/js/search.js?v=<?php echo time(); ?>"></script>

<?php include 'views/footer.php'; ?>