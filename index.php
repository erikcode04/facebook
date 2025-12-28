<?php
echo "<!-- DEBUG: index.php laddas -->\n";

// Huvudfil för projektet
echo "<!-- DEBUG: Laddar config/config.php -->\n";
require_once 'config/config.php';
echo "<!-- DEBUG: config.php laddad -->\n";

echo "<!-- DEBUG: Laddar config/database.php -->\n";
require_once 'config/database.php';
echo "<!-- DEBUG: database.php laddad -->\n";

echo "<!-- DEBUG: Laddar includes/functions.php -->\n";
require_once 'includes/functions.php';
echo "<!-- DEBUG: functions.php laddad -->\n";

// Starta session
session_start();
echo "<!-- DEBUG: Session startad -->\n";

// Inkludera header
echo "<!-- DEBUG: Laddar header.php -->\n";
include 'views/header.php';
echo "<!-- DEBUG: header.php laddad -->\n";

// Huvudinnehåll
echo "<!-- DEBUG: Visar huvudinnehåll -->\n";
?>
<main style="text-align: center; padding: 50px;">
    <h1>Sorry for turning in late</h1>
    <img src="public/images/bowing-down-bow-down.gif" alt="Bowing down apologetically" style="max-width: 500px; margin: 20px auto; display: block;">
</main>

<?php
echo "<!-- DEBUG: Laddar footer.php -->\n";
// Inkludera footer
include 'views/footer.php';
echo "<!-- DEBUG: Allt laddat korrekt! -->\n";
?>