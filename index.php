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
<main>
    <h1>Välkommen till Facebook-projektet</h1>
    <p>Detta är startsidan för ditt projekt.</p>
    <p><strong>DEBUG:</strong> Om du ser detta meddelande fungerar sidan!</p>
</main>

<?php
echo "<!-- DEBUG: Laddar footer.php -->\n";
// Inkludera footer
include 'views/footer.php';
echo "<!-- DEBUG: Allt laddat korrekt! -->\n";
?>