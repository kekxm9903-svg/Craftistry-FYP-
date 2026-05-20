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
    const sellFields = document.getElementById('demoSellSection'); // FIXED: was 'demoSellFields'
    const uploadBtn = document.getElementById('demoSubmitBtn');    // FIXED: was 'uploadBtn'

    if (checkbox && sellFields && uploadBtn) {
        if (checkbox.checked) {
            sellFields.style.display = 'block';
            uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload Demo & List for Sale';

            // Make sell fields required
            document.querySelectorAll('.sell-req').forEach(input => {
                input.required = true;
            });
        } else {
            sellFields.style.display = 'none';
            uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload Demo Artwork';

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
    const form = document.getElementById('demoUploadForm'); // FIXED: was 'uploadForm'
    if (form) {
        form.reset();
    }
    removeImage();

    const sellFields = document.getElementById('demoSellSection'); // FIXED: was 'demoSellFields'
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

    const form = document.getElementById('demoUploadForm'); // FIXED: was 'uploadForm'
    if (form) {
        form.reset();
    }

    removeImage();

    if (demoDescCount) {
        demoDescCount.textContent = '0 / 1000 characters';
    }
}

// Upload Form Submit
const uploadForm = document.getElementById('demoUploadForm'); // FIXED: was 'uploadForm'
if (uploadForm) {
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const uploadBtn = document.getElementById('demoSubmitBtn'); // FIXED: was 'uploadBtn'

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
                // Show validation errors if any
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert(data.message || 'Upload failed. Please try again.');
                }
                console.error('Upload error:', data);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload Demo Artwork';
            }
        }
    });
}

// ========================================
// BULK SELL PREVIEW
// ========================================

function toggleBulkFields(checkbox) {
    const bulkFields = document.getElementById('bulkSellFields');
    if (bulkFields) {
        bulkFields.style.display = checkbox.checked ? 'block' : 'none';
    }
    if (!checkbox.checked) {
        const strip = document.getElementById('bulkPreviewStrip');
        if (strip) strip.style.display = 'none';
    }
}

function updateBulkPreview() {
    const price    = parseFloat(document.getElementById('demoPrice')?.value || 0);
    const minQty   = parseInt(document.getElementById('bulkMinQty')?.value || 0);
    const discount = parseFloat(document.getElementById('bulkDiscount')?.value || 0);
    const strip    = document.getElementById('bulkPreviewStrip');
    const text     = document.getElementById('bulkPreviewText');

    if (strip && text && price > 0 && minQty >= 2 && discount > 0 && discount < 100) {
        const discountedPrice = price * (1 - discount / 100);
        text.textContent = `Buy ${minQty}+ units → RM ${discountedPrice.toFixed(2)} each (${discount}% off)`;
        strip.style.display = 'flex';
    } else if (strip) {
        strip.style.display = 'none';
    }
}

// ========================================
// CHARACTER COUNTERS (generic)
// ========================================

function setupCounter(textareaId, counterId, max) {
    const textarea = document.getElementById(textareaId);
    const counter  = document.getElementById(counterId);
    if (!textarea || !counter) return;
    const update = () => counter.textContent = `${textarea.value.length} / ${max}`;
    textarea.addEventListener('input', update);
    update();
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
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must not exceed 5MB');
                this.value = '';
                return;
            }

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
            const editDemoId          = document.getElementById('editDemoId');
            const editDemoTitle       = document.getElementById('editDemoTitle');
            const editDemoDescription = document.getElementById('editDemoDescription');
            const editDemoCurrentImage = document.getElementById('editDemoCurrentImage');

            if (editDemoId) editDemoId.value = data.id;
            if (editDemoTitle) editDemoTitle.value = data.title;
            if (editDemoDescription) editDemoDescription.value = data.description || '';
            if (editDemoCurrentImage) editDemoCurrentImage.src = data.image_url;

            const length = data.description ? data.description.length : 0;
            if (editDescCount) {
                editDescCount.textContent = `${length} / 1000 characters`;
            }

            removeEditImage();

            const editDemoForm = document.getElementById('editDemoForm');
            if (editDemoForm) {
                editDemoForm.action = `/artist/demo/${data.id}`;
            }

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

        const demoId   = document.getElementById('editDemoId').value;
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

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert(data.message || 'Update failed. Please try again.');
                }
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

// ========================================
// DELETE DEMO
// ========================================

async function deleteDemo(demoId) {
    console.log('=== DELETE DEMO START ===');
    console.log('1. Demo ID received:', demoId);

    const demoCard = document.querySelector(`[data-demo-id="${demoId}"]`);

    if (!confirm('Are you sure you want to delete this demo artwork? This action cannot be undone.')) {
        return;
    }

    const url = `/artist/demo/${demoId}`;
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');

    if (!csrfTokenMeta) {
        alert('CSRF token not found. Please refresh the page and try again.');
        return;
    }

    const csrfToken = csrfTokenMeta.getAttribute('content');

    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            alert('Server returned invalid response: ' + responseText.substring(0, 200));
            return;
        }

        if (data.success) {
            showSuccessPopup('Demo Deleted Successfully!');

            if (demoCard) {
                demoCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                demoCard.style.opacity = '0';
                demoCard.style.transform = 'scale(0.9)';

                setTimeout(() => {
                    demoCard.remove();
                    const remainingDemos = document.querySelectorAll('[data-demo-id]');
                    if (remainingDemos.length === 0) {
                        window.location.reload();
                    }
                }, 300);
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            }
        } else {
            alert(data.message || 'Delete failed. Please try again.');
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting. Please try again.');
    }

    console.log('=== DELETE DEMO END ===');
}

// ========================================
// SUCCESS POPUP
// ========================================

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

// ========================================
// INIT
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Setup counters
    setupCounter('demoTitle', 'titleCounter', 255);
    setupCounter('demoDescription', 'descCounter', 1000);
    setupCounter('demoProductDesc', 'demoProductDescCounter', 2000);

    // Bulk sell preview listeners
    ['bulkMinQty', 'bulkDiscount', 'demoPrice'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateBulkPreview);
    });

    // Close modals on overlay click
    const uploadModalOverlay = document.querySelector('#uploadModal .modal-overlay');
    const editModalOverlay   = document.querySelector('#editDemoModal .modal-overlay');

    if (uploadModalOverlay) uploadModalOverlay.addEventListener('click', closeUploadModal);
    if (editModalOverlay)   editModalOverlay.addEventListener('click', closeEditDemoModal);

    // Log demo cards
    const allDemos = document.querySelectorAll('[data-demo-id]');
    console.log('Total demo artworks on page:', allDemos.length);
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closeEditDemoModal();
    }
});