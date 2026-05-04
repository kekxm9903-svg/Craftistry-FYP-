// uploadForm.js — Combined JS for Demo & Sell Upload Pages (Multi-image)

// ══════════════════════════════
// FILE STORES (per page)
// ══════════════════════════════

const demoFiles = [];
const sellFiles = [];

document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('demoDropZone')) {
        setupMultiDropZone('demoDropZone', 'demoImage', 'demoDropInner', 'demoPreviewGrid', demoFiles);
        setupSubmitLoader('demoUploadForm', 'demoSubmitBtn', '<i class="bi bi-arrow-repeat"></i> Uploading...', demoFiles, 'demoImage');
    }

    if (document.getElementById('sellDropZone')) {
        setupMultiDropZone('sellDropZone', 'sellImage', 'sellDropInner', 'sellPreviewGrid', sellFiles);
        setupSubmitLoader('sellUploadForm', 'sellSubmitBtn', '<i class="bi bi-arrow-repeat"></i> Listing...', sellFiles, 'sellImage');
    }

    setupCounter('demoTitle',       'titleCounter',    255);
    setupCounter('demoDescription', 'descCounter',     1000);
    setupCounter('sellProductName', 'nameCounter',     255);
    setupCounter('sellProductDesc', 'sellDescCounter', 2000);

    ['sellPrice', 'sellShipping'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updatePricingPreview);
    });

    // Also update promo preview when price changes
    ['sellPrice', 'editSellPrice'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updatePromoPreview);
    });

    ['bulkMinQty', 'bulkDiscount'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateBulkPreview);
    });
});


// ══════════════════════════════
// MULTI-IMAGE DROP ZONE
// ══════════════════════════════

function setupMultiDropZone(zoneId, inputId, innerId, gridId, filesArr) {
    const zone  = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    if (!zone || !input) return;

    input.addEventListener('change', function () {
        addFiles(Array.from(this.files), filesArr, gridId, zoneId, innerId, inputId);
        // DO NOT reset this.value — keep files in input for native form submission
    });

    zone.addEventListener('dragover',  (e) => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('dragover');
        addFiles(Array.from(e.dataTransfer.files), filesArr, gridId, zoneId, innerId, inputId);
    });
}

function addFiles(newFiles, filesArr, gridId, zoneId, innerId, inputId) {
    const valid = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    let rejected = 0;

    newFiles.forEach(file => {
        if (!valid.includes(file.type) || file.size > 5 * 1024 * 1024) {
            rejected++;
            return;
        }
        if (!filesArr.some(f => f.name === file.name && f.size === file.size)) {
            filesArr.push(file);
        }
    });

    if (rejected > 0) alert(`${rejected} file(s) skipped — must be JPG/PNG/GIF/WEBP and under 5MB each.`);

    renderPreviews(filesArr, gridId, zoneId, innerId, inputId);
    syncFilesToInput(filesArr, inputId);
}

function syncFilesToInput(filesArr, inputId) {
    // Sync the full filesArr back into the <input> via DataTransfer
    // so the native form POST sends all files under images[]
    try {
        const dt    = new DataTransfer();
        filesArr.forEach(f => dt.items.add(f));
        const input = document.getElementById(inputId);
        if (input) input.files = dt.files;
    } catch (e) {
        console.warn('DataTransfer not supported:', e);
    }
}

function renderPreviews(filesArr, gridId, zoneId, innerId, inputId) {
    const grid  = document.getElementById(gridId);
    const zone  = document.getElementById(zoneId);
    if (!grid || !zone) return;

    // Remove old count label
    const oldCount = grid.nextElementSibling;
    if (oldCount?.classList.contains('multi-preview-count')) oldCount.remove();

    grid.innerHTML = '';

    if (filesArr.length === 0) {
        zone.classList.remove('has-files');
        updateDropLabel(innerId, 0);
        return;
    }

    zone.classList.add('has-files');
    updateDropLabel(innerId, filesArr.length);

    filesArr.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const isMain = index === 0;
            const item   = document.createElement('div');
            item.className     = 'multi-preview-item';
            item.dataset.index = index;
            item.innerHTML = `
                <img src="${e.target.result}" alt="Image ${index + 1}">
                <span class="item-badge ${isMain ? 'main-badge' : ''}">
                    ${isMain ? '<i class="bi bi-star-fill"></i> Main' : `<i class="bi bi-image"></i> #${index + 1}`}
                </span>
                <button type="button" class="item-remove"
                        onclick="removeFile(${index},'${gridId}','${zoneId}','${innerId}','${inputId}')">
                    <i class="bi bi-x"></i>
                </button>`;
            grid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });

    const countEl     = document.createElement('div');
    countEl.className = 'multi-preview-count';
    countEl.innerHTML = `<i class="bi bi-images"></i> ${filesArr.length} image${filesArr.length > 1 ? 's' : ''} selected${filesArr.length > 1 ? ' — first image is the main cover' : ''}`;
    grid.after(countEl);
}

function updateDropLabel(innerId, count) {
    const inner = document.getElementById(innerId);
    if (!inner) return;
    const title = inner.querySelector('.drop-zone-title');
    const sub   = inner.querySelector('.drop-zone-sub');
    if (!title || !sub) return;
    if (count === 0) {
        title.textContent = 'Drop images here';
        sub.innerHTML     = 'or <span class="drop-browse">browse files</span>';
    } else {
        title.textContent = `${count} image${count > 1 ? 's' : ''} selected`;
        sub.innerHTML     = 'Drop more or <span class="drop-browse">browse to add</span>';
    }
}

function removeFile(index, gridId, zoneId, innerId, inputId) {
    const filesArr = inputId === 'demoImage' ? demoFiles : sellFiles;
    filesArr.splice(index, 1);
    renderPreviews(filesArr, gridId, zoneId, innerId, inputId);
    syncFilesToInput(filesArr, inputId);
}


// ══════════════════════════════
// SUBMIT HANDLER
// ══════════════════════════════

function setupSubmitLoader(formId, btnId, loadingHTML, filesArr, inputId) {
    const form = document.getElementById(formId);
    const btn  = document.getElementById(btnId);
    if (!form || !btn) return;

    form.addEventListener('submit', function (e) {
        if (filesArr.length === 0) {
            e.preventDefault();
            alert('Please select at least one image before submitting.');
            return;
        }
        // Final sync right before submit
        syncFilesToInput(filesArr, inputId);
        btn.disabled  = true;
        btn.innerHTML = loadingHTML;
    });
}


// ══════════════════════════════
// SHARED UTILITIES
// ══════════════════════════════

function setupCounter(inputId, counterId, max) {
    const input   = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    const update = () => counter.textContent = `${input.value.length} / ${max}`;
    input.addEventListener('input', update);
    update();
}




// ══════════════════════════════
// PROMOTION FUNCTIONS
// ══════════════════════════════

function togglePromoFields(checkbox) {
    const fields = document.getElementById('promoFields');
    if (!fields) return;
    fields.style.display = checkbox.checked ? 'block' : 'none';
    if (!checkbox.checked) {
        const disc = document.getElementById('promoDiscount');
        if (disc) disc.value = '';
        const preview = document.getElementById('promoPricePreview');
        if (preview) preview.style.display = 'none';
    } else {
        updatePromoPreview();
    }
}

function updatePromoPreview() {
    // Get price from either upload page or edit page field IDs
    const priceEl    = document.getElementById('sellPrice') || document.getElementById('editSellPrice');
    const discountEl = document.getElementById('promoDiscount');
    const preview    = document.getElementById('promoPricePreview');
    const origEl     = document.getElementById('promoOriginalPrice');
    const finalEl    = document.getElementById('promoFinalPrice');
    const savingEl   = document.getElementById('promoSaving');

    if (!discountEl || !preview) return;

    const price    = parseFloat(priceEl?.value) || 0;
    const discount = parseFloat(discountEl.value) || 0;

    if (price > 0 && discount > 0 && discount < 100) {
        const finalPrice = price * (1 - discount / 100);
        const saving     = price - finalPrice;

        if (origEl)   origEl.textContent   = `RM ${price.toFixed(2)}`;
        if (finalEl)  finalEl.textContent  = `RM ${finalPrice.toFixed(2)}`;
        if (savingEl) savingEl.textContent  = `Save ${discount}% (RM ${saving.toFixed(2)})`;

        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// ══════════════════════════════
// IMAGE MANAGER (edit pages)
// ══════════════════════════════

/**
 * Mark an existing image for deletion.
 * Adds a hidden input with the path so the controller knows to delete it.
 * Clicking again toggles the mark off (undo).
 */
function removeExistingImage(btn, formType) {
    const item       = btn.closest('.img-manager-item');
    const path       = item.dataset.path;
    const containerId = formType === 'demo' ? 'demoDeletedInputs' : 'sellDeletedInputs';
    const container  = document.getElementById(containerId);
    if (!container) return;

    const isMarked = item.classList.contains('marked-delete');

    if (isMarked) {
        // Undo — remove the hidden input
        item.classList.remove('marked-delete');
        const existing = container.querySelector(`input[value="${CSS.escape(path)}"]`);
        if (existing) existing.remove();
        btn.title = 'Remove';
    } else {
        // Mark for deletion
        item.classList.add('marked-delete');
        const hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'delete_images[]';
        hidden.value = path;
        container.appendChild(hidden);
        btn.title = 'Undo remove';
    }

    // Update main badge — first non-deleted existing item or first new item becomes main
    refreshMainBadge(formType);
}

/**
 * Add newly selected files to the grid as preview tiles.
 */
function addNewImagesToGrid(files, gridId, newFilesArr, inputId, formType) {
    const valid    = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    let   rejected = 0;

    files.forEach(file => {
        if (!valid.includes(file.type) || file.size > 5 * 1024 * 1024) {
            rejected++;
            return;
        }
        if (!newFilesArr.some(f => f.name === file.name && f.size === file.size)) {
            newFilesArr.push(file);
            renderNewImageTile(file, newFilesArr.length - 1, gridId, newFilesArr, inputId, formType);
        }
    });

    if (rejected > 0) alert(`${rejected} file(s) skipped — must be JPG/PNG/GIF/WEBP and under 5MB each.`);
    syncEditFiles(newFilesArr, inputId);
    refreshMainBadge(formType);
}

function renderNewImageTile(file, index, gridId, newFilesArr, inputId, formType) {
    const grid   = document.getElementById(gridId);
    const addBtn = grid.querySelector('.img-manager-add');
    if (!grid) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const item         = document.createElement('div');
        item.className     = 'img-manager-item';
        item.dataset.type  = 'new';
        item.dataset.index = index;
        item.innerHTML = `
            <img src="${e.target.result}" alt="New image">
            <span class="img-manager-badge"><i class="fas fa-plus-circle"></i> New</span>
            <button type="button" class="img-manager-remove"
                    onclick="removeNewImage(this, '${gridId}', '${inputId}')"
                    title="Remove">
                <i class="fas fa-times"></i>
            </button>`;
        // Insert before the Add tile
        if (addBtn) {
            grid.insertBefore(item, addBtn);
        } else {
            grid.appendChild(item);
        }
    };
    reader.readAsDataURL(file);
}

function removeNewImage(btn, gridId, inputId) {
    const item  = btn.closest('.img-manager-item');
    const index = parseInt(item.dataset.index);
    const newFilesArr = inputId === 'demoEditImage' ? demoEditNewFiles : sellEditNewFiles;

    newFilesArr.splice(index, 1);
    item.remove();

    // Re-index remaining new items
    const grid = document.getElementById(gridId);
    grid.querySelectorAll('.img-manager-item[data-type="new"]').forEach((el, i) => {
        el.dataset.index = i;
    });

    syncEditFiles(newFilesArr, inputId);
}

function syncEditFiles(newFilesArr, inputId) {
    try {
        const dt = new DataTransfer();
        newFilesArr.forEach(f => dt.items.add(f));
        const input = document.getElementById(inputId);
        if (input) input.files = dt.files;
    } catch (e) {
        console.warn('DataTransfer not supported:', e);
    }
}

function refreshMainBadge(formType) {
    const gridId = formType === 'demo' ? 'demoImgManagerGrid' : 'sellImgManagerGrid';
    const grid   = document.getElementById(gridId);
    if (!grid) return;

    // Remove all main badges first
    grid.querySelectorAll('.img-manager-badge.main-badge').forEach(b => b.remove());

    // First non-deleted item (existing or new) gets the main badge
    const firstActive = grid.querySelector('.img-manager-item:not(.marked-delete)');
    if (firstActive) {
        const badge       = document.createElement('span');
        badge.className   = 'img-manager-badge main-badge';
        badge.innerHTML   = '<i class="fas fa-star"></i> Main';
        firstActive.appendChild(badge);
    }
}

// ══════════════════════════════
// DEMO PAGE FUNCTIONS
// ══════════════════════════════

function toggleDemoSellFields() {
    const checkbox = document.getElementById('alsoSellCheckbox');
    const section  = document.getElementById('demoSellSection');
    if (!section) return;
    section.style.display = checkbox.checked ? 'block' : 'none';
    section.querySelectorAll('.sell-req').forEach(input => {
        input.required = checkbox.checked;
    });
    if (checkbox.checked) {
        setTimeout(() => section.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
    }
}


// ══════════════════════════════
// SELL PAGE FUNCTIONS
// ══════════════════════════════

function toggleSellFreeShipping(checkbox) {
    const input = document.getElementById('sellShipping');
    if (!input) return;
    if (checkbox.checked) {
        input.value            = '0';
        input.disabled         = true;
        input.style.background = '#f0fdf4';
        input.style.color      = '#16a34a';
    } else {
        input.disabled         = false;
        input.style.background = '';
        input.style.color      = '';
    }
    updatePricingPreview();
}

function updatePricingPreview() {
    const price    = parseFloat(document.getElementById('sellPrice')?.value)    || 0;
    const shipping = parseFloat(document.getElementById('sellShipping')?.value) || 0;
    const card     = document.getElementById('pricingPreviewCard');
    if (!card) return;
    if (price > 0) {
        card.style.display = 'block';
        document.getElementById('previewBasePrice').textContent = `RM ${price.toFixed(2)}`;
        document.getElementById('previewShipping').textContent  = shipping > 0 ? `RM ${shipping.toFixed(2)}` : 'Free';
        document.getElementById('previewTotal').textContent     = `RM ${(price + shipping).toFixed(2)}`;
    } else {
        card.style.display = 'none';
    }
    updateBulkPreview();
}

function toggleBulkFields(checkbox) {
    const fields = document.getElementById('bulkSellFields');
    if (!fields) return;
    fields.style.display = checkbox.checked ? 'block' : 'none';
    if (!checkbox.checked) {
        ['bulkMinQty', 'bulkDiscount'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        updateBulkPreview();
    }
}

function updateBulkPreview() {
    const qty      = parseInt(document.getElementById('bulkMinQty')?.value)     || 0;
    const discount = parseFloat(document.getElementById('bulkDiscount')?.value) || 0;
    const valid    = qty >= 2 && discount > 0 && discount < 100;

    const strip     = document.getElementById('bulkPreviewStrip');
    const stripText = document.getElementById('bulkPreviewText');
    if (strip && stripText) {
        stripText.textContent = `Buy ${qty} or more and get ${discount}% off each item`;
        strip.style.display   = valid ? 'flex' : 'none';
    }
    const sideRow   = document.getElementById('bulkPreviewRow');
    const sideLabel = document.getElementById('bulkPreviewLabel');
    if (sideRow && sideLabel) {
        sideLabel.textContent = `Bulk: ${discount}% off ≥${qty} pcs`;
        sideRow.style.display = valid ? 'flex' : 'none';
    }
}