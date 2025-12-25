<?php
// Allmänna konfigurationsinställningar
echo "<!-- DEBUG: config.php körs -->\n";

// Webbplatsens URL
define('BASE_URL', 'http://localhost/facebook/');

// Tidszon
date_default_timezone_set('Europe/Stockholm');

// Felhantering (sätt till false i produktion)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    echo "<!-- DEBUG: Debug mode är PÅ -->\n";
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
