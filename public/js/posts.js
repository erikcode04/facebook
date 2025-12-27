// Infinite scroll för posts
let offset = 0;
const limit = 10;
let isLoading = false;
let hasMorePosts = true;

// Ladda inlägg när sidan laddas
document.addEventListener('DOMContentLoaded', function () {
    loadPosts();
    setupInfiniteScroll();
});

/**
 * Ladda inlägg från servern
 */
async function loadPosts() {
    if (isLoading || !hasMorePosts) {
        return;
    }

    isLoading = true;
    showLoading(true);

    try {
        const response = await fetch(`api/get_posts.php?offset=${offset}&limit=${limit}`);

        if (!response.ok) {
            throw new Error('Kunde inte hämta inlägg');
        }

        const data = await response.json();

        if (data.success && data.posts.length > 0) {
            displayPosts(data.posts);
            offset += data.posts.length;

            // Om vi fick färre posts än limit, finns det inga fler
            if (data.posts.length < limit) {
                hasMorePosts = false;
                showNoMorePosts();
            }
        } else {
            hasMorePosts = false;
            showNoMorePosts();
        }
    } catch (error) {
        console.error('Fel vid laddning av inlägg:', error);
        showError('Kunde inte ladda inlägg. Försök igen senare.');
    } finally {
        isLoading = false;
        showLoading(false);
    }
}

/**
 * Visa inlägg på sidan
 */
function displayPosts(posts) {
    const feed = document.getElementById('posts-feed');

    posts.forEach(async post => {
        const postElement = await createPostElement(post);
        feed.appendChild(postElement);
    });
}

/**
 * Skapa HTML-element för ett inlägg
 */
async function createPostElement(post) {
    const article = document.createElement('article');
    article.className = 'post';
    article.dataset.postId = post.id;

    // Kontrollera om användaren gillat inlägget
    const isLiked = await checkIfLiked(post.id);

    // Bygg upp HTML för inlägget
    const profilePicture = post.profile_picture
        ? (BASE_URL + post.profile_picture)
        : (BASE_URL + 'public/images/default-avatar.svg');
    const postImage = post.image ? `<img src="${escapeHtml(BASE_URL + post.image)}" alt="Post image" class="post-image">` : '';

    article.innerHTML = `
        <div class="post-header">
            <img src="${escapeHtml(profilePicture)}" alt="${escapeHtml(post.username)}" class="profile-pic">
            <div class="post-info">
                <h3 class="post-author">${escapeHtml(post.username)}</h3>
                <span class="post-time">${escapeHtml(post.time_ago)}</span>
            </div>
        </div>
        <div class="post-content">
            <p>${escapeHtml(post.content)}</p>
            ${postImage}
        </div>
        <div class="post-footer">
            <div class="post-stats">
                <span class="likes-count-text">👍 <span class="count">${post.likes_count}</span></span>
                <span class="comments-count-text">💬 <span class="count">${post.comments_count}</span></span>
            </div>
            <div class="post-actions">
                <button class="like-btn ${isLiked ? 'liked' : ''}" data-post-id="${post.id}">
                    <span class="icon">${isLiked ? '❤️' : '🤍'}</span> Gilla
                </button>
                <button class="comment-btn" data-post-id="${post.id}">
                    <span class="icon">💬</span> Kommentera
                </button>
            </div>
            <div class="comments-section" id="comments-${post.id}" style="display: none;">
                <div class="comments-list"></div>
                <form class="comment-form" data-post-id="${post.id}">
                    <input type="text" placeholder="Skriv en kommentar..." class="comment-input" required>
                    <button type="submit" class="submit-comment-btn">Skicka</button>
                </form>
            </div>
        </div>
    `;

    // Lägg till event listeners
    setupPostEventListeners(article);

    return article;
}

/**
 * Konfigurera infinite scroll
 */
function setupInfiniteScroll() {
    window.addEventListener('scroll', () => {
        // Kontrollera om användaren har scrollat nästan till botten
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;

        // Ladda fler posts när användaren är 200px från botten
        if (scrollPosition >= pageHeight - 200) {
            loadPosts();
        }
    });
}

/**
 * Visa/dölj laddningsindikator
 */
function showLoading(show) {
    const loading = document.getElementById('loading');
    loading.style.display = show ? 'block' : 'none';
}

/**
 * Visa meddelande om att det inte finns fler posts
 */
function showNoMorePosts() {
    const noMorePosts = document.getElementById('no-more-posts');
    noMorePosts.style.display = 'block';
}

/**
 * Visa felmeddelande
 */
function showError(message) {
    const feed = document.getElementById('posts-feed');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    feed.appendChild(errorDiv);
}

/**
 * Escapa HTML för att förhindra XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Konfigurera event listeners för ett inlägg
 */
function setupPostEventListeners(article) {
    const postId = article.dataset.postId;

    // Like-knapp
    const likeBtn = article.querySelector('.like-btn');
    likeBtn.addEventListener('click', () => toggleLike(postId, likeBtn));

    // Kommentar-knapp (visa/dölj kommentarssektion)
    const commentBtn = article.querySelector('.comment-btn');
    commentBtn.addEventListener('click', () => toggleComments(postId));

    // Kommentarformulär
    const commentForm = article.querySelector('.comment-form');
    commentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        submitComment(postId, commentForm);
    });
}

/**
 * Gilla/ogilla ett inlägg
 */
async function toggleLike(postId, button) {
    try {
        const response = await fetch('api/like_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ post_id: postId })
        });

        if (!response.ok) {
            throw new Error('Kunde inte gilla inlägg');
        }

        const data = await response.json();

        if (data.success) {
            // Uppdatera knappen
            const icon = button.querySelector('.icon');
            if (data.liked) {
                button.classList.add('liked');
                icon.textContent = '❤️';
            } else {
                button.classList.remove('liked');
                icon.textContent = '🤍';
            }

            // Uppdatera räknaren
            const article = button.closest('.post');
            const likesCount = article.querySelector('.likes-count-text .count');
            likesCount.textContent = data.likes_count;
        }
    } catch (error) {
        console.error('Fel vid gillning:', error);
        showError('Kunde inte gilla inlägg. Försök igen.');
    }
}

/**
 * Visa/dölj kommentarssektionen
 */
async function toggleComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    const commentsList = commentsSection.querySelector('.comments-list');

    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';

        // Ladda kommentarer om de inte redan är laddade
        if (commentsList.children.length === 0) {
            await loadComments(postId);
        }
    } else {
        commentsSection.style.display = 'none';
    }
}

/**
 * Ladda kommentarer för ett inlägg
 */
async function loadComments(postId) {
    try {
        const response = await fetch(`api/get_comments.php?post_id=${postId}`);

        if (!response.ok) {
            throw new Error('Kunde inte hämta kommentarer');
        }

        const data = await response.json();

        if (data.success) {
            displayComments(postId, data.comments);
        }
    } catch (error) {
        console.error('Fel vid laddning av kommentarer:', error);
    }
}

/**
 * Visa kommentarer för ett inlägg
 */
function displayComments(postId, comments) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    const commentsList = commentsSection.querySelector('.comments-list');

    commentsList.innerHTML = '';

    if (comments.length === 0) {
        commentsList.innerHTML = '<p class="no-comments">Inga kommentarer ännu</p>';
        return;
    }

    comments.forEach(comment => {
        const commentElement = createCommentElement(comment);
        commentsList.appendChild(commentElement);
    });
}

/**
 * Skapa HTML-element för en kommentar
 */
function createCommentElement(comment) {
    const div = document.createElement('div');
    div.className = 'comment';

    const profilePicture = comment.profile_picture
        ? (BASE_URL + comment.profile_picture)
        : (BASE_URL + 'public/images/default-avatar.svg');
    const timestamp = new Date(comment.created_at).toLocaleString('sv-SE');

    div.innerHTML = `
        <img src="${escapeHtml(profilePicture)}" alt="${escapeHtml(comment.username)}" class="comment-profile-pic">
        <div class="comment-content-wrapper">
            <div class="comment-header">
                <span class="comment-author">${escapeHtml(comment.username)}</span>
                <span class="comment-time">${timestamp}</span>
            </div>
            <p class="comment-text">${escapeHtml(comment.content)}</p>
        </div>
    `;

    return div;
}

/**
 * Skicka en kommentar
 */
async function submitComment(postId, form) {
    const input = form.querySelector('.comment-input');
    const content = input.value.trim();

    if (!content) {
        return;
    }

    try {
        const response = await fetch('api/add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                post_id: postId,
                content: content
            })
        });

        if (!response.ok) {
            throw new Error('Kunde inte lägga till kommentar');
        }

        const data = await response.json();

        if (data.success) {
            // Lägg till kommentaren i listan
            const commentsSection = document.getElementById(`comments-${postId}`);
            const commentsList = commentsSection.querySelector('.comments-list');

            // Ta bort "inga kommentarer" meddelandet om det finns
            const noComments = commentsList.querySelector('.no-comments');
            if (noComments) {
                noComments.remove();
            }

            const commentElement = createCommentElement(data.comment);
            commentsList.appendChild(commentElement);

            // Uppdatera räknaren
            const article = form.closest('.post');
            const commentsCount = article.querySelector('.comments-count-text .count');
            commentsCount.textContent = data.comments_count;

            // Rensa formuläret
            input.value = '';
        }
    } catch (error) {
        console.error('Fel vid tillägg av kommentar:', error);
        showError('Kunde inte lägga till kommentar. Försök igen.');
    }
}

/**
 * Kontrollera om användaren gillat ett inlägg
 */
async function checkIfLiked(postId) {
    try {
        const response = await fetch(`api/check_like.php?post_id=${postId}`);

        if (!response.ok) {
            return false;
        }

        const data = await response.json();
        return data.success && data.liked;
    } catch (error) {
        console.error('Fel vid kontroll av like:', error);
        return false;
    }
}
