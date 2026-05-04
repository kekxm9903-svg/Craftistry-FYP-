// uploadForm.js — Combined JS for Demo & Sell Upload Pages (Multi-image)

// ══════════════════════════════
// SHARED FILE STORES
// ══════════════════════════════

const demoFiles = [];
const sellFiles = [];

document.addEventListener('DOMContentLoaded', function () {

    // ── Demo page setup ──
    if (document.getElementById('demoDropZone')) {
        setupMultiDropZone('demoDropZone', 'demoImage', 'demoDropInner', 'demoPreviewGrid', demoFiles);
    }

    // ── Sell page setup ──
    if (document.getElementById('sellDropZone')) {
        setupMultiDropZone('sellDropZone', 'sellImage', 'sellDropInner', 'sellPreviewGrid', sellFiles);
    }

    // ── Character counters ──
    setupCounter('demoTitle',       'titleCounter',    255);
    setupCounter('demoDescription', 'descCounter',     1000);
    setupCounter('sellProductName', 'nameCounter',     255);
    setupCounter('sellProductDesc', 'sellDescCounter', 2000);

    // ── Submit loaders ──
    setupSubmitLoader('demoUploadForm', 'demoSubmitBtn', '<i class="bi bi-arrow-repeat"></i> Uploading...');
    setupSubmitLoader('sellUploadForm', 'sellSubmitBtn', '<i class="bi bi-arrow-repeat"></i> Listing...');

    // ── Sell pricing listeners ──
    ['sellPrice', 'sellShipping'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updatePricingPreview);
    });

    // ── Bulk preview listeners ──
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
        this.value = '';
    });

    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.classList.add('dragover');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('dragover');
        addFiles(Array.from(e.dataTransfer.files), filesArr, gridId, zoneId, innerId, inputId);
    });
}

function addFiles(newFiles, filesArr, gridId, zoneId, innerId, inputId) {
    const valid   = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    let   rejected = 0;

    newFiles.forEach(file => {
        if (!valid.includes(file.type) || file.size > 5 * 1024 * 1024) {
            rejected++;
            return;
        }
        // skip duplicates
        if (!filesArr.some(f => f.name === file.name && f.size === file.size)) {
            filesArr.push(file);
        }
    });

    if (rejected > 0) {
        alert(`${rejected} file(s) skipped — must be JPG/PNG/GIF/WEBP and under 5MB each.`);
    }

    renderPreviews(filesArr, gridId, zoneId, innerId, inputId);
    syncInput(filesArr, inputId);
}

function renderPreviews(filesArr, gridId, zoneId, innerId, inputId) {
    const grid  = document.getElementById(gridId);
    const zone  = document.getElementById(zoneId);
    const inner = document.getElementById(innerId);
    if (!grid) return;

    // Clear old count label
    const oldCount = grid.nextElementSibling;
    if (oldCount && oldCount.classList.contains('multi-preview-count')) oldCount.remove();

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
            item.className      = 'multi-preview-item';
            item.dataset.index  = index;
            item.innerHTML = `
                <img src="${e.target.result}" alt="Image ${index + 1}">
                <span class="item-badge ${isMain ? 'main-badge' : ''}">
                    ${isMain
                        ? '<i class="bi bi-star-fill"></i> Main'
                        : `<i class="bi bi-image"></i> #${index + 1}`}
                </span>
                <button type="button" class="item-remove"
                        onclick="removeFile(${index},'${gridId}','${zoneId}','${innerId}','${inputId}')">
                    <i class="bi bi-x"></i>
                </button>`;
            grid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });

    // Count label
    const countEl   = document.createElement('div');
    countEl.className = 'multi-preview-count';
    countEl.innerHTML = `<i class="bi bi-images"></i>
        ${filesArr.length} image${filesArr.length > 1 ? 's' : ''} selected
        ${filesArr.length > 1 ? '— first image is the main cover' : ''}`;
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
    syncInput(filesArr, inputId);
}

function syncInput(filesArr, inputId) {
    try {
        const dt = new DataTransfer();
        filesArr.forEach(f => dt.items.add(f));
        const input = document.getElementById(inputId);
        if (input) input.files = dt.files;
    } catch (e) {
        console.warn('DataTransfer assign not supported:', e);
    }
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

function setupSubmitLoader(formId, btnId, loadingHTML) {
    const form = document.getElementById(formId);
    const btn  = document.getElementById(btnId);
    if (!form || !btn) return;
    form.addEventListener('submit', function (e) {
        // Use the in-memory array as the source of truth (reliable across all browsers)
        const filesArr = formId === 'demoUploadForm' ? demoFiles : sellFiles;
        if (filesArr.length === 0) {
            e.preventDefault();
            alert('Please select at least one image before submitting.');
            return;
        }
        // Re-sync one final time before submit in case DataTransfer was lost
        const inputId = formId === 'demoUploadForm' ? 'demoImage' : 'sellImage';
        syncInput(filesArr, inputId);
        btn.disabled  = true;
        btn.innerHTML = loadingHTML;
    });
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