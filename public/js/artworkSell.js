// ========================================
// SUCCESS POPUP - AUTO HIDE VERSION
// ========================================
function showSuccessPopup(message = 'Success!') {
    const popup = document.getElementById('successPopup');
    if (!popup) return;
    
    const messageElement = popup.querySelector('p') || popup.querySelector('h3') || popup.querySelector('#successTitle');
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    
    // Show popup with slide-in animation
    popup.classList.add('show');
    popup.classList.remove('hide');
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        popup.classList.remove('show');
        popup.classList.add('hide');
    }, 3000);
}

// ========================================
// ARTWORK SELL UPLOAD & EDIT FUNCTIONS
// ========================================

// Character counter for sell description
const sellDescTextarea = document.getElementById('productDescription');
const sellDescCount = document.getElementById('sellDescCount');

if (sellDescTextarea && sellDescCount) {
    sellDescTextarea.addEventListener('input', function() {
        const length = this.value.length;
        sellDescCount.textContent = `${length} / 2000 characters`;
    });
}

// Image preview for sell upload
const productImageInput = document.getElementById('productImage');
const sellImageUploadArea = document.getElementById('sellImageUploadArea');
const sellImagePreview = document.getElementById('sellImagePreview');
const sellPreviewImage = document.getElementById('sellPreviewImage');
const sellUploadPlaceholder = document.getElementById('sellUploadPlaceholder');

if (productImageInput) {
    productImageInput.addEventListener('change', function(e) {
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
                sellPreviewImage.src = e.target.result;
                sellImagePreview.style.display = 'block';
                if (sellUploadPlaceholder) {
                    sellUploadPlaceholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeSellImage() {
    if (productImageInput) {
        productImageInput.value = '';
    }
    if (sellImagePreview) {
        sellImagePreview.style.display = 'none';
    }
    if (sellUploadPlaceholder) {
        sellUploadPlaceholder.style.display = 'block';
    }
}

// Open/Close Sell Modal
function openSellModal() {
    const modal = document.getElementById('sellModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeSellModal() {
    const modal = document.getElementById('sellModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    const form = document.getElementById('sellForm');
    if (form) {
        form.reset();
    }
    
    removeSellImage();
    
    if (sellDescCount) {
        sellDescCount.textContent = '0 / 2000 characters';
    }
}

// Sell Form Submit
const sellForm = document.getElementById('sellForm');
if (sellForm) {
    sellForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sellBtn = document.getElementById('sellBtn');
        
        console.log('Submitting sell form...');
        
        if (sellBtn) {
            sellBtn.disabled = true;
            sellBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Listing...';
        }
        
        try {
            const response = await fetch('/artist/artwork/sell', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            console.log('Sell Response:', data);
            
            if (data.success) {
                closeSellModal();
                showSuccessPopup('Artwork listed successfully!');
                
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
            if (sellBtn) {
                sellBtn.disabled = false;
                sellBtn.innerHTML = '<i class="fas fa-upload"></i> List Artwork';
            }
        }
    });
}

// ========================================
// EDIT ARTWORK SELL FUNCTIONS
// ========================================

// Edit Sell Character Counter
const editSellDescTextarea = document.getElementById('editProductDescription');
const editSellDescCount = document.getElementById('editSellDescCount');

if (editSellDescTextarea && editSellDescCount) {
    editSellDescTextarea.addEventListener('input', function() {
        const length = this.value.length;
        editSellDescCount.textContent = `${length} / 2000 characters`;
    });
}

// Edit Sell Image Preview
const editProductImageInput = document.getElementById('editProductImage');
const editSellImageUploadArea = document.getElementById('editSellImageUploadArea');
const editSellImagePreview = document.getElementById('editSellImagePreview');
const editSellPreviewImage = document.getElementById('editSellPreviewImage');

if (editProductImageInput) {
    editProductImageInput.addEventListener('change', function(e) {
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
                editSellPreviewImage.src = e.target.result;
                editSellImagePreview.style.display = 'block';
                const placeholder = editSellImageUploadArea ? editSellImageUploadArea.querySelector('.upload-placeholder') : null;
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeEditSellImage() {
    if (editProductImageInput) {
        editProductImageInput.value = '';
    }
    if (editSellImagePreview) {
        editSellImagePreview.style.display = 'none';
    }
    const placeholder = editSellImageUploadArea ? editSellImageUploadArea.querySelector('.upload-placeholder') : null;
    if (placeholder) {
        placeholder.style.display = 'block';
    }
}

// Open Edit Artwork Modal
async function openEditArtworkModal(artworkId) {
    try {
        const response = await fetch(`/artist/artwork/${artworkId}/edit`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        console.log('Edit Artwork Response:', result);
        console.log('Status value:', result.status);
        
        if (result.success) {
            // Get all form elements
            const editArtworkId = document.getElementById('editArtworkId');
            const editProductName = document.getElementById('editProductName');
            const editProductPrice = document.getElementById('editProductPrice');
            const editProductDescription = document.getElementById('editProductDescription');
            const editSellCurrentImage = document.getElementById('editSellCurrentImage');
            const editProductMaterial = document.getElementById('editProductMaterial');
            const editProductHeight = document.getElementById('editProductHeight');
            const editProductWidth = document.getElementById('editProductWidth');
            const editProductDepth = document.getElementById('editProductDepth');
            const editProductUnit = document.getElementById('editProductUnit');
            
            // Artwork Type Radio Buttons
            const editTypePhysical = document.getElementById('editTypePhysical');
            const editTypeDigital = document.getElementById('editTypeDigital');
            
            // Status Radio Buttons
            const editStatusAvailable = document.getElementById('editStatusAvailable');
            const editStatusSoldOut = document.getElementById('editStatusSoldOut');
            
            // Populate basic fields
            if (editArtworkId) editArtworkId.value = result.id;
            if (editProductName) editProductName.value = result.product_name;
            if (editProductPrice) editProductPrice.value = result.product_price;
            if (editProductDescription) editProductDescription.value = result.product_description || '';
            if (editSellCurrentImage) editSellCurrentImage.src = result.image_url;
            
            // Populate dimension fields
            if (editProductMaterial) editProductMaterial.value = result.material || '';
            if (editProductHeight) editProductHeight.value = result.height || '';
            if (editProductWidth) editProductWidth.value = result.width || '';
            if (editProductDepth) editProductDepth.value = result.depth || '';
            if (editProductUnit) editProductUnit.value = result.unit || 'cm';
            
            // Set Artwork Type radio buttons
            if (result.artwork_type === 'digital') {
                if (editTypeDigital) editTypeDigital.checked = true;
                if (editTypePhysical) editTypePhysical.checked = false;
            } else {
                if (editTypePhysical) editTypePhysical.checked = true;
                if (editTypeDigital) editTypeDigital.checked = false;
            }
            
            // Set Status radio buttons
            if (result.status === 'available') {
                if (editStatusAvailable) editStatusAvailable.checked = true;
                if (editStatusSoldOut) editStatusSoldOut.checked = false;
            } else {
                if (editStatusSoldOut) editStatusSoldOut.checked = true;
                if (editStatusAvailable) editStatusAvailable.checked = false;
            }
            
            // Update character count
            const length = result.product_description ? result.product_description.length : 0;
            if (editSellDescCount) {
                editSellDescCount.textContent = `${length} / 2000 characters`;
            }
            
            // Reset new image preview
            removeEditSellImage();
            
            // Set form action
            const editSellForm = document.getElementById('editSellForm');
            if (editSellForm) {
                editSellForm.action = `/artist/artwork/${result.id}`;
            }
            
            // Open modal
            const modal = document.getElementById('editSellModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        } else {
            alert(result.message || 'Failed to load artwork data.');
            console.error('Edit error:', result);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load artwork data. Please try again.');
    }
}

// Close Edit Sell Modal
function closeEditSellModal() {
    const modal = document.getElementById('editSellModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    const form = document.getElementById('editSellForm');
    if (form) {
        form.reset();
    }
    
    removeEditSellImage();
}

// Update Artwork Form Submit
const editSellForm = document.getElementById('editSellForm');
if (editSellForm) {
    editSellForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const artworkId = document.getElementById('editArtworkId').value;
        const formData = new FormData(this);
        const updateBtn = document.getElementById('updateArtworkBtn');
        
        console.log('===== STARTING UPDATE =====');
        console.log('Artwork ID:', artworkId);
        
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }
        
        try {
            const url = `/artist/artwork/${artworkId}`;
            console.log('Request URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Response Status:', response.status);
            
            const data = await response.json();
            console.log('Update Response:', data);
            
            if (data.success) {
                console.log('SUCCESS!');
                closeEditSellModal();
                showSuccessPopup('Artwork Updated Successfully!');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                console.error('Update failed:', data);
                alert(data.message || 'Update failed. Please try again.');
                
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    let errorMsg = 'Validation errors:\n';
                    for (let field in data.errors) {
                        errorMsg += `${field}: ${data.errors[field].join(', ')}\n`;
                    }
                    alert(errorMsg);
                }
            }
            
        } catch (error) {
            console.error('===== ERROR CAUGHT =====');
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        } finally {
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save"></i> Update Artwork';
            }
            console.log('===== UPDATE ATTEMPT COMPLETE =====');
        }
    });
}

// Delete Artwork
async function deleteArtwork(artworkId) {
    if (confirm('Are you sure you want to delete this artwork? This action cannot be undone.')) {
        try {
            const response = await fetch(`/artist/artwork/${artworkId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            console.log('Delete Response:', data);
            
            if (data.success) {
                showSuccessPopup('Artwork Deleted Successfully!');
                
                // Reload page after brief delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Delete failed. Please try again.');
                console.error('Delete error:', data);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
}

// Close modals on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const sellModalOverlay = document.querySelector('#sellModal .modal-overlay');
    const editSellModalOverlay = document.querySelector('#editSellModal .modal-overlay');
    
    if (sellModalOverlay) {
        sellModalOverlay.addEventListener('click', closeSellModal);
    }
    
    if (editSellModalOverlay) {
        editSellModalOverlay.addEventListener('click', closeEditSellModal);
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSellModal();
        closeEditSellModal();
    }
});