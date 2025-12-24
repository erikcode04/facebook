<?php
// Huvudfil för projektet
require_once 'config/database.php';
require_once 'includes/functions.php';

// Starta session
session_start();

// Inkludera header
include 'views/header.php';

// Huvudinnehåll
?>
<main>
    <h1>Välkommen till Facebook-projektet</h1>
    <p>Detta är startsidan för ditt projekt.</p>
</main>

<?php
// Inkludera footer
include 'views/footer.php';
?>