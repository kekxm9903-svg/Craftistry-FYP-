// Character counter for bio textarea
const bioTextarea = document.getElementById('bio');
const bioCount = document.getElementById('bioCount');

if (bioTextarea && bioCount) {
    bioTextarea.addEventListener('input', function() {
        const length = this.value.length;
        bioCount.textContent = length + ' / 1000 characters';
        
        if (length > 1000) {
            bioCount.style.color = '#ff4757';
        } else {
            bioCount.style.color = '#999';
        }
    });
    
    // Initialize counter on page load
    bioTextarea.dispatchEvent(new Event('input'));
}

// Navigation - Only prevent default for placeholder links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.getAttribute('href') === '#') {
            e.preventDefault();
        }
    });
});