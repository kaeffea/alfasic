/* Extraido de views/pages/system/users/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var allUserRows = [];
var filteredUserRows = [];
var userCurrentPage = 1;
var userRowsPerPage = 8;

function applyUserFilters() {
    const term = (document.getElementById('filter-user-search')?.value || '').toLowerCase().trim();
    const role = (document.getElementById('filter-user-role')?.value || '').toLowerCase();
    const status = document.getElementById('filter-user-status')?.value || '';
    const employee = document.getElementById('filter-user-employee')?.value || '';

    const hasActiveFilters = term !== '' || role !== '' || status !== '' || employee !== '';
    const resetBtn = document.getElementById('btn-reset-user-filters');
    if (resetBtn) {
        resetBtn.style.visibility = hasActiveFilters ? 'visible' : 'hidden';
        resetBtn.style.opacity = hasActiveFilters ? '1' : '0';
    }

    filteredUserRows = allUserRows.filter(row => {
        const uName = row.getAttribute('data-username') || '';
        const uEmail = row.getAttribute('data-email') || '';
        const uEmp = row.getAttribute('data-employee') || '';
        const uRole = (row.getAttribute('data-role') || '').toLowerCase();
        const uStatus = row.getAttribute('data-status') || '';
        const uHasEmp = row.getAttribute('data-has-employee') || '';

        const matchText = term === '' || uName.includes(term) || uEmail.includes(term) || uEmp.includes(term);
        const matchRole = role === '' || uRole.includes(role);
        const matchStatus = status === '' || uStatus === status;
        const matchEmp = employee === '' || (employee === 'com_vinculo' && uHasEmp === '1') || (employee === 'sem_vinculo' && uHasEmp === '0');

        return matchText && matchRole && matchStatus && matchEmp;
    });

    userCurrentPage = 1;
    renderUserTable();
}

function resetAllUserFilters() {
    const searchInput = document.getElementById('filter-user-search');
    const roleSelect = document.getElementById('filter-user-role');
    const statusSelect = document.getElementById('filter-user-status');
    const employeeSelect = document.getElementById('filter-user-employee');

    if (searchInput) searchInput.value = '';
    if (roleSelect) { roleSelect.value = ''; CustomSelect.sync(roleSelect); }
    if (statusSelect) { statusSelect.value = ''; CustomSelect.sync(statusSelect); }
    if (employeeSelect) { employeeSelect.value = ''; CustomSelect.sync(employeeSelect); }

    applyUserFilters();
}

function renderUserTable() {
    const total = filteredUserRows.length;
    const totalPages = Math.ceil(total / userRowsPerPage) || 1;
    if (userCurrentPage > totalPages) userCurrentPage = totalPages;
    if (userCurrentPage < 1) userCurrentPage = 1;

    const startIdx = (userCurrentPage - 1) * userRowsPerPage;
    const endIdx = startIdx + userRowsPerPage;

    allUserRows.forEach(row => row.style.display = 'none');
    filteredUserRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

    const resultCountEl = document.getElementById('user-result-count');
    if (resultCountEl) {
        const displayed = Math.min(total, endIdx - startIdx);
        resultCountEl.textContent = `Mostrando ${displayed} de ${total} contas`;
    }

    const curPageEl = document.getElementById('user-current-page-text');
    const totalPageEl = document.getElementById('user-total-pages-text');
    if (curPageEl) curPageEl.textContent = userCurrentPage;
    if (totalPageEl) totalPageEl.textContent = totalPages;

    const prevBtn = document.getElementById('user-btn-prev');
    const nextBtn = document.getElementById('user-btn-next');
    if (prevBtn) prevBtn.disabled = userCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = userCurrentPage >= totalPages;

    const pageNumbers = document.getElementById('user-page-numbers');
    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === userCurrentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                userCurrentPage = i;
                renderUserTable();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeUserPage(delta) {
    userCurrentPage += delta;
    renderUserTable();
}

function deleteUser(id, username) {
    if (confirm(`Deseja realmente excluir a conta de acesso "${username}"? Esta ação revogará todo o acesso ao sistema imediatamente.`)) {
        alert(`Conta #${id} (${username}) marcada para exclusão.`);
    }
}

function initUserPage() {
    allUserRows = Array.from(document.querySelectorAll('.user-row'));
    filteredUserRows = [...allUserRows];

    const searchInput = document.getElementById('filter-user-search');
    const roleSelect = document.getElementById('filter-user-role');
    const statusSelect = document.getElementById('filter-user-status');
    const employeeSelect = document.getElementById('filter-user-employee');

    if (searchInput) searchInput.addEventListener('input', applyUserFilters);
    if (roleSelect) roleSelect.addEventListener('change', applyUserFilters);
    if (statusSelect) statusSelect.addEventListener('change', applyUserFilters);
    if (employeeSelect) employeeSelect.addEventListener('change', applyUserFilters);

    renderUserTable();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserPage);
} else {
    initUserPage();
}
