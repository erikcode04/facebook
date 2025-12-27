// Söka användare och hantera följ-funktioner

let searchTimeout = null;

// Lyssna på input i sökfältet
document.getElementById('search-input').addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length === 0) {
        document.getElementById('search-results').innerHTML = '';
        document.getElementById('no-results').style.display = 'none';
        return;
    }

    // Fördröj sökningen för att undvika för många förfrågningar
    searchTimeout = setTimeout(() => {
        searchUsers(query);
    }, 300);
});

// Tillåt sökning med Enter-tangenten
document.getElementById('search-input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = this.value.trim();
        if (query.length > 0) {
            clearTimeout(searchTimeout);
            searchUsers(query);
        }
    }
});

/**
 * Sök användare via API
 */
async function searchUsers(query = null) {
    if (query === null) {
        query = document.getElementById('search-input').value.trim();
    }

    if (query.length === 0) {
        return;
    }

    const loading = document.getElementById('loading-search');
    const resultsContainer = document.getElementById('search-results');
    const noResults = document.getElementById('no-results');

    loading.style.display = 'block';
    noResults.style.display = 'none';

    try {
        console.log(`Söker efter användare med query: ${query}`);
        console.log(`Använder URL: ${BASE_URL}api/search_users.php?q=${encodeURIComponent(query)}`);
        const response = await fetch(`${BASE_URL}api/search_users.php?q=${encodeURIComponent(query)}`);

        if (!response.ok) {
            throw new Error('Kunde inte söka användare');
        }

        const data = await response.json();

        if (data.success && data.users.length > 0) {
            displaySearchResults(data.users);
        } else {
            resultsContainer.innerHTML = '';
            noResults.style.display = 'block';
        }
    } catch (error) {
        console.error('Fel vid sökning:', error);
        resultsContainer.innerHTML = '<p style="color: red; text-align: center;">Ett fel uppstod vid sökning</p>';
    } finally {
        loading.style.display = 'none';
    }
}

/**
 * Visa sökresultat
 */
function displaySearchResults(users) {
    const resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = '';

    users.forEach(user => {
        const userCard = createUserCard(user);
        resultsContainer.appendChild(userCard);
    });
}

/**
 * Skapa användar-kort
 */
function createUserCard(user) {
    const card = document.createElement('div');
    card.className = 'user-card';
    card.dataset.userId = user.id;

    const profilePicture = user.profile_picture
        ? (BASE_URL + user.profile_picture)
        : (BASE_URL + 'public/images/default-avatar.svg');

    const bio = user.bio ? escapeHtml(user.bio) : 'Ingen beskrivning';

    card.innerHTML = `
        <a href="${BASE_URL}profile.php?id=${user.id}" class="user-card-link">
            <img src="${escapeHtml(profilePicture)}" alt="${escapeHtml(user.username)}" class="user-card-avatar">
            <div class="user-card-info">
                <h3 class="user-card-username">${escapeHtml(user.username)}</h3>
                <p class="user-card-bio">${bio}</p>
                <div class="user-card-stats">
                    <span>${user.followers_count} följare</span>
                    <span>${user.following_count} följer</span>
                </div>
            </div>
        </a>
        <button class="follow-btn-small ${user.is_following ? 'following' : ''}" 
                data-user-id="${user.id}"
                onclick="toggleFollowUser(${user.id}, this, event)">
            ${user.is_following ? 'Följer' : 'Följ'}
        </button>
    `;

    return card;
}

/**
 * Följ/avfölj användare från söksidan
 */
async function toggleFollowUser(userId, button, event) {
    event.preventDefault();
    event.stopPropagation();

    const isFollowing = button.classList.contains('following');
    const action = isFollowing ? 'unfollow' : 'follow';

    try {
        const response = await fetch(BASE_URL + 'api/follow_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                action: action
            })
        });

        const data = await response.json();

        if (data.success) {
            button.classList.toggle('following');
            button.textContent = button.classList.contains('following') ? 'Följer' : 'Följ';

            // Uppdatera följare-räknare i kortet
            const card = button.closest('.user-card');
            const statsSpan = card.querySelector('.user-card-stats span:first-child');
            if (statsSpan) {
                const currentCount = parseInt(statsSpan.textContent.split(' ')[0]);
                const newCount = button.classList.contains('following') ? currentCount + 1 : currentCount - 1;
                statsSpan.textContent = `${newCount} följare`;
            }
        } else {
            alert('Kunde inte uppdatera följ-status: ' + (data.error || 'Okänt fel'));
        }
    } catch (error) {
        console.error('Fel vid följ-åtgärd:', error);
        alert('Kunde inte uppdatera följ-status');
    }
}

/**
 * Escape HTML för säkerhet
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
