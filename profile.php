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
$view_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $user_id;
$is_own_profile = ($user_id == $view_user_id);

// Hämta användarinformation
$stmt = $pdo->prepare("SELECT id, username, email, profile_picture, bio, created_at FROM users WHERE id = ?");
$stmt->execute([$view_user_id]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    die("Användaren hittades inte.");
}

// Hämta antal inlägg
$stmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM posts WHERE user_id = ?");
$stmt->execute([$view_user_id]);
$post_stats = $stmt->fetch();

// Hantera profiluppdatering
$update_message = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    if (isset($_POST['update_profile'])) {
        $new_bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

        try {
            $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
            $stmt->execute([$new_bio, $user_id]);
            $update_message = 'Profilen har uppdaterats!';

            // Uppdatera lokala data
            $profile_user['bio'] = $new_bio;
        } catch (Exception $e) {
            $update_error = 'Kunde inte uppdatera profilen.';
        }
    }

    // Hantera profilbildsuppladdning
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $upload_dir = 'uploads/profiles/';

            // Skapa mapp om den inte finns
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Ta bort gammal profilbild om den finns
                if ($profile_user['profile_picture'] && file_exists($profile_user['profile_picture'])) {
                    unlink($profile_user['profile_picture']);
                }

                // Uppdatera databasen
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$upload_path, $user_id]);

                $profile_user['profile_picture'] = $upload_path;
                $update_message = 'Profilbilden har uppdaterats!';
            } else {
                $update_error = 'Kunde inte ladda upp bilden.';
            }
        } else {
            $update_error = 'Ogiltig filtyp eller för stor fil (max 5MB).';
        }
    }
}

include 'views/header.php';
?>

<div class="profile-container">
    <?php if ($update_message): ?>
        <div class="success-message"><?php echo htmlspecialchars($update_message); ?></div>
    <?php endif; ?>

    <?php if ($update_error): ?>
        <div class="error-message"><?php echo htmlspecialchars($update_error); ?></div>
    <?php endif; ?>

    <!-- Profilhuvud -->
    <div class="profile-header">
        <div class="profile-cover"></div>
        <div class="profile-info">
            <div class="profile-picture-container">
                <?php if ($profile_user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($profile_user['profile_picture']); ?>"
                        alt="Profilbild" class="profile-picture-large">
                <?php else: ?>
                    <div class="profile-picture-placeholder">
                        <?php echo strtoupper(substr($profile_user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_own_profile): ?>
                    <button class="edit-profile-pic-btn" onclick="document.getElementById('profile-pic-input').click()">
                        📷
                    </button>
                    <form id="profile-pic-form" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="profile-pic-input" name="profile_picture"
                            accept="image/*" onchange="this.form.submit()">
                    </form>
                <?php endif; ?>
            </div>

            <div class="profile-details">
                <h1 class="profile-username"><?php echo htmlspecialchars($profile_user['username']); ?></h1>

                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $post_stats['post_count']; ?></span>
                        <span class="stat-label">Inlägg</span>
                    </div>
                </div>

                <?php if ($is_own_profile): ?>
                    <button class="edit-profile-btn" onclick="toggleEditProfile()">Redigera profil</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bio sektion -->
    <div class="profile-bio-section">
        <?php if ($is_own_profile): ?>
            <div id="bio-display" class="bio-display">
                <h3>Om mig</h3>
                <p><?php echo $profile_user['bio'] ? htmlspecialchars($profile_user['bio']) : 'Ingen beskrivning ännu.'; ?></p>
            </div>

            <div id="bio-edit" class="bio-edit" style="display: none;">
                <form method="POST">
                    <h3>Redigera bio</h3>
                    <textarea name="bio" rows="4" maxlength="500"
                        placeholder="Berätta lite om dig själv..."><?php echo htmlspecialchars($profile_user['bio']); ?></textarea>
                    <div class="bio-actions">
                        <button type="submit" name="update_profile" class="save-btn">Spara</button>
                        <button type="button" class="cancel-btn" onclick="toggleEditProfile()">Avbryt</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bio-display">
                <h3>Om <?php echo htmlspecialchars($profile_user['username']); ?></h3>
                <p><?php echo $profile_user['bio'] ? htmlspecialchars($profile_user['bio']) : 'Ingen beskrivning.'; ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Användarens inlägg -->
    <div class="profile-posts-section">
        <h2>Inlägg från <?php echo $is_own_profile ? 'dig' : htmlspecialchars($profile_user['username']); ?></h2>
        <div id="user-posts-feed">
            <!-- Posts kommer laddas här via JavaScript -->
        </div>

        <div id="loading-user-posts" class="loading-spinner" style="display: none;">
            <p>Laddar inlägg...</p>
        </div>

        <div id="no-user-posts" style="display: none; text-align: center; padding: 40px; color: #666;">
            <p><?php echo $is_own_profile ? 'Du har inga inlägg ännu.' : 'Inga inlägg att visa.'; ?></p>
        </div>
    </div>
</div>

<script>
    const profileUserId = <?php echo $view_user_id; ?>;
    const isOwnProfile = <?php echo $is_own_profile ? 'true' : 'false'; ?>;

    function toggleEditProfile() {
        const bioDisplay = document.getElementById('bio-display');
        const bioEdit = document.getElementById('bio-edit');

        if (bioEdit.style.display === 'none') {
            bioDisplay.style.display = 'none';
            bioEdit.style.display = 'block';
        } else {
            bioDisplay.style.display = 'block';
            bioEdit.style.display = 'none';
        }
    }

    // Dölj meddelanden efter 3 sekunder
    setTimeout(() => {
        const messages = document.querySelectorAll('.success-message, .error-message');
        messages.forEach(msg => msg.style.display = 'none');
    }, 3000);
</script>

<script src="<?php echo BASE_URL; ?>public/js/profile.js"></script>

<?php include 'views/footer.php'; ?>