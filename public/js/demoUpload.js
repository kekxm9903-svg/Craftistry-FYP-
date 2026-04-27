// ========================================
// DEMO ARTWORK UPLOAD & EDIT FUNCTIONS
// ========================================

// Character counter for description
const demoDescTextarea = document.getElementById('demoDescription');
const demoDescCount = document.getElementById('descCount');

if (demoDescTextarea && demoDescCount) {
    demoDescTextarea.addEventListener('input', function() {
        const length = this.value.length;
        demoDescCount.textContent = `${length} / 1000 characters`;
    });
}

// Image preview for upload
const demoImageInput = document.getElementById('demoImage');
const imageUploadArea = document.getElementById('imageUploadArea');
const imagePreview = document.getElementById('imagePreview');
const previewImage = document.getElementById('previewImage');

if (demoImageInput) {
    demoImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must not exceed 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPEG, JPG, PNG, GIF, and WEBP images are allowed');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                imagePreview.style.display = 'block';
                const placeholder = imageUploadArea.querySelector('.upload-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeImage() {
    if (demoImageInput) {
        demoImageInput.value = '';
    }
    if (imagePreview) {
        imagePreview.style.display = 'none';
    }
    const placeholder = imageUploadArea ? imageUploadArea.querySelector('.upload-placeholder') : null;
    if (placeholder) {
        placeholder.style.display = 'block';
    }
}

// Toggle Demo Sell Fields
function toggleDemoSellFields() {
    const checkbox = document.getElementById('alsoSellCheckbox');
    const sellFields = document.getElementById('demoSellFields');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (checkbox && sellFields && uploadBtn) {
        if (checkbox.checked) {
            sellFields.style.display = 'block';
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Demo & List for Sale';
            
            // Make sell fields required
            document.querySelectorAll('.sell-req').forEach(input => {
                input.required = true;
            });
        } else {
            sellFields.style.display = 'none';
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Demo';
            
            // Make sell fields optional
            document.querySelectorAll('.sell-req').forEach(input => {
                input.required = false;
            });
        }
    }
}

// Open/Close Upload Modal
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Reset form and hide sell fields
    const form = document.getElementById('uploadForm');
    if (form) {
        form.reset();
    }
    removeImage();
    
    const sellFields = document.getElementById('demoSellFields');
    if (sellFields) {
        sellFields.style.display = 'none';
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    const form = document.getElementById('uploadForm');
    if (form) {
        form.reset();
    }
    
    removeImage();
    
    if (demoDescCount) {
        demoDescCount.textContent = '0 / 1000 characters';
    }
}

// Upload Form Submit
const uploadForm = document.getElementById('uploadForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('uploadBtn');
        
        console.log('Submitting demo upload form...');
        
        if (uploadBtn) {
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        }
        
        try {
            const response = await fetch('/artist/demo/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            console.log('Upload Response:', data);
            
            if (data.success) {
                closeUploadModal();
                showSuccessPopup('Demo Upload Successful!');
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Upload failed. Please try again.');
                console.error('Upload error:', data);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Demo';
            }
        }
    });
}

// ========================================
// EDIT DEMO FUNCTIONS
// ========================================

// Edit Demo Character Counter
const editDemoDescTextarea = document.getElementById('editDemoDescription');
const editDescCount = document.getElementById('editDescCount');

if (editDemoDescTextarea && editDescCount) {
    editDemoDescTextarea.addEventListener('input', function() {
        const length = this.value.length;
        editDescCount.textContent = `${length} / 1000 characters`;
    });
}

// Edit Demo Image Preview
const editDemoImageInput = document.getElementById('editDemoImage');
const editImageUploadArea = document.getElementById('editImageUploadArea');
const editImagePreview = document.getElementById('editImagePreview');
const editPreviewImage = document.getElementById('editPreviewImage');

if (editDemoImageInput) {
    editDemoImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must not exceed 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPEG, JPG, PNG, GIF, and WEBP images are allowed');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                editPreviewImage.src = e.target.result;
                editImagePreview.style.display = 'block';
                const placeholder = editImageUploadArea ? editImageUploadArea.querySelector('.upload-placeholder') : null;
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeEditImage() {
    if (editDemoImageInput) {
        editDemoImageInput.value = '';
    }
    if (editImagePreview) {
        editImagePreview.style.display = 'none';
    }
    const placeholder = editImageUploadArea ? editImageUploadArea.querySelector('.upload-placeholder') : null;
    if (placeholder) {
        placeholder.style.display = 'block';
    }
}

// Open Edit Demo Modal
async function openEditDemoModal(demoId) {
    try {
        const response = await fetch(`/artist/demo/${demoId}/edit`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        console.log('Edit Demo Data:', data);
        
        if (data.success) {
            // Populate form
            const editDemoId = document.getElementById('editDemoId');
            const editDemoTitle = document.getElementById('editDemoTitle');
            const editDemoDescription = document.getElementById('editDemoDescription');
            const editDemoCurrentImage = document.getElementById('editDemoCurrentImage');
            
            if (editDemoId) editDemoId.value = data.id;
            if (editDemoTitle) editDemoTitle.value = data.title;
            if (editDemoDescription) editDemoDescription.value = data.description || '';
            if (editDemoCurrentImage) editDemoCurrentImage.src = data.image_url;
            
            // Update character count
            const length = data.description ? data.description.length : 0;
            if (editDescCount) {
                editDescCount.textContent = `${length} / 1000 characters`;
            }
            
            // Reset new image preview
            removeEditImage();
            
            // Set form action
            const editDemoForm = document.getElementById('editDemoForm');
            if (editDemoForm) {
                editDemoForm.action = `/artist/demo/${data.id}`;
            }
            
            // Open modal
            const modal = document.getElementById('editDemoModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        } else {
            alert(data.message || 'Failed to load demo data.');
            console.error('Edit error:', data);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load demo data. Please try again.');
    }
}

// Close Edit Demo Modal
function closeEditDemoModal() {
    const modal = document.getElementById('editDemoModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    const form = document.getElementById('editDemoForm');
    if (form) {
        form.reset();
    }
    
    removeEditImage();
}

// Update Demo Form Submit
const editDemoForm = document.getElementById('editDemoForm');
if (editDemoForm) {
    editDemoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const demoId = document.getElementById('editDemoId').value;
        const formData = new FormData(this);
        const updateBtn = document.getElementById('updateDemoBtn');
        
        console.log('Updating Demo ID:', demoId);
        
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }
        
        try {
            const response = await fetch(`/artist/demo/${demoId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            console.log('Update Response:', data);
            
            if (data.success) {
                closeEditDemoModal();
                showSuccessPopup('Demo Updated Successfully!');
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Update failed. Please try again.');
                console.error('Update error:', data);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save"></i> Update Demo';
            }
        }
    });
}

// Delete Demo - COMPLETE DEBUG VERSION
async function deleteDemo(demoId) {
    console.log('=== DELETE DEMO START ===');
    console.log('1. Demo ID received:', demoId);
    console.log('2. Demo ID type:', typeof demoId);
    
    // Check if demo card exists in DOM
    const demoCard = document.querySelector(`[data-demo-id="${demoId}"]`);
    console.log('3. Demo card found in DOM:', demoCard ? 'YES' : 'NO');
    if (demoCard) {
        console.log('   Card data-demo-id attribute:', demoCard.getAttribute('data-demo-id'));
    }
    
    // Check all demo cards in page
    const allDemoCards = document.querySelectorAll('[data-demo-id]');
    console.log('4. Total demo cards on page:', allDemoCards.length);
    if (allDemoCards.length > 0) {
        console.log('   All demo IDs:', Array.from(allDemoCards).map(card => card.getAttribute('data-demo-id')));
    }
    
    if (!confirm('Are you sure you want to delete this demo artwork? This action cannot be undone.')) {
        console.log('5. User cancelled deletion');
        return;
    }

    console.log('5. User confirmed deletion');

    const url = `/artist/demo/${demoId}`;
    console.log('6. DELETE URL:', url);
    
    // Get CSRF token
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    console.log('7. CSRF token meta tag found:', csrfTokenMeta ? 'YES' : 'NO');
    
    if (!csrfTokenMeta) {
        alert('CSRF token not found. Please refresh the page and try again.');
        console.error('CRITICAL: No CSRF token meta tag found in page');
        return;
    }
    
    const csrfToken = csrfTokenMeta.getAttribute('content');
    console.log('8. CSRF token value:', csrfToken ? csrfToken.substring(0, 10) + '...' : 'EMPTY');

    try {
        console.log('9. Sending DELETE request...');
        
        const fetchOptions = {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        console.log('10. Fetch options:', fetchOptions);
        
        const response = await fetch(url, fetchOptions);
        
        console.log('11. Response received');
        console.log('    - Status:', response.status);
        console.log('    - Status Text:', response.statusText);
        console.log('    - OK:', response.ok);
        console.log('    - Headers:', {
            'Content-Type': response.headers.get('Content-Type'),
            'Content-Length': response.headers.get('Content-Length')
        });
        
        const responseText = await response.text();
        console.log('12. Response body (raw):', responseText);
        console.log('    - Length:', responseText.length);
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('13. Parsed JSON successfully:', data);
        } catch (parseError) {
            console.error('14. JSON Parse Error:', parseError);
            console.error('    - Parse error message:', parseError.message);
            console.error('    - First 500 chars of response:', responseText.substring(0, 500));
            alert('Server returned invalid response. Response: ' + responseText.substring(0, 200));
            return;
        }
        
        console.log('15. Checking data.success:', data.success);
        
        if (data.success) {
            console.log('16. Delete was successful!');
            console.log('    - Message:', data.message);
            console.log('    - Was cross-listed:', data.was_cross_listed);
            
            showSuccessPopup('Demo Deleted Successfully!');
            
            // Remove the card from DOM with animation
            if (demoCard) {
                console.log('17. Removing card from DOM...');
                demoCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                demoCard.style.opacity = '0';
                demoCard.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    demoCard.remove();
                    console.log('18. Card removed from DOM');
                    
                    // Check if there are no more demos
                    const remainingDemos = document.querySelectorAll('[data-demo-id]');
                    console.log('19. Remaining demos:', remainingDemos.length);
                    
                    if (remainingDemos.length === 0) {
                        console.log('20. No more demos, reloading page...');
                        window.location.reload();
                    }
                }, 300);
            } else {
                console.log('17. Card not found in DOM, reloading page...');
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            }
        } else {
            console.error('16. Delete failed');
            console.error('    - Success flag:', data.success);
            console.error('    - Message:', data.message);
            console.error('    - Full response:', data);
            alert(data.message || 'Delete failed. Please try again.');
        }
    } catch (error) {
        console.error('=== DELETE ERROR ===');
        console.error('Error object:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        alert('An error occurred while deleting. Error: ' + error.message + '. Check console for details.');
    }
    
    console.log('=== DELETE DEMO END ===');
}

// Success Popup
function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const title = document.getElementById('successTitle');
    
    if (popup && title) {
        title.textContent = message;
        popup.classList.add('active');
        
        setTimeout(() => {
            popup.classList.remove('active');
        }, 2000);
    }
}

// Close modals on overlay click
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing modal overlays...');
    
    const uploadModalOverlay = document.querySelector('#uploadModal .modal-overlay');
    const editModalOverlay = document.querySelector('#editDemoModal .modal-overlay');
    
    if (uploadModalOverlay) {
        uploadModalOverlay.addEventListener('click', closeUploadModal);
        console.log('Upload modal overlay listener attached');
    }
    
    if (editModalOverlay) {
        editModalOverlay.addEventListener('click', closeEditDemoModal);
        console.log('Edit modal overlay listener attached');
    }
    
    // Log all demo cards on page load
    const allDemos = document.querySelectorAll('[data-demo-id]');
    console.log('Total demo artworks on page:', allDemos.length);
    if (allDemos.length > 0) {
        console.log('Demo IDs on page:', Array.from(allDemos).map(card => card.getAttribute('data-demo-id')));
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closeEditDemoModal();
    }
});