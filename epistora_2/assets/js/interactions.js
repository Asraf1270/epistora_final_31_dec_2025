// /assets/js/interactions.js
// AJAX Interaction Handler

document.addEventListener('DOMContentLoaded', () => {
    // Auto-track view when post card enters viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const postId = entry.target.dataset.postId;
                fetch('/actions/api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=view&post_id=' + postId + '&csrf_token=' + csrfToken
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.post-card').forEach(card => {
        observer.observe(card);
    });

    // Like button
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const postId = btn.closest('.post-card').dataset.postId;
            const res = await fetch('/actions/api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=like&post_id=' + postId + '&csrf_token=' + csrfToken
            });
            const data = await res.json();
            if (data.success) {
                btn.querySelector('.like-count').textContent = data.count;
                btn.style.fontWeight = data.liked ? 'bold' : 'normal';
            }
        });
    });

    // Follow button
    document.querySelectorAll('.follow-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const targetId = btn.dataset.userId;
            const res = await fetch('/actions/api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=follow&target_user_id=' + targetId + '&csrf_token=' + csrfToken
            });
            const data = await res.json();
            if (data.success) {
                btn.textContent = data.following ? 'Following ✓' : 'Follow';
                btn.style.background = data.following ? '#e0e0e0' : '';
            }
        });
    });
});

// Global CSRF token (inject in <head> or from hidden input)
const csrfToken = document.querySelector('meta[name="csrf"]')?.content || 
                  document.querySelector('input[name="csrf_token"]')?.value || '';