/**
 * Handle the profile picture preview
 * @param {Event} event 
 */
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Hide the placeholder div (initials) if it exists
            const placeholder = document.getElementById('preview-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            
            // Update and show the image tag
            const img = document.getElementById('preview-img-tag');
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Auto-dismiss success alert after 3 seconds
     */
    const successAlert = document.getElementById('successAlert');
    
    if (successAlert) {
        // Auto remove after 3 seconds (3000 milliseconds)
        setTimeout(function() {
            successAlert.remove();
        }, 3000);
    }
    
});
}