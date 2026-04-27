function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Check if placeholder exists before trying to hide it
            const placeholder = document.getElementById('preview-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            
            // Check if image tag exists before updating it
            const img = document.getElementById('preview-img-tag');
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
        }
        reader.readAsDataURL(file);
    }
}