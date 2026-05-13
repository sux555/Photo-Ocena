document.addEventListener('DOMContentLoaded', function() {
    const initLikeSystem = () => {
        const feed = document.querySelector('.feed');
        if (!feed) return;

        feed.addEventListener('click', async function(e) {
            const btn = e.target.closest('.btn-like, .btn-dislike');
            if (!btn) return;

            const card = btn.closest('.card');
            const photoId = btn.dataset.photoId;
            const isLikeBtn = btn.classList.contains('btn-like');
            const actionType = isLikeBtn ? 'like' : 'dislike';

            const likesCountEl = card.querySelector('.likes-count');
            const dislikesCountEl = card.querySelector('.dislikes-count');
            
            const oldLikes = parseInt(likesCountEl.textContent) || 0;
            const oldDislikes = parseInt(dislikesCountEl.textContent) || 0;

            btn.style.transform = 'scale(1.3)';
            btn.style.transition = 'transform 0.2s';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);

            try {
                const response = await fetch('api/like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ photo_id: photoId, type: actionType })
                });

                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (jsonErr) {
                    console.error(text);
                    throw new Error('Błąd formatu danych serwera.');
                }

                if (data.success) {
                    likesCountEl.textContent = data.likes;
                    dislikesCountEl.textContent = data.dislikes;
                    
                    btn.style.boxShadow = '0 0 15px var(--accent-color)';
                    setTimeout(() => btn.style.boxShadow = 'none', 500);
                } else {
                    alert(data.message || 'Wystąpił błąd.');
                }

            } catch (err) {
                console.error(err);
                alert('Błąd połączenia: ' + err.message);
                
                btn.style.transform = 'scale(1)';
            }
        });
    };

    initLikeSystem();
});