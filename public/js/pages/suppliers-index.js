/* Extraido de views/pages/management/suppliers/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var supplierCurrentPage = 1;
var supplierPageSize = 8;
var supplierFilteredRows = [];

function applySupplierFilters() {
    const searchInput = document.getElementById('filter-supplier-search');
    const typeSelect = document.getElementById('filter-supplier-type');
    const stateSelect = document.getElementById('filter-supplier-state');
    const citySelect = document.getElementById('filter-supplier-city');
    const resetBtn = document.getElementById('btn-reset-filters');
    const tbody = document.getElementById('suppliers-table-body');

    if (!tbody) return;

    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const selectedType = typeSelect ? typeSelect.value : '';
    const selectedState = stateSelect ? stateSelect.value : '';
    const selectedCity = citySelect ? citySelect.value.toLowerCase() : '';

    const hasFilter = query !== '' || selectedType !== '' || selectedState !== '' || selectedCity !== '';
    if (resetBtn) {
        if (hasFilter) {
            resetBtn.style.visibility = 'visible';
            resetBtn.style.opacity = '1';
        } else {
            resetBtn.style.visibility = 'hidden';
            resetBtn.style.opacity = '0';
        }
    }

    const allRows = Array.from(tbody.querySelectorAll('.supplier-row'));
    supplierFilteredRows = [];

    // Remove empty state se existir
    const existingEmpty = document.getElementById('supplier-dynamic-empty-row');
    if (existingEmpty) existingEmpty.remove();

    allRows.forEach(row => {
        const nameText = row.dataset.name || '';
        const rowType = row.dataset.type || '';
        const rowState = row.dataset.state || '';
        const rowCity = row.dataset.city || '';

        const matchQuery = query === '' || nameText.includes(query);
        const matchType = selectedType === '' || rowType.includes(selectedType);
        const matchState = selectedState === '' || rowState === selectedState;
        const matchCity = selectedCity === '' || rowCity.includes(selectedCity);

        if (matchQuery && matchType && matchState && matchCity) {
            supplierFilteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    if (supplierFilteredRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'supplier-dynamic-empty-row';
        emptyRow.style.height = '452px';
        emptyRow.innerHTML = `
            <td colspan="7" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                Nenhum fornecedor encontrado com os filtros selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    supplierCurrentPage = 1;
    renderSupplierPagination();
}

function renderSupplierPagination() {
    const totalItems = supplierFilteredRows.length;
    const totalPages = Math.ceil(totalItems / supplierPageSize) || 1;
    const countDisplay = document.getElementById('supplier-result-count');
    const pageText = document.getElementById('supplier-current-page-text');
    const totalPagesText = document.getElementById('supplier-total-pages-text');
    const prevBtn = document.getElementById('supplier-btn-prev');
    const nextBtn = document.getElementById('supplier-btn-next');
    const pageNumbers = document.getElementById('supplier-page-numbers');

    if (supplierCurrentPage > totalPages) supplierCurrentPage = totalPages;
    if (supplierCurrentPage < 1) supplierCurrentPage = 1;

    const startIdx = (supplierCurrentPage - 1) * supplierPageSize;
    const endIdx = startIdx + supplierPageSize;

    supplierFilteredRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    if (pageText) pageText.textContent = supplierCurrentPage;
    if (totalPagesText) totalPagesText.textContent = totalPages;

    if (countDisplay) {
        if (totalItems === 0) {
            countDisplay.textContent = 'Mostrando 0 de 0 fornecedores';
        } else {
            const visibleCountOnPage = Math.min(supplierPageSize, totalItems - startIdx);
            countDisplay.textContent = `Mostrando ${visibleCountOnPage} de ${totalItems} fornecedores`;
        }
    }

    if (prevBtn) prevBtn.disabled = supplierCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = supplierCurrentPage >= totalPages;

    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === supplierCurrentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                supplierCurrentPage = i;
                renderSupplierPagination();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeSupplierPage(delta) {
    supplierCurrentPage += delta;
    renderSupplierPagination();
}

function resetAllSupplierFilters() {
    const searchInput = document.getElementById('filter-supplier-search');
    const typeSelect = document.getElementById('filter-supplier-type');
    const stateSelect = document.getElementById('filter-supplier-state');
    const citySelect = document.getElementById('filter-supplier-city');

    if (searchInput) searchInput.value = '';
    if (typeSelect) typeSelect.value = '';
    if (stateSelect) stateSelect.value = '';
    if (citySelect) citySelect.value = '';

    applySupplierFilters();
}

function initSupplierPage() {
    const searchInput = document.getElementById('filter-supplier-search');
    const typeSelect = document.getElementById('filter-supplier-type');
    const stateSelect = document.getElementById('filter-supplier-state');
    const citySelect = document.getElementById('filter-supplier-city');

    if (searchInput) searchInput.addEventListener('input', applySupplierFilters);
    if (typeSelect) typeSelect.addEventListener('change', applySupplierFilters);
    if (stateSelect) stateSelect.addEventListener('change', applySupplierFilters);
    if (citySelect) citySelect.addEventListener('change', applySupplierFilters);

    applySupplierFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSupplierPage);
} else {
    initSupplierPage();
}
