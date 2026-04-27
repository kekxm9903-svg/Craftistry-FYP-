// Craftistry Class Events JavaScript - Complete Version

console.log('ClassEvent.js loading...');

// ============================================
// GLOBAL VARIABLES
// ============================================

let deleteClassId = null;

// ============================================
// MODAL FUNCTIONS
// ============================================

function openModal() {
    console.log('openModal() called');
    const uploadModal = document.getElementById('uploadModal');
    
    if (!uploadModal) {
        console.error('ERROR: uploadModal element not found!');
        alert('Error: Modal element not found. Check your HTML.');
        return;
    }
    
    console.log('Adding active class to modal...');
    uploadModal.classList.add('active');
    document.body.style.overflow = 'hidden';
    console.log('Modal opened successfully');
}

function closeModal() {
    console.log('closeModal() called');
    const uploadModal = document.getElementById('uploadModal');
    const uploadForm = document.getElementById('uploadForm');
    
    if (!uploadModal) {
        console.error('ERROR: uploadModal not found in closeModal');
        return;
    }
    
    uploadModal.classList.remove('active');
    document.body.style.overflow = 'auto';
    
    if (uploadForm) {
        uploadForm.reset();
    }
    
    removeFile();
    clearErrors();
    
    // Reset calculated fields
    const durationWeeks = document.getElementById('durationWeeks');
    const durationHours = document.getElementById('durationHours');
    const durationMinutes = document.getElementById('durationMinutes');
    
    if (durationWeeks) durationWeeks.value = '';
    if (durationHours) durationHours.value = '';
    if (durationMinutes) durationMinutes.value = '';
    
    console.log('Modal closed successfully');
}

// ============================================
// EDIT/DELETE MODAL FUNCTIONS
// ============================================

// Edit Class Function - Load data and show modal
async function editClass(classId) {
    try {
        // Fetch class data as JSON
        const response = await fetch(`/class-event/${classId}/data`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch class data');
        }
        
        const data = result.data;
        
        // Populate form fields
        document.getElementById('editClassId').value = data.id;
        document.getElementById('editEventTitle').value = data.title;
        document.getElementById('editEventDescription').value = data.description || '';
        
        // Update character count for description
        const editDescCount = document.getElementById('editDescCount');
        if (editDescCount) {
            const descLength = (data.description || '').length;
            editDescCount.textContent = `${descLength} / 1000 characters`;
        }
        
        // ── Fee / Price ──────────────────────────────────────────────
        const isPaid = parseInt(data.is_paid) === 1;
        const editFeeFree = document.getElementById('editFeeFree');
        const editFeePaid = document.getElementById('editFeePaid');
        const editPriceField = document.getElementById('editPriceField');
        const editPrice = document.getElementById('editPrice');

        if (editFeeFree && editFeePaid) {
            editFeeFree.checked = !isPaid;
            editFeePaid.checked = isPaid;
        }

        if (editPriceField) {
            if (isPaid) {
                editPriceField.classList.add('active');
                if (editPrice) {
                    editPrice.value = data.price ? parseFloat(data.price).toFixed(2) : '';
                    editPrice.required = true;
                }
            } else {
                editPriceField.classList.remove('active');
                if (editPrice) {
                    editPrice.value = '';
                    editPrice.required = false;
                }
            }
        }
        // ────────────────────────────────────────────────────────────

        // Set media type and show/hide fields
        if (data.media_type === 'online') {
            document.getElementById('editMediaOnline').checked = true;
            document.getElementById('editPlatformField').classList.add('active');
            document.getElementById('editLocationField').classList.remove('active');
            document.getElementById('editPlatform').value = data.platform || '';
            document.getElementById('editPlatform').required = true;
            document.getElementById('editLocation').required = false;
        } else {
            document.getElementById('editMediaPhysical').checked = true;
            document.getElementById('editLocationField').classList.add('active');
            document.getElementById('editPlatformField').classList.remove('active');
            document.getElementById('editLocation').value = data.location || '';
            document.getElementById('editLocation').required = true;
            document.getElementById('editPlatform').required = false;
        }
        
        // Set dates and times
        document.getElementById('editStartDate').value = data.start_date;
        document.getElementById('editEndDate').value = data.end_date;
        document.getElementById('editStartTime').value = data.start_time;
        document.getElementById('editEndTime').value = data.end_time;

        // Pre-fill enrollment deadline (blank if null)
        const editDeadline = document.getElementById('editEnrollmentDeadline');
        if (editDeadline) {
            editDeadline.value = data.enrollment_deadline || '';
        }

        // Pre-fill participant limit
        const editMaxPax = document.getElementById('editMaxParticipants');
        if (editMaxPax) {
            editMaxPax.value = data.max_participants || '';
        }

        // Pre-fill cancellation deadline
        const editCancelDeadline = document.getElementById('editCancellationDeadline');
        if (editCancelDeadline) {
            editCancelDeadline.value = data.cancellation_deadline || '';
        }

        // Pre-fill require_form
        const editRequireFormNo  = document.getElementById('editRequireFormNo');
        const editRequireFormYes = document.getElementById('editRequireFormYes');
        const editFormUrlField   = document.getElementById('editFormUrlField');
        const editEnrollmentFormUrl = document.getElementById('editEnrollmentFormUrl');

        if (editRequireFormNo && editRequireFormYes) {
            const requireForm = parseInt(data.require_form) === 1;
            editRequireFormNo.checked  = !requireForm;
            editRequireFormYes.checked = requireForm;
            if (editFormUrlField) {
                editFormUrlField.style.display = requireForm ? 'block' : 'none';
            }
            if (editEnrollmentFormUrl) {
                editEnrollmentFormUrl.value = data.enrollment_form_url || '';
            }
        }
        
        // Calculate and display durations
        calculateEditWeeks();
        calculateEditDuration();
        
        // Set current image
        document.getElementById('currentImage').src = data.poster_url;
        
        // Reset file preview
        document.getElementById('editFilePreview').style.display = 'none';
        document.getElementById('editPosterImage').value = '';
        
        // Show the edit modal
        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        console.error('Error:', error);
        showError('Failed to load class data');
    }
}

// Close Edit Modal
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.getElementById('editForm').reset();
    document.getElementById('editFilePreview').style.display = 'none';
    
    // Reset calculated fields
    const editDurationWeeks = document.getElementById('editDurationWeeks');
    const editDurationHours = document.getElementById('editDurationHours');
    const editDurationMinutes = document.getElementById('editDurationMinutes');
    
    if (editDurationWeeks) editDurationWeeks.value = '';
    if (editDurationHours) editDurationHours.value = '';
    if (editDurationMinutes) editDurationMinutes.value = '';
    
    document.body.style.overflow = 'auto';
}

// Confirm Delete - Using Browser's Native Confirm Dialog
async function confirmDelete(classId, title) {
    // Use browser's native confirm dialog
    const confirmed = confirm(`Are you sure you want to delete this class/event?\n\n${title}`);
    
    if (confirmed) {
        await deleteClass(classId);
    }
}

// Delete Class - Direct deletion without modal
async function deleteClass(classId) {
    if (!classId) return;
    
    try {
        const response = await fetch(`/class-event/${classId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Class/Event deleted successfully!');
            
            // Remove card with animation
            const card = document.querySelector(`.class-card[data-id="${classId}"]`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Reload if no more cards
                    const grid = document.getElementById('classGrid');
                    if (grid.querySelectorAll('.class-card').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            showError(data.message || 'Failed to delete');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Failed to delete class/event');
    }
}

// ============================================
// AUTO-CALCULATION FUNCTIONS (UPLOAD FORM)
// ============================================

function calculateWeeks() {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const durationWeeks = document.getElementById('durationWeeks');
    
    if (startDate && endDate && startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end < start) {
            endDate.setCustomValidity('End date must be after start date');
            if (durationWeeks) durationWeeks.value = '';
            return;
        } else {
            endDate.setCustomValidity('');
        }
        
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const weeks = (diffDays / 7).toFixed(1);
        
        if (durationWeeks) durationWeeks.value = weeks;
    } else {
        if (durationWeeks) durationWeeks.value = '';
    }
}

function calculateDuration() {
    const startTime = document.getElementById('startTime');
    const endTime = document.getElementById('endTime');
    const durationHours = document.getElementById('durationHours');
    const durationMinutes = document.getElementById('durationMinutes');
    
    if (startTime && endTime && startTime.value && endTime.value) {
        const [startHours, startMinutes] = startTime.value.split(':').map(Number);
        const [endHours, endMinutes] = endTime.value.split(':').map(Number);
        
        const startTotalMinutes = startHours * 60 + startMinutes;
        const endTotalMinutes = endHours * 60 + endMinutes;
        
        if (endTotalMinutes <= startTotalMinutes) {
            endTime.setCustomValidity('End time must be after start time');
            if (durationHours) durationHours.value = '';
            if (durationMinutes) durationMinutes.value = '';
            return;
        } else {
            endTime.setCustomValidity('');
        }
        
        const diffMinutes = endTotalMinutes - startTotalMinutes;
        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;
        
        if (durationHours) durationHours.value = hours;
        if (durationMinutes) durationMinutes.value = minutes;
    } else {
        if (durationHours) durationHours.value = '';
        if (durationMinutes) durationMinutes.value = '';
    }
}

// ============================================
// AUTO-CALCULATION FUNCTIONS (EDIT FORM)
// ============================================

function calculateEditWeeks() {
    const startDate = document.getElementById('editStartDate');
    const endDate = document.getElementById('editEndDate');
    const durationWeeks = document.getElementById('editDurationWeeks');
    
    if (startDate && endDate && startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end < start) {
            endDate.setCustomValidity('End date must be after start date');
            if (durationWeeks) durationWeeks.value = '';
            return;
        } else {
            endDate.setCustomValidity('');
        }
        
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const weeks = (diffDays / 7).toFixed(1);
        
        if (durationWeeks) durationWeeks.value = weeks;
    } else {
        if (durationWeeks) durationWeeks.value = '';
    }
}

function calculateEditDuration() {
    const startTime = document.getElementById('editStartTime');
    const endTime = document.getElementById('editEndTime');
    const durationHours = document.getElementById('editDurationHours');
    const durationMinutes = document.getElementById('editDurationMinutes');
    
    if (startTime && endTime && startTime.value && endTime.value) {
        const [startHours, startMinutes] = startTime.value.split(':').map(Number);
        const [endHours, endMinutes] = endTime.value.split(':').map(Number);
        
        const startTotalMinutes = startHours * 60 + startMinutes;
        const endTotalMinutes = endHours * 60 + endMinutes;
        
        if (endTotalMinutes <= startTotalMinutes) {
            endTime.setCustomValidity('End time must be after start time');
            if (durationHours) durationHours.value = '';
            if (durationMinutes) durationMinutes.value = '';
            return;
        } else {
            endTime.setCustomValidity('');
        }
        
        const diffMinutes = endTotalMinutes - startTotalMinutes;
        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;
        
        if (durationHours) durationHours.value = hours;
        if (durationMinutes) durationMinutes.value = minutes;
    } else {
        if (durationHours) durationHours.value = '';
        if (durationMinutes) durationMinutes.value = '';
    }
}

// ============================================
// FILE UPLOAD HANDLING
// ============================================

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        displayFile(file);
    }
}

function displayFile(file) {
    const fileNameText = document.getElementById('fileNameText');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        showError('posterImageError', 'File size must be less than 5MB');
        return;
    }

    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        showError('posterImageError', 'Invalid file type. Please upload JPEG, JPG, PNG, GIF, or WEBP');
        return;
    }

    if (fileNameText) fileNameText.textContent = file.name;
    if (filePreview) filePreview.classList.add('active');
    
    const reader = new FileReader();
    reader.onload = (e) => {
        if (previewImage) previewImage.src = e.target.result;
    };
    reader.readAsDataURL(file);

    clearError('posterImageError');
}

function removeFile() {
    const fileInput = document.getElementById('posterImage');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    const fileNameText = document.getElementById('fileNameText');
    
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.classList.remove('active');
    if (previewImage) previewImage.src = '';
    if (fileNameText) fileNameText.textContent = '';
}

// ============================================
// VALIDATION & ERROR HANDLING
// ============================================

function validateForm() {
    let isValid = true;
    clearErrors();

    const fileInput = document.getElementById('posterImage');
    if (!fileInput || !fileInput.files[0]) {
        showErrorMessage('posterImageError', 'Please upload a poster image');
        isValid = false;
    }

    const title = document.getElementById('eventTitle');
    if (title && !title.value.trim()) {
        showErrorMessage('titleError', 'Please enter a title');
        isValid = false;
    }

    // Validate price if Paid is selected
    const isPaidRadio = document.querySelector('#uploadModal input[name="is_paid"]:checked');
    if (isPaidRadio && isPaidRadio.value === '1') {
        const priceInput = document.getElementById('price');
        if (!priceInput || !priceInput.value || parseFloat(priceInput.value) <= 0) {
            showErrorMessage('priceError', 'Please enter a valid price');
            isValid = false;
        }
    }

    const mediaType = document.querySelector('input[name="media_type"]:checked');
    if (mediaType && mediaType.value === 'online') {
        const platform = document.getElementById('platform');
        if (platform && !platform.value.trim()) {
            showErrorMessage('platformError', 'Please enter the platform');
            isValid = false;
        }
    } else {
        const location = document.getElementById('location');
        if (location && !location.value.trim()) {
            showErrorMessage('locationError', 'Please enter the location');
            isValid = false;
        }
    }

    const startDate = document.getElementById('startDate');
    if (startDate && !startDate.value) {
        showErrorMessage('startDateError', 'Please select a start date');
        isValid = false;
    }

    const endDate = document.getElementById('endDate');
    if (endDate && !endDate.value) {
        showErrorMessage('endDateError', 'Please select an end date');
        isValid = false;
    }

    // Validate enrollment deadline <= start date (if set)
    const deadline = document.getElementById('enrollmentDeadline');
    if (deadline && deadline.value && startDate && startDate.value) {
        if (deadline.value > startDate.value) {
            showErrorMessage('enrollmentDeadlineError', 'Enrollment deadline must be on or before the start date');
            isValid = false;
        }
    }

    const startTime = document.getElementById('startTime');
    if (startTime && !startTime.value) {
        showErrorMessage('startTimeError', 'Please select a start time');
        isValid = false;
    }

    const endTime = document.getElementById('endTime');
    if (endTime && !endTime.value) {
        showErrorMessage('endTimeError', 'Please select an end time');
        isValid = false;
    }

    return isValid;
}

function showErrorMessage(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
    }
}

function clearError(elementId) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = '';
    }
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(element => {
        element.textContent = '';
    });
}

// ============================================
// NOTIFICATION FUNCTIONS
// ============================================

function showSuccess(message) {
    const popup = document.getElementById('successPopup');
    const messageEl = popup ? popup.querySelector('p') : null;
    
    if (!popup) {
        console.error('Success popup element not found!');
        return;
    }
    
    if (messageEl) messageEl.textContent = message;
    
    popup.classList.remove('show', 'hide');
    void popup.offsetWidth;
    popup.classList.add('show');
    
    setTimeout(() => {
        popup.classList.remove('show');
        popup.classList.add('hide');
        setTimeout(() => popup.classList.remove('hide'), 350);
    }, 3000);
}

function showError(elementIdOrMessage, message) {
    if (arguments.length === 1) {
        const notification = document.getElementById('errorNotification');
        const messageEl = notification ? notification.querySelector('p') : null;
        
        if (!notification) return;
        
        if (messageEl) messageEl.textContent = elementIdOrMessage;
        
        notification.classList.remove('show', 'hide');
        void notification.offsetWidth;
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
            notification.classList.add('hide');
            setTimeout(() => notification.classList.remove('hide'), 350);
        }, 3000);
    } else {
        showErrorMessage(elementIdOrMessage, message);
    }
}

function showNotification(type, message) {
    if (type === 'success') {
        showSuccess(message);
    } else {
        showError(message);
    }
}

// ============================================
// CLASS CARD FUNCTIONS
// ============================================

function viewClass(classId) {
    window.location.href = `/class-events/${classId}`;
}

async function saveClass(classId) {
    try {
        const response = await fetch(`/class-events/${classId}/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showSuccess(data.message || 'Class saved successfully!');
        } else {
            showError(data.message || 'Failed to save class');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    }
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM Content Loaded ===');
    
    // Upload Button
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    // Close Modal Button
    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }

    // Cancel Button
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }

    // Modal Overlay Click
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('click', function(e) {
            if (e.target === uploadModal || e.target.classList.contains('modal-overlay')) {
                closeModal();
            }
        });
    }

    // File Upload
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('posterImage');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                fileInput.files = e.dataTransfer.files;
                displayFile(file);
            }
        });
    }

    // Remove File Button
    const removeFileBtn = document.getElementById('removeFile');
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', removeFile);
    }

    // Media Type Toggle (Upload Form)
    const mediaOnline = document.getElementById('mediaOnline');
    const mediaPhysical = document.getElementById('mediaPhysical');
    const platformField = document.getElementById('platformField');
    const locationField = document.getElementById('locationField');

    if (mediaOnline) {
        mediaOnline.addEventListener('change', () => {
            if (platformField) platformField.classList.add('active');
            if (locationField) locationField.classList.remove('active');
            const platformInput = document.getElementById('platform');
            const locationInput = document.getElementById('location');
            if (platformInput) platformInput.required = true;
            if (locationInput) locationInput.required = false;
            clearError('locationError');
        });
    }

    if (mediaPhysical) {
        mediaPhysical.addEventListener('change', () => {
            if (platformField) platformField.classList.remove('active');
            if (locationField) locationField.classList.add('active');
            const platformInput = document.getElementById('platform');
            const locationInput = document.getElementById('location');
            if (platformInput) platformInput.required = false;
            if (locationInput) locationInput.required = true;
            clearError('platformError');
        });
    }

    // ── Upload modal: Fee toggle ─────────────────────────────────────
    const feeFree = document.getElementById('feeFree');
    const feePaid = document.getElementById('feePaid');
    const priceField = document.getElementById('priceField');
    const priceInput = document.getElementById('price');

    function toggleUploadPriceField() {
        if (!priceField) return;
        if (feePaid && feePaid.checked) {
            priceField.classList.add('active');
            if (priceInput) priceInput.required = true;
        } else {
            priceField.classList.remove('active');
            if (priceInput) { priceInput.required = false; priceInput.value = ''; }
            clearError('priceError');
        }
    }

    if (feeFree)  feeFree.addEventListener('change', toggleUploadPriceField);
    if (feePaid)  feePaid.addEventListener('change', toggleUploadPriceField);
    toggleUploadPriceField();

    // ── Edit modal: Fee toggle ───────────────────────────────────────
    const editFeeFree = document.getElementById('editFeeFree');
    const editFeePaid = document.getElementById('editFeePaid');
    const editPriceField = document.getElementById('editPriceField');
    const editPriceInput = document.getElementById('editPrice');

    function toggleEditPriceField() {
        if (!editPriceField) return;
        if (editFeePaid && editFeePaid.checked) {
            editPriceField.classList.add('active');
            if (editPriceInput) editPriceInput.required = true;
        } else {
            editPriceField.classList.remove('active');
            if (editPriceInput) { editPriceInput.required = false; editPriceInput.value = ''; }
            clearError('editPriceError');
        }
    }

    if (editFeeFree) editFeeFree.addEventListener('change', toggleEditPriceField);
    if (editFeePaid) editFeePaid.addEventListener('change', toggleEditPriceField);

    // Date/Time Calculations (Upload Form)
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const startTime = document.getElementById('startTime');
    const endTime = document.getElementById('endTime');
    const enrollmentDeadline = document.getElementById('enrollmentDeadline');

    if (startDate) {
        const today = new Date().toISOString().split('T')[0];
        startDate.setAttribute('min', today);
        
        startDate.addEventListener('change', function() {
            calculateWeeks();
            if (endDate) endDate.min = startDate.value;
            if (enrollmentDeadline) enrollmentDeadline.max = startDate.value;
        });
    }

    if (endDate) endDate.addEventListener('change', calculateWeeks);
    if (startTime) startTime.addEventListener('change', calculateDuration);
    if (endTime) endTime.addEventListener('change', calculateDuration);

    // Character Counter (Upload Form)
    const descriptionTextarea = document.getElementById('eventDescription');
    const descCount = document.getElementById('descCount');

    if (descriptionTextarea && descCount) {
        descriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            descCount.textContent = `${count} / 1000 characters`;
            descCount.style.color = count > 1000 ? '#ef4444' : '#6b7280';
        });
    }

    // Form Submission (Upload)
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateForm()) return;

            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            }

            const formData = new FormData(uploadForm);

            try {
                const url = typeof storeRoute !== 'undefined' ? storeRoute : '/class-event';
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const contentType = response.headers.get('content-type');
                let data;
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                }

                if (response.ok && data.success) {
                    closeModal();
                    setTimeout(() => showSuccess(data.message || 'Class/Event created successfully!'), 400);
                    setTimeout(() => window.location.reload(), 3750);
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorElementId = key.replace(/_/g, '') + 'Error';
                            showErrorMessage(errorElementId, data.errors[key][0]);
                        });
                    } else {
                        showError(data.message || 'An error occurred. Please try again.');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showError('An error occurred: ' + error.message);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        });
    }

    // Edit Form Submission
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const classId = document.getElementById('editClassId').value;

            // Client-side deadline validation
            const editDeadline = document.getElementById('editEnrollmentDeadline');
            const editStartDate = document.getElementById('editStartDate');
            if (editDeadline && editDeadline.value && editStartDate && editStartDate.value) {
                if (editDeadline.value > editStartDate.value) {
                    showError('Enrollment deadline must be on or before the start date');
                    return;
                }
            }

            // Client-side price validation
            const editIsPaid = document.querySelector('#editModal input[name="is_paid"]:checked');
            if (editIsPaid && editIsPaid.value === '1') {
                const ep = document.getElementById('editPrice');
                if (!ep || !ep.value || parseFloat(ep.value) <= 0) {
                    showErrorMessage('editPriceError', 'Please enter a valid price');
                    return;
                }
            }
            
            try {
                const response = await fetch(`/class-event/${classId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeEditModal();
                    setTimeout(() => showSuccess('Class/Event updated successfully!'), 400);
                    setTimeout(() => window.location.reload(), 3750);
                } else {
                    showError(data.message || 'Failed to update class/event');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Failed to update class/event');
            }
        });
    }
    
    // Media type toggle for edit form
    const editMediaOnline = document.getElementById('editMediaOnline');
    const editMediaPhysical = document.getElementById('editMediaPhysical');
    
    if (editMediaOnline && editMediaPhysical) {
        editMediaOnline.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('editPlatformField').classList.add('active');
                document.getElementById('editLocationField').classList.remove('active');
                document.getElementById('editPlatform').required = true;
                document.getElementById('editLocation').required = false;
            }
        });
        
        editMediaPhysical.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('editLocationField').classList.add('active');
                document.getElementById('editPlatformField').classList.remove('active');
                document.getElementById('editLocation').required = true;
                document.getElementById('editPlatform').required = false;
            }
        });
    }
    
    // Date/Time Calculations (Edit Form)
    const editStartDate = document.getElementById('editStartDate');
    const editEndDate = document.getElementById('editEndDate');
    const editStartTime = document.getElementById('editStartTime');
    const editEndTime = document.getElementById('editEndTime');
    const editEnrollmentDeadline = document.getElementById('editEnrollmentDeadline');

    if (editStartDate) {
        editStartDate.addEventListener('change', function() {
            calculateEditWeeks();
            if (editEndDate) editEndDate.min = editStartDate.value;
            if (editEnrollmentDeadline) editEnrollmentDeadline.max = editStartDate.value;
        });
    }

    if (editEndDate) editEndDate.addEventListener('change', calculateEditWeeks);
    if (editStartTime) editStartTime.addEventListener('change', calculateEditDuration);
    if (editEndTime) editEndTime.addEventListener('change', calculateEditDuration);

    // Character Counter (Edit Form)
    const editDescriptionTextarea = document.getElementById('editEventDescription');
    const editDescCountEl = document.getElementById('editDescCount');

    if (editDescriptionTextarea && editDescCountEl) {
        editDescriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            editDescCountEl.textContent = `${count} / 1000 characters`;
            editDescCountEl.style.color = count > 1000 ? '#ef4444' : '#6b7280';
        });
    }
    
    // Edit image upload preview
    const editPosterImage = document.getElementById('editPosterImage');
    const editFilePreview = document.getElementById('editFilePreview');
    const editPreviewImage = document.getElementById('editPreviewImage');
    const editFileNameText = document.getElementById('editFileNameText');
    const editRemoveFile = document.getElementById('editRemoveFile');
    const editUploadArea = document.getElementById('editUploadArea');
    
    if (editPosterImage && editUploadArea) {
        editUploadArea.addEventListener('click', function(e) {
            if (e.target !== editRemoveFile && !editRemoveFile.contains(e.target)) {
                editPosterImage.click();
            }
        });
        
        editPosterImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                editFileNameText.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    editPreviewImage.src = e.target.result;
                    editFilePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (editRemoveFile) {
        editRemoveFile.addEventListener('click', function(e) {
            e.stopPropagation();
            editPosterImage.value = '';
            editFilePreview.style.display = 'none';
        });
    }

    // ── Upload modal: Require Enrollment Form toggle ─────────────────
    const requireFormNo  = document.getElementById('requireFormNo');
    const requireFormYes = document.getElementById('requireFormYes');
    const formUrlField   = document.getElementById('formUrlField');

    function toggleFormUrlField() {
        if (!formUrlField) return;
        if (requireFormYes && requireFormYes.checked) {
            formUrlField.style.display = 'block';
        } else {
            formUrlField.style.display = 'none';
            const urlInput = document.getElementById('enrollmentFormUrl');
            if (urlInput) urlInput.value = '';
        }
    }

    if (requireFormNo)  requireFormNo.addEventListener('change', toggleFormUrlField);
    if (requireFormYes) requireFormYes.addEventListener('change', toggleFormUrlField);
    toggleFormUrlField();

    // ── Edit modal: Require Enrollment Form toggle ───────────────────
    const editRequireFormNo  = document.getElementById('editRequireFormNo');
    const editRequireFormYes = document.getElementById('editRequireFormYes');
    const editFormUrlField   = document.getElementById('editFormUrlField');

    function toggleEditFormUrlField() {
        if (!editFormUrlField) return;
        if (editRequireFormYes && editRequireFormYes.checked) {
            editFormUrlField.style.display = 'block';
        } else {
            editFormUrlField.style.display = 'none';
        }
    }

    if (editRequireFormNo)  editRequireFormNo.addEventListener('change', toggleEditFormUrlField);
    if (editRequireFormYes) editRequireFormYes.addEventListener('change', toggleEditFormUrlField);
    toggleEditFormUrlField();

    // Close modals on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeEditModal();
        }
    });

    console.log('=== Initialization Complete ===');
});

// Export functions
window.viewClass = viewClass;
window.saveClass = saveClass;
window.editClass = editClass;
window.confirmDelete = confirmDelete;
window.closeEditModal = closeEditModal;
window.openModal = openModal;
window.closeModal = closeModal;

console.log('ClassEvent.js loaded successfully');