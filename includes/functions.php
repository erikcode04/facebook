<?php
// Allmänna hjälpfunktioner

/**
 * Sanera användarinput
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Kontrollera om användaren är inloggad
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Omdirigera användaren
 */
function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Visa meddelanden
 */
function display_message($message, $type = 'info')
{
    return "<div class='alert alert-{$type}'>{$message}</div>";
}
