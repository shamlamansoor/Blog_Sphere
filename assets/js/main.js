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

    // Markdown Editor Live Preview
    const contentTextarea = document.getElementById('content');
    const previewPane = document.getElementById('preview-pane');

    if (contentTextarea && previewPane && typeof marked !== 'undefined') {
        const updatePreview = () => {
            const rawContent = contentTextarea.value;
            // Use marked library to parse markdown to HTML
            previewPane.innerHTML = marked.parse(rawContent);
        };

        // Initial preview
        updatePreview();

        // Update on input
        contentTextarea.addEventListener('input', updatePreview);
    }

    // Single Post Markdown Rendering
    const postContentElement = document.getElementById('post-content-raw');
    if (postContentElement && typeof marked !== 'undefined') {
        const rawContent = postContentElement.textContent || postContentElement.innerText;
        const renderedHtml = marked.parse(rawContent);
        
        // Hide the raw element and show a new one with rendered HTML
        postContentElement.style.display = 'none';
        
        const renderedElement = document.createElement('div');
        renderedElement.className = 'post-content-rendered markdown-body';
        renderedElement.innerHTML = renderedHtml;
        
        postContentElement.parentNode.insertBefore(renderedElement, postContentElement.nextSibling);
    }
});
