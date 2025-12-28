# Facebook-projekt

Ett skolprojekt som skapar en Facebook-liknande applikation med PHP och MySQL.

## Förutsättningar

Innan du börjar, se till att du har följande installerat:

- **XAMPP** (eller liknande med Apache och MySQL)
  - PHP 7.4 eller senare
  - MySQL/MariaDB
- En webbläsare (Chrome, Firefox, Edge, etc.)
- En kod-editor (VS Code, Sublime Text, etc.)

## Installation steg-för-steg

### 1. Ladda ner projektet

**Alternativ A: Med Git**
```bash
cd C:\xampp\htdocs
git clone <din-repo-url> facebook
```

**Alternativ B: Manuell nedladdning**
- Ladda ner projektet som ZIP
- Packa upp i `C:\xampp\htdocs\facebook`

### 2. Starta XAMPP

1. Öppna **XAMPP Control Panel**
2. Starta **Apache** (klicka på "Start")
3. Starta **MySQL** (klicka på "Start")
4. Kontrollera att båda visar "Running" (grönt)

### 3. Skapa databasen

**Alternativ A: Via phpMyAdmin (rekommenderas)**

1. Öppna din webbläsare och gå till: `http://localhost/phpmyadmin`
2. Klicka på **"SQL"** i toppmenyn
3. Kopiera hela innehållet från filen `database.sql`
4. Klistra in i SQL-fönstret
5. Klicka på **"Kör"** (eller "Go")
6. Databasen `facebook_project` och alla tabeller är nu skapade! ✅

**Alternativ B: Via kommandoraden**
```bash
cd C:\xampp\htdocs\facebook
mysql -u root -p < database.sql
```
(Tryck bara Enter när den frågar efter lösenord, det är tomt som standard)

### 4. Kontrollera databaskonfigurationen

Öppna filen `config/database.php` och kontrollera att inställningarna stämmer:

```php
define('DB_HOST', 'localhost');      // Borde vara rätt
define('DB_USER', 'root');           // Borde vara rätt
define('DB_PASS', '');               // Tomt lösenord (standard i XAMPP)
define('DB_NAME', 'facebook_project'); // Måste matcha databasen du skapade
```

**OBS:** Om du har ändrat lösenordet för MySQL i XAMPP, uppdatera `DB_PASS`.

### 5. Öppna projektet i webbläsaren

Gå till: `http://localhost/facebook/`

Du borde nu se startsidan med en GIF och texten "Sorry for turning in late" 🎉

### 6. Testa funktionaliteten

1. **Skapa ett konto:**
   - Gå till `http://localhost/facebook/register.php`
   - Fyll i användarnamn, email och lösenord
   - Klicka på "Skapa nytt konto"

2. **Logga in:**
   - Du blir automatiskt inloggad efter registrering
   - Eller gå till `http://localhost/facebook/login.php`

3. **Testa funktioner:**
   - Skapa inlägg
   - Kommentera
   - Gilla inlägg
   - Sök efter användare
   - Följ andra användare

## Mappstruktur

```
facebook/
├── api/                # API-endpoints för AJAX-anrop
│   ├── add_comment.php
│   ├── like_post.php
│   ├── search_users.php
│   └── ...
├── config/             # Konfigurationsfiler
│   ├── config.php      # Allmänna inställningar
│   └── database.php    # Databaskoppling
├── controllers/        # Controllers för affärslogik
├── includes/           # Hjälpfiler och funktioner
│   └── functions.php   # Gemensamma funktioner
├── models/             # Databasmodeller
├── public/             # Publika filer
│   ├── css/           # Stilmallar
│   ├── js/            # JavaScript-filer
│   └── images/        # Bilder och GIF:ar
├── src/               # Källkodsfiler
├── uploads/           # Uppladdade filer (profilbilder, etc.)
│   └── profiles/
├── views/             # HTML/PHP-vyer
│   ├── header.php
│   └── footer.php
├── database.sql       # SQL-fil för att skapa databas
├── index.php          # Startsida
├── login.php          # Inloggningssida
├── register.php       # Registreringssida
├── posts.php          # Inläggssida
├── profile.php        # Profilsida
└── search.php         # Söksida
```

## Databasstruktur

Projektet använder följande tabeller:

- **users** - Användare (id, username, email, password, profile_picture, bio)
- **posts** - Inlägg (id, user_id, content, image, likes_count, comments_count)
- **comments** - Kommentarer (id, post_id, user_id, content)
- **likes** - Gillningar (id, post_id, user_id)
- **follows** - Följningar (id, follower_id, following_id)

## Funktioner

✅ **Autentisering:**
- Användarregistrering med validering
- Säker inloggning med bcrypt-hashade lösenord
- Session-baserad autentisering
- Utloggning

✅ **Sociala funktioner:**
- Skapa och visa inlägg
- Gilla inlägg
- Kommentera på inlägg
- Följ andra användare
- Sök efter användare

✅ **Profil:**
- Visa användarprofil
- Redigera profil och biografi
- Ladda upp profilbild

## Teknologier

- **Backend:** PHP 7.4+ med PDO
- **Databas:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Serverpaket:** XAMPP (Apache + MySQL)
- **Säkerhet:** Prepared statements, password hashing, input sanering

## Felsökning

### Problem: "Databasanslutning misslyckades"

**Lösningar:**
1. Kontrollera att MySQL är igång i XAMPP
2. Kolla att databasen `facebook_project` finns i phpMyAdmin
3. Verifiera användarnamn/lösenord i `config/database.php`

### Problem: "Access denied for user 'root'@'localhost'"

**Lösning:** Ditt MySQL-lösenord är inte tomt. Uppdatera `DB_PASS` i `config/database.php`

### Problem: Sidan visar bara blank skärm

**Lösningar:**
1. Kontrollera att Apache är igång i XAMPP
2. Se PHP-felloggen i `C:\xampp\apache\logs\error.log`
3. Aktivera felmeddelanden i `config/config.php` (sätt `DEBUG_MODE` till `true`)

### Problem: CSS/bilder laddas inte

**Lösning:** Kontrollera att `BASE_URL` i `config/config.php` är korrekt:
```php
define('BASE_URL', 'http://localhost/facebook/');
```

## Säkerhetsinformation

⚠️ **OBS:** Detta är ett skolprojekt för lokal utveckling. Följande gäller:

- Tomt databas-lösenord är **ENDAST OK** för lokal utveckling
- I produktion: Använd starka lösenord och hårdkoda aldrig känsliga uppgifter
- Filen `uploads/` borde ha bättre validering av filtyper
- HTTPS borde användas i produktion

## Support

Om du stöter på problem:
1. Kolla felsökningssektionen ovan
2. Se PHP-felloggen
3. Kontrollera att alla förutsättningar är uppfyllda
4. Verifiera att alla SQL-tabeller skapades korrekt

## Licens

Detta är ett skolprojekt för utbildningssyfte.
