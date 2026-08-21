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
});
