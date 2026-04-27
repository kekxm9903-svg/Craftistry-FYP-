// Profile Picture Preview
document.getElementById('profile_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('profilePreview');
            const placeholder = document.getElementById('profilePreviewPlaceholder');
            
            preview.src = event.target.result;
            preview.style.display = 'block';
            
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
        
        // Reset remove flag when new image is selected
        document.getElementById('remove_profile_image').value = '0';
    }
});

// Remove Profile Picture
function removeProfilePicture() {
    const preview = document.getElementById('profilePreview');
    const placeholder = document.getElementById('profilePreviewPlaceholder');
    const fileInput = document.getElementById('profile_image');
    const removeInput = document.getElementById('remove_profile_image');
    
    // Clear file input
    fileInput.value = '';
    
    // Set remove flag
    removeInput.value = '1';
    
    // Hide preview, show placeholder
    if (preview) {
        preview.style.display = 'none';
        preview.src = '';
    }
    
    if (placeholder) {
        placeholder.style.display = 'flex';
    } else {
        // Create placeholder if it doesn't exist
        const placeholderDiv = document.createElement('div');
        placeholderDiv.className = 'profile-preview-placeholder';
        placeholderDiv.id = 'profilePreviewPlaceholder';
        placeholderDiv.textContent = document.getElementById('fullname')?.value.charAt(0).toUpperCase() || 'U';
        preview.parentNode.appendChild(placeholderDiv);
    }
}

// Bio character counter
const bioTextarea = document.getElementById('bio');
const bioCount = document.getElementById('bioCount');

if (bioTextarea && bioCount) {
    bioTextarea.addEventListener('input', function() {
        bioCount.textContent = this.value.length;
    });
}

// Success notification auto-hide
const successNotification = document.getElementById('successNotification');
if (successNotification) {
    setTimeout(() => {
        successNotification.classList.add('slide-out');
        setTimeout(() => {
            successNotification.remove();
        }, 300);
    }, 3000);
}

// Form validation
document.querySelector('.edit-form')?.addEventListener('submit', function(e) {
    const artworkTypes = document.querySelectorAll('input[name="artwork_types[]"]:checked');
    
    if (artworkTypes.length === 0) {
        e.preventDefault();
        alert('Please select at least one artwork type');
        return false;
    }
});