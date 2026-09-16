/* Extraido de views/pages/management/employees/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var employeeCurrentPage = 1;
var employeePageSize = 8;
var employeeFilteredRows = [];

function applyEmployeeFilters() {
    const searchInput = document.getElementById('filter-employee-search');
    const roleSelect = document.getElementById('filter-employee-role');
    const cnhSelect = document.getElementById('filter-employee-cnh');
    const citySelect = document.getElementById('filter-employee-city');
    const resetBtn = document.getElementById('btn-reset-filters');
    const tbody = document.getElementById('employees-table-body');

    if (!tbody) return;

    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const selectedRole = roleSelect ? roleSelect.value : '';
    const selectedCnh = cnhSelect ? cnhSelect.value : '';
    const selectedCity = citySelect ? citySelect.value.toLowerCase() : '';

    const hasFilter = query !== '' || selectedRole !== '' || selectedCnh !== '' || selectedCity !== '';
    if (resetBtn) {
        if (hasFilter) {
            resetBtn.style.visibility = 'visible';
            resetBtn.style.opacity = '1';
        } else {
            resetBtn.style.visibility = 'hidden';
            resetBtn.style.opacity = '0';
        }
    }

    const allRows = Array.from(tbody.querySelectorAll('.employee-row'));
    employeeFilteredRows = [];

    // Remove empty state se existir
    const existingEmpty = document.getElementById('employee-dynamic-empty-row');
    if (existingEmpty) existingEmpty.remove();

    allRows.forEach(row => {
        const nameText = row.dataset.name || '';
        const rowRole = row.dataset.role || '';
        const rowCnh = row.dataset.cnh || '0';
        const rowCnhCat = row.dataset.cnhcat || '';
        const rowCity = row.dataset.city || '';

        const matchQuery = query === '' || nameText.includes(query);
        const matchRole = selectedRole === '' || rowRole.includes(selectedRole);
        
        let matchCnh = true;
        if (selectedCnh === 'com_cnh') matchCnh = rowCnh === '1';
        else if (selectedCnh === 'sem_cnh') matchCnh = rowCnh === '0';
        else if (selectedCnh !== '') matchCnh = rowCnhCat === selectedCnh;

        const matchCity = selectedCity === '' || rowCity.includes(selectedCity);

        if (matchQuery && matchRole && matchCnh && matchCity) {
            employeeFilteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    if (employeeFilteredRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'employee-dynamic-empty-row';
        emptyRow.style.height = '452px';
        emptyRow.innerHTML = `
            <td colspan="7" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                Nenhum funcionário encontrado com os filtros selecionados.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    employeeCurrentPage = 1;
    renderEmployeePagination();
}

function renderEmployeePagination() {
    const totalItems = employeeFilteredRows.length;
    const totalPages = Math.ceil(totalItems / employeePageSize) || 1;
    const countDisplay = document.getElementById('employee-result-count');
    const pageText = document.getElementById('employee-current-page-text');
    const totalPagesText = document.getElementById('employee-total-pages-text');
    const prevBtn = document.getElementById('employee-btn-prev');
    const nextBtn = document.getElementById('employee-btn-next');
    const pageNumbers = document.getElementById('employee-page-numbers');

    if (employeeCurrentPage > totalPages) employeeCurrentPage = totalPages;
    if (employeeCurrentPage < 1) employeeCurrentPage = 1;

    const startIdx = (employeeCurrentPage - 1) * employeePageSize;
    const endIdx = startIdx + employeePageSize;

    employeeFilteredRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    if (pageText) pageText.textContent = employeeCurrentPage;
    if (totalPagesText) totalPagesText.textContent = totalPages;

    if (countDisplay) {
        if (totalItems === 0) {
            countDisplay.textContent = 'Mostrando 0 de 0 funcionários';
        } else {
            const visibleCountOnPage = Math.min(employeePageSize, totalItems - startIdx);
            countDisplay.textContent = `Mostrando ${visibleCountOnPage} de ${totalItems} funcionários`;
        }
    }

    if (prevBtn) prevBtn.disabled = employeeCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = employeeCurrentPage >= totalPages;

    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === employeeCurrentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                employeeCurrentPage = i;
                renderEmployeePagination();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeEmployeePage(delta) {
    employeeCurrentPage += delta;
    renderEmployeePagination();
}

function resetAllEmployeeFilters() {
    const searchInput = document.getElementById('filter-employee-search');
    const roleSelect = document.getElementById('filter-employee-role');
    const cnhSelect = document.getElementById('filter-employee-cnh');
    const citySelect = document.getElementById('filter-employee-city');

    if (searchInput) searchInput.value = '';
    if (roleSelect) roleSelect.value = '';
    if (cnhSelect) cnhSelect.value = '';
    if (citySelect) citySelect.value = '';

    applyEmployeeFilters();
}

function initEmployeePage() {
    const searchInput = document.getElementById('filter-employee-search');
    const roleSelect = document.getElementById('filter-employee-role');
    const cnhSelect = document.getElementById('filter-employee-cnh');
    const citySelect = document.getElementById('filter-employee-city');

    if (searchInput) searchInput.addEventListener('input', applyEmployeeFilters);
    if (roleSelect) roleSelect.addEventListener('change', applyEmployeeFilters);
    if (cnhSelect) cnhSelect.addEventListener('change', applyEmployeeFilters);
    if (citySelect) citySelect.addEventListener('change', applyEmployeeFilters);

    applyEmployeeFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEmployeePage);
} else {
    initEmployeePage();
}
