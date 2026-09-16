/* Extraido de views/pages/management/products/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var currentPage = 1;
var pageSize = 8;
var filteredRows = [];

function applyProductFilters() {
    const searchInput = document.getElementById('filter-search');
    const typeSelect = document.getElementById('filter-type');
    const usageSelect = document.getElementById('filter-usage');
    const unitSelect = document.getElementById('filter-unit');
    const capInput = document.getElementById('filter-capacity');
    const priceMinInput = document.getElementById('filter-price-min');
    const priceMaxInput = document.getElementById('filter-price-max');
    const statusSelect = document.getElementById('filter-status');
    const resetBtn = document.getElementById('btn-reset-filters');
    const tbody = document.getElementById('products-table-body');

    if (!tbody) return;

    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const selectedType = typeSelect ? typeSelect.value : '';
    const selectedUsage = usageSelect ? usageSelect.value : '';
    const selectedUnit = unitSelect ? unitSelect.value.toLowerCase() : '';
    const typedCapacity = capInput && capInput.value !== '' ? parseFloat(capInput.value) : null;
    const minPrice = priceMinInput && priceMinInput.value !== '' ? parseFloat(priceMinInput.value) : null;
    const maxPrice = priceMaxInput && priceMaxInput.value !== '' ? parseFloat(priceMaxInput.value) : null;
    const selectedStatus = statusSelect ? statusSelect.value : '';

    const hasFilter = query !== '' || selectedType !== '' || selectedUsage !== '' || selectedUnit !== '' || typedCapacity !== null || minPrice !== null || maxPrice !== null || selectedStatus !== '';
    if (resetBtn) {
        if (hasFilter) {
            resetBtn.style.visibility = 'visible';
            resetBtn.style.opacity = '1';
        } else {
            resetBtn.style.visibility = 'hidden';
            resetBtn.style.opacity = '0';
        }
    }

    const allRows = Array.from(tbody.querySelectorAll('.product-row'));
    filteredRows = [];

    // Remove empty state se existir
    const existingEmpty = document.getElementById('dynamic-empty-row');
    if (existingEmpty) existingEmpty.remove();

    allRows.forEach(row => {
        const nameText = row.dataset.name || '';
        const rowType = row.dataset.type || '';
        const rowUsage = row.dataset.usage || '';
        const rowUnit = (row.dataset.unit || '').toLowerCase();
        const rowCapacity = parseFloat(row.dataset.capacity || '0');
        const rowPrice = parseFloat(row.dataset.price || '0');
        const rowStatus = row.dataset.status || '';

        const matchQuery = query === '' || nameText.includes(query);
        const matchType = selectedType === '' || rowType === selectedType;
        const matchUsage = selectedUsage === '' || rowUsage === selectedUsage;
        const matchUnit = selectedUnit === '' || rowUnit === selectedUnit;
        const matchCapacity = typedCapacity === null || rowCapacity === typedCapacity;
        
        let matchPrice = true;
        if (minPrice !== null && rowPrice < minPrice) matchPrice = false;
        if (maxPrice !== null && rowPrice > maxPrice) matchPrice = false;

        const matchStatus = selectedStatus === '' || rowStatus === selectedStatus;

        if (matchQuery && matchType && matchUsage && matchUnit && matchCapacity && matchPrice && matchStatus) {
            filteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    if (filteredRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'dynamic-empty-row';
        emptyRow.style.height = '452px';
        emptyRow.innerHTML = `
            <td colspan="8" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                Nenhum produto encontrado com os filtros selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    currentPage = 1;
    renderPagination();
}

function renderPagination() {
    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;
    const countDisplay = document.getElementById('product-result-count');
    const pageText = document.getElementById('current-page-text');
    const totalPagesText = document.getElementById('total-pages-text');
    const prevBtn = document.getElementById('btn-prev-page');
    const nextBtn = document.getElementById('btn-next-page');
    const pageNumbers = document.getElementById('page-numbers');

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = startIdx + pageSize;

    filteredRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    if (pageText) pageText.textContent = currentPage;
    if (totalPagesText) totalPagesText.textContent = totalPages;

    if (countDisplay) {
        if (totalItems === 0) {
            countDisplay.textContent = 'Mostrando 0 de 0 produtos';
        } else {
            const visibleCountOnPage = Math.min(pageSize, totalItems - startIdx);
            countDisplay.textContent = `Mostrando ${visibleCountOnPage} de ${totalItems} produtos`;
        }
    }

    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                currentPage = i;
                renderPagination();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeProductPage(delta) {
    currentPage += delta;
    renderPagination();
}

function resetAllFilters() {
    const searchInput = document.getElementById('filter-search');
    const typeSelect = document.getElementById('filter-type');
    const usageSelect = document.getElementById('filter-usage');
    const unitSelect = document.getElementById('filter-unit');
    const capInput = document.getElementById('filter-capacity');
    const priceMinInput = document.getElementById('filter-price-min');
    const priceMaxInput = document.getElementById('filter-price-max');
    const statusSelect = document.getElementById('filter-status');

    if (searchInput) searchInput.value = '';
    if (typeSelect) typeSelect.value = '';
    if (usageSelect) usageSelect.value = '';
    if (unitSelect) unitSelect.value = '';
    if (capInput) capInput.value = '';
    if (priceMinInput) priceMinInput.value = '';
    if (priceMaxInput) priceMaxInput.value = '';
    if (statusSelect) statusSelect.value = '';

    applyProductFilters();
}

function initProductPage() {
    const searchInput = document.getElementById('filter-search');
    const typeSelect = document.getElementById('filter-type');
    const usageSelect = document.getElementById('filter-usage');
    const unitSelect = document.getElementById('filter-unit');
    const capInput = document.getElementById('filter-capacity');
    const priceMinInput = document.getElementById('filter-price-min');
    const priceMaxInput = document.getElementById('filter-price-max');
    const statusSelect = document.getElementById('filter-status');

    if (searchInput) searchInput.addEventListener('input', applyProductFilters);
    if (typeSelect) typeSelect.addEventListener('change', applyProductFilters);
    if (usageSelect) usageSelect.addEventListener('change', applyProductFilters);
    if (unitSelect) unitSelect.addEventListener('change', applyProductFilters);
    if (capInput) capInput.addEventListener('input', applyProductFilters);
    if (priceMinInput) priceMinInput.addEventListener('input', applyProductFilters);
    if (priceMaxInput) priceMaxInput.addEventListener('input', applyProductFilters);
    if (statusSelect) statusSelect.addEventListener('change', applyProductFilters);

    applyProductFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductPage);
} else {
    initProductPage();
}
