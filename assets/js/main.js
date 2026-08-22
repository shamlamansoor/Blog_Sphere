document.addEventListener('DOMContentLoaded', () => {
    // Delete Confirmation
    const deleteForms = document.querySelectorAll('.form-delete');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Safe Markdown Rendering Function
    const renderMarkdownSafely = (rawContent) => {
        if (typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined') {
            const rawHtml = marked.parse(rawContent);
            return DOMPurify.sanitize(rawHtml);
        }
        return "Error: Markdown parser or Sanitizer not loaded.";
    };

    // Markdown Editor Live Preview
    const contentTextarea = document.getElementById('content');
    const previewPane = document.getElementById('preview-pane');

    if (contentTextarea && previewPane) {
        const updatePreview = () => {
            previewPane.innerHTML = renderMarkdownSafely(contentTextarea.value);
        };

        // Initial preview
        updatePreview();

        // Update on input
        contentTextarea.addEventListener('input', updatePreview);
    }

    // Single Post Markdown Rendering
    const postContentElement = document.getElementById('post-content-raw');
    if (postContentElement) {
        const rawContent = postContentElement.textContent || postContentElement.innerText;
        const renderedHtml = renderMarkdownSafely(rawContent);
        
        // Hide the raw element and show a new one with rendered HTML
        postContentElement.style.display = 'none';
        
        const renderedElement = document.createElement('div');
        renderedElement.className = 'post-content-rendered markdown-body';
        renderedElement.innerHTML = renderedHtml;
        
        postContentElement.parentNode.insertBefore(renderedElement, postContentElement.nextSibling);
    }

    // Tab functionality for profile
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-tab');
                
                // Remove active from all
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active to current
                btn.classList.add('active');
                document.getElementById(targetId).classList.add('active');
            });
        });
    }

    // Like functionality (Generic Event Delegation)
    const handleLike = (btn, url, dataKey, id, isPost = false) => {
        const payload = {};
        payload[dataKey] = id;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrfToken) {
            payload['csrf_token'] = csrfToken;
        }

        // Disable button to prevent double-clicks
        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'liked') {
                    btn.classList.add('liked');
                    if (isPost) {
                        btn.innerHTML = `❤️ Liked <span class="like-count" style="margin-left: 4px;">${data.like_count}</span>`;
                    } else {
                        btn.innerHTML = `❤️ <span class="like-count">${data.like_count}</span>`;
                    }
                } else {
                    btn.classList.remove('liked');
                    if (isPost) {
                        btn.innerHTML = `♡ Like <span class="like-count" style="margin-left: 4px;">${data.like_count}</span>`;
                    } else {
                        btn.innerHTML = `♡ <span class="like-count">${data.like_count}</span>`;
                    }
                }
            } else {
                if (data.error === 'Not logged in') {
                    alert('Please login to like this.');
                    window.location.href = 'login.php';
                } else {
                    alert(data.error || 'An error occurred.');
                }
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            btn.disabled = false;
        });
    };

    // Global click listener for event delegation
    document.addEventListener('click', (e) => {
        const btnLike = e.target.closest('.btn-like');
        
        if (btnLike) {
            e.preventDefault();
            
            if (btnLike.disabled) return; // Prevent if already processing
            
            const id = btnLike.getAttribute('data-id');
            
            if (btnLike.id === 'btn-like-post' || btnLike.classList.contains('btn-like-index')) {
                const isPostText = btnLike.id === 'btn-like-post';
                handleLike(btnLike, 'api/like_post.php', 'post_id', id, isPostText);
            } else if (btnLike.classList.contains('btn-like-comment')) {
                handleLike(btnLike, 'api/like_comment.php', 'comment_id', id, false);
            } else if (btnLike.classList.contains('btn-like-question')) {
                handleLike(btnLike, 'api/like_question.php', 'question_id', id, false);
            }
        }
    });

    // Inline edit toggle
    document.querySelectorAll('.btn-toggle-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            const displayEl = document.getElementById(targetId.replace('form-', 'display-'));
            
            if (targetEl.style.display === 'none') {
                targetEl.style.display = 'block';
                displayEl.style.display = 'none';
            } else {
                targetEl.style.display = 'none';
                displayEl.style.display = 'block';
            }
        });
    });
});

