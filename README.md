# Facebook-projekt

Ett skolprojekt som skapar en Facebook-liknande applikation med PHP.

## Mappstruktur

```
facebook/
├── config/          # Konfigurationsfiler (databas, inställningar)
├── controllers/     # Controllers för affärslogik
├── models/          # Databasmodeller
├── views/           # HTML/PHP-vyer
├── includes/        # Hjälpfiler och funktioner
├── public/          # Publika filer
│   ├── css/        # Stilmallar
│   ├── js/         # JavaScript-filer
│   └── images/     # Bilder
├── src/            # Källkodsfiler
├── uploads/        # Uppladdade filer
└── index.php       # Huvudfil
```

## Installation

1. Klona projektet till din XAMPP htdocs-mapp
2. Skapa en databas som heter `facebook_project` i phpMyAdmin
3. Uppdatera databasinställningarna i `config/database.php`
4. Öppna `http://localhost/facebook/` i din webbläsare

## Funktioner

- Användarregistrering och inloggning
- Profilhantering
- Inlägg och kommentarer
- Vänförfrågningar

## Teknologier

- PHP 7.4+
- MySQL/MariaDB
- HTML5/CSS3
- JavaScript
