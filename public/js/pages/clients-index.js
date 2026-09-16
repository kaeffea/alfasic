/* Extraido de views/pages/management/clients/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var clientCurrentPage = 1;
var clientPageSize = 8;
var clientFilteredRows = [];

function applyClientFilters() {
    const searchInput = document.getElementById('filter-client-search');
    const personSelect = document.getElementById('filter-client-person');
    const segmentSelect = document.getElementById('filter-client-segment');
    const citySelect = document.getElementById('filter-client-city');
    const statusSelect = document.getElementById('filter-client-status');
    const resetBtn = document.getElementById('btn-reset-filters');
    const tbody = document.getElementById('clients-table-body');

    if (!tbody) return;

    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const selectedPerson = personSelect ? personSelect.value : '';
    const selectedSegment = segmentSelect ? segmentSelect.value : '';
    const selectedCity = citySelect ? citySelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value : '';

    const hasFilter = query !== '' || selectedPerson !== '' || selectedSegment !== '' || selectedCity !== '' || selectedStatus !== '';
    if (resetBtn) {
        if (hasFilter) {
            resetBtn.style.visibility = 'visible';
            resetBtn.style.opacity = '1';
        } else {
            resetBtn.style.visibility = 'hidden';
            resetBtn.style.opacity = '0';
        }
    }

    const allRows = Array.from(tbody.querySelectorAll('.client-row'));
    clientFilteredRows = [];

    // Remove empty state se existir
    const existingEmpty = document.getElementById('client-dynamic-empty-row');
    if (existingEmpty) existingEmpty.remove();

    allRows.forEach(row => {
        const nameText = row.dataset.name || '';
        const rowPerson = row.dataset.person || '';
        const rowSegment = row.dataset.segment || '';
        const rowCity = row.dataset.city || '';
        const rowOrders = row.dataset.orders || '0';

        const matchQuery = query === '' || nameText.includes(query);
        const matchPerson = selectedPerson === '' || rowPerson === selectedPerson;
        const matchSegment = selectedSegment === '' || rowSegment.includes(selectedSegment);
        const matchCity = selectedCity === '' || rowCity.includes(selectedCity);
        
        let matchStatus = true;
        if (selectedStatus === 'with_orders') matchStatus = rowOrders === '1';
        if (selectedStatus === 'no_orders') matchStatus = rowOrders === '0';

        if (matchQuery && matchPerson && matchSegment && matchCity && matchStatus) {
            clientFilteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    if (clientFilteredRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'client-dynamic-empty-row';
        emptyRow.style.height = '452px';
        emptyRow.innerHTML = `
            <td colspan="7" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                Nenhum cliente encontrado com os filtros selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    clientCurrentPage = 1;
    renderClientPagination();
}

function renderClientPagination() {
    const totalItems = clientFilteredRows.length;
    const totalPages = Math.ceil(totalItems / clientPageSize) || 1;
    const countDisplay = document.getElementById('client-result-count');
    const pageText = document.getElementById('client-current-page-text');
    const totalPagesText = document.getElementById('client-total-pages-text');
    const prevBtn = document.getElementById('client-btn-prev');
    const nextBtn = document.getElementById('client-btn-next');
    const pageNumbers = document.getElementById('client-page-numbers');

    if (clientCurrentPage > totalPages) clientCurrentPage = totalPages;
    if (clientCurrentPage < 1) clientCurrentPage = 1;

    const startIdx = (clientCurrentPage - 1) * clientPageSize;
    const endIdx = startIdx + clientPageSize;

    clientFilteredRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    if (pageText) pageText.textContent = clientCurrentPage;
    if (totalPagesText) totalPagesText.textContent = totalPages;

    if (countDisplay) {
        if (totalItems === 0) {
            countDisplay.textContent = 'Mostrando 0 de 0 clientes';
        } else {
            const visibleCountOnPage = Math.min(clientPageSize, totalItems - startIdx);
            countDisplay.textContent = `Mostrando ${visibleCountOnPage} de ${totalItems} clientes`;
        }
    }

    if (prevBtn) prevBtn.disabled = clientCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = clientCurrentPage >= totalPages;

    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === clientCurrentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                clientCurrentPage = i;
                renderClientPagination();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeClientPage(delta) {
    clientCurrentPage += delta;
    renderClientPagination();
}

function resetAllClientFilters() {
    const searchInput = document.getElementById('filter-client-search');
    const personSelect = document.getElementById('filter-client-person');
    const segmentSelect = document.getElementById('filter-client-segment');
    const citySelect = document.getElementById('filter-client-city');
    const statusSelect = document.getElementById('filter-client-status');

    if (searchInput) searchInput.value = '';
    if (personSelect) personSelect.value = '';
    if (segmentSelect) segmentSelect.value = '';
    if (citySelect) citySelect.value = '';
    if (statusSelect) statusSelect.value = '';

    applyClientFilters();
}

function initClientPage() {
    const searchInput = document.getElementById('filter-client-search');
    const personSelect = document.getElementById('filter-client-person');
    const segmentSelect = document.getElementById('filter-client-segment');
    const citySelect = document.getElementById('filter-client-city');
    const statusSelect = document.getElementById('filter-client-status');

    if (searchInput) searchInput.addEventListener('input', applyClientFilters);
    if (personSelect) personSelect.addEventListener('change', applyClientFilters);
    if (segmentSelect) segmentSelect.addEventListener('change', applyClientFilters);
    if (citySelect) citySelect.addEventListener('change', applyClientFilters);
    if (statusSelect) statusSelect.addEventListener('change', applyClientFilters);

    applyClientFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientPage);
} else {
    initClientPage();
}
