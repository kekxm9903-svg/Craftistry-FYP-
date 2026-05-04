console.log('Artist Profile JS loaded');

// ============================================
// COMMON UTILITIES & POPUP
// ============================================

function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const messageEl = document.getElementById('successMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => popup.classList.remove('show', 'hide'), 300);
        }, 3000);
    }
}

function showDeletePopup(message) {
    const popup = document.getElementById('deletePopup');
    const messageEl = document.getElementById('deleteMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => popup.classList.remove('show', 'hide'), 300);
        }, 3000);
    }
}

function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
           || document.querySelector('input[name="_token"]')?.value;
}

// ============================================
// ACTION CARDS
// ============================================

const actionCards = document.querySelectorAll('.action-card');
actionCards.forEach(card => {
    card.addEventListener('click', (e) => {
        if (e.target.closest('.card-link')) return;
        const link = card.querySelector('.card-link');
        if (link) window.location.href = link.getAttribute('href');
    });
});

const cardLinks = document.querySelectorAll('.card-link');
cardLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.stopPropagation();
        if (link.getAttribute('href') === '#') e.preventDefault();
    });
});

// ============================================
// EDIT DEMO MODAL
// ============================================

function openEditDemoModal(id) {
    fetch(`/artist/demo/${id}/edit`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch demo artwork');
            return response.json();
        })
        .then(data => {
            document.getElementById('editDemoId').value           = data.id;
            document.getElementById('editDemoTitle').value        = data.title;
            document.getElementById('editDemoDescription').value  = data.description || '';
            document.getElementById('editDemoCurrentImage').src   = data.image_url;

            const descLength = (data.description || '').length;
            document.getElementById('editDescCount').textContent  = `${descLength} / 1000 characters`;

            document.getElementById('editDemoForm').action = `/artist/demo/${id}`;
            document.getElementById('editDemoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading demo artwork.');
        });
}

function closeEditDemoModal() {
    const modal = document.getElementById('editDemoModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('editDemoForm').reset();
        removeEditImage();
    }
}

const editDemoImage = document.getElementById('editDemoImage');
if (editDemoImage) {
    editDemoImage.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview     = document.getElementById('editImagePreview');
            const previewImg  = document.getElementById('editPreviewImage');
            const placeholder = document.querySelector('#editImageUploadArea .upload-placeholder');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                if (placeholder) placeholder.style.display = 'none';
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    });
}

function removeEditImage() {
    const imageInput  = document.getElementById('editDemoImage');
    const preview     = document.getElementById('editImagePreview');
    const placeholder = document.querySelector('#editImageUploadArea .upload-placeholder');
    if (imageInput)   imageInput.value = '';
    if (preview)      preview.style.display = 'none';
    if (placeholder)  placeholder.style.display = 'block';
}

const editDescTextarea = document.getElementById('editDemoDescription');
const editDescCount    = document.getElementById('editDescCount');
if (editDescTextarea && editDescCount) {
    editDescTextarea.addEventListener('input', function () {
        editDescCount.textContent = `${this.value.length} / 1000 characters`;
    });
}

// ============================================
// DELETE DEMO
// ============================================

function deleteDemo(id) {
    if (!confirm('Are you sure you want to delete this demo artwork?')) return;
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
// EDIT SELL MODAL
// ============================================

function openEditArtworkModal(id) {
    fetch(`/artist/artwork/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) { alert('Error fetching data'); return; }

            document.getElementById('editArtworkId').value          = data.id;
            document.getElementById('editProductName').value        = data.product_name;
            document.getElementById('editProductPrice').value       = data.product_price;
            document.getElementById('editProductDescription').value = data.product_description || '';
            document.getElementById('editSellCurrentImage').src     = data.image_url;
            document.getElementById('editProductMaterial').value    = data.material || '';
            document.getElementById('editProductHeight').value      = data.height || '';
            document.getElementById('editProductWidth').value       = data.width || '';
            document.getElementById('editProductDepth').value       = data.depth || '';
            document.getElementById('editProductUnit').value        = data.unit || 'cm';

            if (data.artwork_type === 'digital') {
                document.getElementById('editTypeDigital').checked  = true;
                document.getElementById('editTypePhysical').checked = false;
            } else {
                document.getElementById('editTypePhysical').checked = true;
                document.getElementById('editTypeDigital').checked  = false;
            }

            if (data.status === 'available') {
                document.getElementById('editStatusAvailable').checked = true;
                document.getElementById('editStatusSoldOut').checked   = false;
            } else {
                document.getElementById('editStatusSoldOut').checked   = true;
                document.getElementById('editStatusAvailable').checked = false;
            }

            // Shipping
            document.getElementById('editProductShipping').value = data.shipping_fee ?? 0;
            syncFreeShippingCheckbox();

            // Bulk sell
            const bulkEnabled = !!data.bulk_sell_enabled;
            document.getElementById('editBulkSellEnabled').checked = bulkEnabled;
            const bulkFields = document.getElementById('editBulkSellFields');
            if (bulkFields) bulkFields.style.display = bulkEnabled ? 'block' : 'none';
            if (bulkEnabled) {
                document.getElementById('editBulkMinQty').value   = data.bulk_sell_min_qty || '';
                document.getElementById('editBulkDiscount').value = data.bulk_sell_discount || '';
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

function closeEditSellModal() {
    const modal = document.getElementById('editSellModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('editSellForm').reset();
        removeEditSellImage();
    }
}

const editProductImage = document.getElementById('editProductImage');
if (editProductImage) {
    editProductImage.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview     = document.getElementById('editSellImagePreview');
            const previewImg  = document.getElementById('editSellPreviewImage');
            const placeholder = document.querySelector('#editSellImageUploadArea .upload-placeholder');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                if (placeholder) placeholder.style.display = 'none';
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    });
}

function removeEditSellImage() {
    const imageInput  = document.getElementById('editProductImage');
    const preview     = document.getElementById('editSellImagePreview');
    const placeholder = document.querySelector('#editSellImageUploadArea .upload-placeholder');
    if (imageInput)   imageInput.value = '';
    if (preview)      preview.style.display = 'none';
    if (placeholder)  placeholder.style.display = 'block';
}

const editSellDescTextarea = document.getElementById('editProductDescription');
const editSellDescCount    = document.getElementById('editSellDescCount');
if (editSellDescTextarea && editSellDescCount) {
    editSellDescTextarea.addEventListener('input', function () {
        editSellDescCount.textContent = `${this.value.length} / 2000 characters`;
    });
}

// ============================================
// DELETE ARTWORK
// ============================================

function deleteArtwork(id) {
    if (!confirm('Are you sure you want to delete this artwork?')) return;
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
// CARD IMAGE SLIDER
// ============================================

function slideCard(btn, direction) {
    if (event) event.stopPropagation();

    // Arrows are siblings of .card-slider inside .artwork-image
    const artworkImage = btn.closest('.artwork-image');
    if (!artworkImage) return;

    const slider = artworkImage.querySelector('.card-slider');
    if (!slider) return;

    const imgs  = Array.from(slider.querySelectorAll('.slider-img'));
    const dots  = Array.from(slider.querySelectorAll('.slider-dot'));
    const total = imgs.length;
    if (total <= 1) return;

    let current = parseInt(slider.dataset.index) || 0;
    let next    = (current + direction + total) % total;

    imgs[current].classList.remove('active');
    imgs[next].classList.add('active');

    if (dots.length) {
        dots[current].classList.remove('active');
        dots[next].classList.add('active');
    }

    slider.dataset.index = next;
}

// ============================================
// GLOBAL EVENT LISTENERS
// ============================================

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeEditDemoModal();
        closeEditSellModal();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity    = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});