// Studio.js - Clean version with NO navigation interference

console.log('Studio JS loaded');

// Character counter for bio textarea
const bioTextarea = document.getElementById('bio');
const bioCount = document.getElementById('bioCount');

if (bioTextarea && bioCount) {
    bioTextarea.addEventListener('input', function() {
        const length = this.value.length;
        bioCount.textContent = length + ' / 1000 characters';
        
        if (length > 1000) {
            bioCount.style.color = '#ff4757';
        } else if (length < 50) {
            bioCount.style.color = '#f97316';
        } else {
            bioCount.style.color = '#10b981';
        }
    });
    
    // Initialize counter on page load
    bioTextarea.dispatchEvent(new Event('input'));
}

// Form validation before submit
const artistForm = document.querySelector('.artist-form');
if (artistForm) {
    artistForm.addEventListener('submit', function(e) {
        // Check if at least one artwork type is selected
        const checkboxes = document.querySelectorAll('input[name="artwork_types[]"]');
        const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        
        if (!isChecked) {
            e.preventDefault();
            alert('Please select at least one artwork type.');
            return false;
        }
        
        // Check bio length
        const bio = document.getElementById('bio');
        if (bio && bio.value.length < 50) {
            e.preventDefault();
            alert('Your bio must be at least 50 characters long.');
            bio.focus();
            return false;
        }
        
        // Check terms agreement
        const terms = document.getElementById('terms');
        if (terms && !terms.checked) {
            e.preventDefault();
            alert('Please agree to the Artist Terms & Conditions.');
            return false;
        }
    });
}