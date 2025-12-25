<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Om användaren redan är inloggad, omdirigera till startsidan
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Hantera registrering
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validering
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vänligen fyll i alla fält';
    } elseif (strlen($username) < 3) {
        $error = 'Användarnamnet måste vara minst 3 tecken';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress';
    } elseif (strlen($password) < 6) {
        $error = 'Lösenordet måste vara minst 6 tecken';
    } elseif ($password !== $confirm_password) {
        $error = 'Lösenorden matchar inte';
    } else {
        // Kontrollera om användarnamn eller e-post redan finns
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Användarnamnet eller e-postadressen finns redan';
        } else {
            // Skapa ny användare
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");

            try {
                $stmt->execute([$username, $email, $hashed_password]);
                $success = 'Kontot har skapats! Logga in för att fortsätta.';

                // Logga in användaren automatiskt
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;

                // Omdirigera efter 2 sekunder
                header("refresh:2;url=" . BASE_URL . "index.php");
            } catch (PDOException $e) {
                $error = 'Ett fel uppstod. Försök igen senare.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skapa konto - Facebook-projekt</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box signup-box">
            <div class="auth-logo">
                <svg viewBox="0 0 24 24" width="40" height="40">
                    <path fill="#1DA1F2" d="M23.643 4.937c-.835.37-1.732.62-2.675.733.962-.576 1.7-1.49 2.048-2.578-.9.534-1.897.922-2.958 1.13-.85-.904-2.06-1.47-3.4-1.47-2.572 0-4.658 2.086-4.658 4.66 0 .364.042.718.12 1.06-3.873-.195-7.304-2.05-9.602-4.868-.4.69-.63 1.49-.63 2.342 0 1.616.823 3.043 2.072 3.878-.764-.025-1.482-.234-2.11-.583v.06c0 2.257 1.605 4.14 3.737 4.568-.392.106-.803.162-1.227.162-.3 0-.593-.028-.877-.082.593 1.85 2.313 3.198 4.352 3.234-1.595 1.25-3.604 1.995-5.786 1.995-.376 0-.747-.022-1.112-.065 2.062 1.323 4.51 2.093 7.14 2.093 8.57 0 13.255-7.098 13.255-13.254 0-.2-.005-.402-.014-.602.91-.658 1.7-1.477 2.323-2.41z"></path>
                </svg>
            </div>

            <h1>Skapa ditt konto</h1>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <input
                        type="text"
                        name="username"
                        placeholder="Användarnamn"
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="E-postadress"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Lösenord"
                        required>
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Bekräfta lösenord"
                        required>
                </div>

                <button type="submit" class="btn-primary">Skapa konto</button>
            </form>

            <div class="auth-divider">
                <span>eller</span>
            </div>

            <a href="login.php" class="btn-secondary">Har redan ett konto? Logga in</a>
        </div>
    </div>
</body>

</html>