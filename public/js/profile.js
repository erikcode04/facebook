// Profil-sida funktionalitet
let offset = 0;
const limit = 10;
let isLoading = false;
let hasMorePosts = true;

// Ladda användarens inlägg när sidan laddas
document.addEventListener('DOMContentLoaded', function () {
    loadUserPosts();
    setupInfiniteScroll();
});

/**
 * Ladda användarens inlägg
 */
async function loadUserPosts() {
    if (isLoading || !hasMorePosts) {
        return;
    }

    isLoading = true;
    showLoading(true);

    try {
        const response = await fetch(`api/get_user_posts.php?user_id=${profileUserId}&offset=${offset}&limit=${limit}`);

        if (!response.ok) {
            throw new Error('Kunde inte hämta inlägg');
        }

        const data = await response.json();

        if (data.success && data.posts.length > 0) {
            await displayUserPosts(data.posts);
            offset += data.posts.length;

            // Om vi fick färre posts än limit, finns det inga fler
            if (data.posts.length < limit) {
                hasMorePosts = false;
            }
        } else {
            hasMorePosts = false;
            showNoPosts();
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
 * Visa användarens inlägg
 */
async function displayUserPosts(posts) {
    const feed = document.getElementById('user-posts-feed');

    for (const post of posts) {
        const postElement = await createPostElement(post);
        feed.appendChild(postElement);
    }
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
    const profilePicture = post.profile_picture || 'public/images/default-avatar.png';
    const postImage = post.image ? `<img src="${escapeHtml(post.image)}" alt="Post image" class="post-image">` : '';

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
 * Konfigurera event listeners för ett inlägg
 */
function setupPostEventListeners(article) {
    const postId = article.dataset.postId;

    // Like-knapp
    const likeBtn = article.querySelector('.like-btn');
    likeBtn.addEventListener('click', () => toggleLike(postId, likeBtn));

    // Kommentar-knapp
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
            const icon = button.querySelector('.icon');
            if (data.liked) {
                button.classList.add('liked');
                icon.textContent = '❤️';
            } else {
                button.classList.remove('liked');
                icon.textContent = '🤍';
            }

            const article = button.closest('.post');
            const likesCount = article.querySelector('.likes-count-text .count');
            likesCount.textContent = data.likes_count;
        }
    } catch (error) {
        console.error('Fel vid gillning:', error);
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
 * Visa kommentarer
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

    const profilePicture = comment.profile_picture || 'public/images/default-avatar.png';
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
            const commentsSection = document.getElementById(`comments-${postId}`);
            const commentsList = commentsSection.querySelector('.comments-list');

            const noComments = commentsList.querySelector('.no-comments');
            if (noComments) {
                noComments.remove();
            }

            const commentElement = createCommentElement(data.comment);
            commentsList.appendChild(commentElement);

            const article = form.closest('.post');
            const commentsCount = article.querySelector('.comments-count-text .count');
            commentsCount.textContent = data.comments_count;

            input.value = '';
        }
    } catch (error) {
        console.error('Fel vid tillägg av kommentar:', error);
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

/**
 * Konfigurera infinite scroll
 */
function setupInfiniteScroll() {
    window.addEventListener('scroll', () => {
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;

        if (scrollPosition >= pageHeight - 200) {
            loadUserPosts();
        }
    });
}

/**
 * Visa/dölj laddningsindikator
 */
function showLoading(show) {
    const loading = document.getElementById('loading-user-posts');
    if (loading) {
        loading.style.display = show ? 'block' : 'none';
    }
}

/**
 * Visa meddelande om inga posts
 */
function showNoPosts() {
    const noPosts = document.getElementById('no-user-posts');
    if (noPosts && offset === 0) {
        noPosts.style.display = 'block';
    }
}

/**
 * Visa felmeddelande
 */
function showError(message) {
    const feed = document.getElementById('user-posts-feed');
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
