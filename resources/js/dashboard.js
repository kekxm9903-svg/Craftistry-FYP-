// Navigation Active State - FIXED VERSION
// Only prevent default for placeholder links (#)
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', function(e) {
    // Only prevent default for links with href="#" (placeholder links)
    if (this.getAttribute('href') === '#') {
      e.preventDefault();
    }
    // Real links (like /studio, /dashboard) will navigate normally
  });
});

// Action Card Click Handler
document.querySelectorAll('.action-card').forEach(card => {
  card.addEventListener('click', () => {
    const title = card.querySelector('h3').textContent;
    console.log('Navigating to:', title);
    // Add navigation logic here when features are ready
  });
});

// Card Link Click (prevent card click when clicking link)
document.querySelectorAll('.card-link').forEach(link => {
  link.addEventListener('click', (e) => {
    e.stopPropagation();
  });
});