-- Lägg till testdata för posts
-- OBS: Kör detta efter att du har skapat användare i systemet

-- Exempel på hur man lägger till testposts
-- Ersätt user_id med faktiska användare från din users-tabell

-- Kontrollera befintliga användare
-- SELECT id, username FROM users;

-- Lägg till testposts (ersätt user_id = 1 med din faktiska användare)
INSERT INTO posts (user_id, content, likes_count, comments_count, created_at) VALUES
(1, 'Hej! Detta är mitt första inlägg på denna plattform. Ser fram emot att dela mer med er alla!', 5, 2, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'Precis kommit hem från en fantastisk promenad. Vädret är verkligen underbart idag! 🌞', 12, 4, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'Någon annan som älskar kaffe lika mycket som jag? ☕', 8, 6, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1, 'Jobbar på ett nytt projekt som jag är riktigt exalterad över! Mer info kommer snart...', 15, 3, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(1, 'God morgon alla! Vad har ni för planer för helgen?', 20, 8, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(1, 'Rekommendationer på bra böcker? Letar efter något nytt att läsa 📚', 6, 12, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(1, 'Försöker äta mer hälsosamt. Tips på enkla recept uppskattas!', 10, 5, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'Fredagsmys! Vad ska ni se på ikväll? 🍿', 18, 7, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'Tack för alla fina kommentarer på mitt senaste inlägg! Ni är bäst! ❤️', 25, 9, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'Produktivitet på topp idag! Äntligen kommit ikapp med allt jobb 💪', 14, 4, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 'Musikrekommendationer? Behöver uppdatera min spellista!', 11, 15, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'Fantastisk solnedgång igår kväll. Naturen är verkligen vacker! 🌅', 30, 6, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 'Någon som vill spela lite online senare ikväll?', 7, 10, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 'Tänkte på en rolig grej häromdagen... Hur ofta tänker ni på romartiden? 🏛️', 22, 18, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 'Tips på bra träningspass för nybörjare?', 16, 11, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(1, 'Imorgon är det måndag igen... men positivt tänkande! Ny vecka, nya möjligheter!', 19, 8, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 'Älskar när det blir varmare ute. Snart dags för utomhusaktiviteter! ☀️', 13, 5, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(1, 'Någon som har tips på bra poddar att lyssna på?', 9, 20, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, 'Trevlig tisdag allesammans! Hoppas ni har en fantastisk dag!', 21, 7, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(1, 'Funderar på att börja med yoga. Har ni några tips för nybörjare? 🧘', 17, 13, DATE_SUB(NOW(), INTERVAL 14 DAY));

-- Om du har flera användare kan du lägga till fler posts
-- INSERT INTO posts (user_id, content, likes_count, comments_count, created_at) VALUES
-- (2, 'Innehåll från användare 2...', 10, 5, DATE_SUB(NOW(), INTERVAL 2 HOUR));
