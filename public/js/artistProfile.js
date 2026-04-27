console.log('Artist Profile JS loaded');

// ============================================
// COMMON UTILITIES & POPUP
// ============================================

// Show success popup
function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const messageEl = document.getElementById('successMessage');
    
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => {
                popup.classList.remove('show', 'hide');
            }, 300);
        }, 3000);
    }
}

// Show delete popup
function showDeletePopup(message) {
    const popup = document.getElementById('deletePopup');
    const messageEl = document.getElementById('deleteMessage');
    
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => {
                popup.classList.remove('show', 'hide');
            }, 300);
        }, 3000);
    }
}

// Get CSRF token
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
           || document.querySelector('input[name="_token"]')?.value;
}

// ============================================
// ACTION CARDS
// ============================================

// Handle action card clicks
const actionCards = document.querySelectorAll('.action-card');
actionCards.forEach(card => {
    card.addEventListener('click', (e) => {
        if (e.target.closest('.card-link')) return;
        
        const link = card.querySelector('.card-link');
        if (link) {
            window.location.href = link.getAttribute('href');
        }
    });
});

// Prevent card click when clicking the link
const cardLinks = document.querySelectorAll('.card-link');
cardLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.stopPropagation();
        if (link.getAttribute('href') === '#') {
            e.preventDefault();
        }
    });
});

// ============================================
// DEMO ARTWORK FUNCTIONS
// ============================================

// Open Upload Demo Modal
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Close Upload Demo Modal
function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        const form = document.getElementById('uploadForm');
        if (form) {
            form.reset();
            removeImage();
        }
    }
}

// Toggle Demo Sell Fields
function toggleDemoSellFields() {
    const checkbox = document.getElementById('alsoSellCheckbox');
    const container = document.getElementById('demoSellFields');
    const requiredInputs = container.querySelectorAll('.sell-req');

    if (checkbox.checked) {
        container.style.display = 'block';
        requiredInputs.forEach(input => input.required = true);
        if (!container.querySelector('input[name="artwork_type"]:checked')) {
            container.querySelector('input[name="artwork_type"][value="physical"]').checked = true;
        }
        if (!container.querySelector('input[name="status"]:checked')) {
            container.querySelector('input[name="status"][value="available"]').checked = true;
        }
    } else {
        container.style.display = 'none';
        requiredInputs.forEach(input => input.required = false);
    }
}

// Demo Image Preview
const imageInput = document.getElementById('demoImage');
if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImage');
                const placeholder = document.querySelector('.upload-placeholder');
                
                if (preview && previewImg && placeholder) {
                    previewImg.src = e.target.result;
                    placeholder.style.display = 'none';
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove Demo Image
function removeImage() {
    const imageInput = document.getElementById('demoImage');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.querySelector('.upload-placeholder');
    
    if (imageInput) imageInput.value = '';
    if (preview) preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
}

// Character count for demo description
const descTextarea = document.getElementById('demoDescription');
const descCount = document.getElementById('descCount');

if (descTextarea && descCount) {
    descTextarea.addEventListener('input', function() {
        const count = this.value.length;
        descCount.textContent = `${count} / 1000 characters`;
    });
}

// Open Edit Demo Modal
function openEditDemoModal(id) {
    fetch(`/artist/demo/${id}/edit`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch demo artwork');
            return response.json();
        })
        .then(data => {
            document.getElementById('editDemoId').value = data.id;
            document.getElementById('editDemoTitle').value = data.title;
            document.getElementById('editDemoDescription').value = data.description || '';
            document.getElementById('editDemoCurrentImage').src = data.image_url;
            
            const descLength = (data.description || '').length;
            document.getElementById('editDescCount').textContent = `${descLength} / 1000 characters`;
            
            document.getElementById('editDemoForm').action = `/artist/demo/${id}`;
            document.getElementById('editDemoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading demo artwork.');
        });
}

// Close Edit Demo Modal
function closeEditDemoModal() {
    const modal = document.getElementById('editDemoModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('editDemoForm').reset();
        removeEditImage();
    }
}

// Edit Demo Image Preview
const editDemoImage = document.getElementById('editDemoImage');
if (editDemoImage) {
    editDemoImage.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('editImagePreview');
                const previewImg = document.getElementById('editPreviewImage');
                const placeholder = document.querySelector('#editImageUploadArea .upload-placeholder');
                
                if (preview && previewImg) {
                    previewImg.src = e.target.result;
                    if (placeholder) placeholder.style.display = 'none';
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove Edit Demo Image
function removeEditImage() {
    const imageInput = document.getElementById('editDemoImage');
    const preview = document.getElementById('editImagePreview');
    const placeholder = document.querySelector('#editImageUploadArea .upload-placeholder');
    
    if (imageInput) imageInput.value = '';
    if (preview) preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
}

// Character count for edit demo description
const editDescTextarea = document.getElementById('editDemoDescription');
const editDescCount = document.getElementById('editDescCount');

if (editDescTextarea && editDescCount) {
    editDescTextarea.addEventListener('input', function() {
        const count = this.value.length;
        editDescCount.textContent = `${count} / 1000 characters`;
    });
}

// Delete Demo
function deleteDemo(id) {
    if (!confirm('Are you sure you want to delete this demo artwork?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/artist/demo/${id}`;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${getCSRFToken()}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
}

// ============================================
// ARTWORK SELL FUNCTIONS
// ============================================

// Open Sell Modal
function openSellModal() {
    const modal = document.getElementById('sellModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Close Sell Modal
function closeSellModal() {
    const modal = document.getElementById('sellModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        const form = document.getElementById('sellForm');
        if (form) {
            form.reset();
            removeSellImage();
        }
    }
}

// Sell Image Preview
const productImage = document.getElementById('productImage');
if (productImage) {
    productImage.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('sellImagePreview');
                const previewImg = document.getElementById('sellPreviewImage');
                const placeholder = document.getElementById('sellUploadPlaceholder');
                
                if (preview && previewImg && placeholder) {
                    previewImg.src = e.target.result;
                    placeholder.style.display = 'none';
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove Sell Image
function removeSellImage() {
    const imageInput = document.getElementById('productImage');
    const preview = document.getElementById('sellImagePreview');
    const placeholder = document.getElementById('sellUploadPlaceholder');
    
    if (imageInput) imageInput.value = '';
    if (preview) preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
}

// Character count for sell description
const sellDescTextarea = document.getElementById('productDescription');
if (sellDescTextarea) {
    sellDescTextarea.addEventListener('input', function() {
        const count = this.value.length;
        const countEl = document.querySelector('#productDescription + .char-count');
        if (countEl) {
            countEl.textContent = `${count} / 2000 characters`;
        }
    });
}

// Open Edit Artwork Modal
function openEditArtworkModal(id) {
    fetch(`/artist/artwork/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Error fetching data');
                return;
            }

            // Populate Basic Fields
            document.getElementById('editArtworkId').value = data.id;
            document.getElementById('editProductName').value = data.product_name;
            document.getElementById('editProductPrice').value = data.product_price;
            document.getElementById('editProductDescription').value = data.product_description || '';
            document.getElementById('editSellCurrentImage').src = data.image_url;
            
            // Populate Additional Fields
            document.getElementById('editProductMaterial').value = data.material || '';
            document.getElementById('editProductHeight').value = data.height || '';
            document.getElementById('editProductWidth').value = data.width || '';
            document.getElementById('editProductDepth').value = data.depth || '';
            document.getElementById('editProductUnit').value = data.unit || 'cm';
            
            // Populate Artwork Type Radio Buttons
            if (data.artwork_type === 'digital') {
                document.getElementById('editTypeDigital').checked = true;
                document.getElementById('editTypePhysical').checked = false;
            } else {
                document.getElementById('editTypePhysical').checked = true;
                document.getElementById('editTypeDigital').checked = false;
            }

            // Populate Status Radio Buttons
            if (data.status === 'available') {
                document.getElementById('editStatusAvailable').checked = true;
                document.getElementById('editStatusSoldOut').checked = false;
            } else {
                document.getElementById('editStatusSoldOut').checked = true;
                document.getElementById('editStatusAvailable').checked = false;
            }

            const descLength = (data.product_description || '').length;
            document.getElementById('editSellDescCount').textContent = `${descLength} / 2000 characters`;
            
            document.getElementById('editSellForm').action = `/artist/artwork/${id}`;
            document.getElementById('editSellModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading artwork info.');
        });
}

// Close Edit Sell Modal
function closeEditSellModal() {
    const modal = document.getElementById('editSellModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('editSellForm').reset();
        removeEditSellImage();
    }
}

// Edit Sell Image Preview
const editProductImage = document.getElementById('editProductImage');
if (editProductImage) {
    editProductImage.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('editSellImagePreview');
                const previewImg = document.getElementById('editSellPreviewImage');
                const placeholder = document.querySelector('#editSellImageUploadArea .upload-placeholder');
                
                if (preview && previewImg) {
                    previewImg.src = e.target.result;
                    if (placeholder) placeholder.style.display = 'none';
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove Edit Sell Image
function removeEditSellImage() {
    const imageInput = document.getElementById('editProductImage');
    const preview = document.getElementById('editSellImagePreview');
    const placeholder = document.querySelector('#editSellImageUploadArea .upload-placeholder');
    
    if (imageInput) imageInput.value = '';
    if (preview) preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
}

// Character count for edit sell description
const editSellDescTextarea = document.getElementById('editProductDescription');
const editSellDescCount = document.getElementById('editSellDescCount');

if (editSellDescTextarea && editSellDescCount) {
    editSellDescTextarea.addEventListener('input', function() {
        const count = this.value.length;
        editSellDescCount.textContent = `${count} / 2000 characters`;
    });
}

// Delete Artwork
function deleteArtwork(id) {
    if (!confirm('Are you sure you want to delete this artwork?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/artist/artwork/${id}`;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${getCSRFToken()}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
}

// ============================================
// GLOBAL EVENT LISTENERS
// ============================================

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closeSellModal();
        closeEditDemoModal();
        closeEditSellModal();
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});