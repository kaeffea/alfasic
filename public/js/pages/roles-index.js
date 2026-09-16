/* Extraido de views/pages/system/roles/index.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var allRoleRows = [];
var filteredRoleRows = [];
var roleCurrentPage = 1;
var roleRowsPerPage = 8;

function applyRoleFilters() {
    const term = (document.getElementById('filter-role-search')?.value || '').toLowerCase().trim();
    const status = document.getElementById('filter-role-status')?.value || '';
    const alloc = document.getElementById('filter-role-allocation')?.value || '';

    const hasActiveFilters = term !== '' || status !== '' || alloc !== '';
    const resetBtn = document.getElementById('btn-reset-role-filters');
    if (resetBtn) {
        resetBtn.style.visibility = hasActiveFilters ? 'visible' : 'hidden';
        resetBtn.style.opacity = hasActiveFilters ? '1' : '0';
    }

    filteredRoleRows = allRoleRows.filter(row => {
        const rName = row.getAttribute('data-name') || '';
        const rDesc = row.getAttribute('data-desc') || '';
        const rPerm = row.getAttribute('data-perm') || '';
        const rStatus = row.getAttribute('data-status') || '';
        const rHasUsers = row.getAttribute('data-has-users') || '';

        const matchText = term === '' || rName.includes(term) || rDesc.includes(term) || rPerm.includes(term);
        const matchStatus = status === '' || rStatus === status;
        const matchAlloc = alloc === '' || (alloc === 'com_usuarios' && rHasUsers === '1') || (alloc === 'sem_usuarios' && rHasUsers === '0');

        return matchText && matchStatus && matchAlloc;
    });

    roleCurrentPage = 1;
    renderRoleTable();
}

function resetAllRoleFilters() {
    const searchInput = document.getElementById('filter-role-search');
    const statusSelect = document.getElementById('filter-role-status');
    const allocSelect = document.getElementById('filter-role-allocation');

    if (searchInput) searchInput.value = '';
    if (statusSelect) { statusSelect.value = ''; CustomSelect.sync(statusSelect); }
    if (allocSelect) { allocSelect.value = ''; CustomSelect.sync(allocSelect); }

    applyRoleFilters();
}

function renderRoleTable() {
    const total = filteredRoleRows.length;
    const totalPages = Math.ceil(total / roleRowsPerPage) || 1;
    if (roleCurrentPage > totalPages) roleCurrentPage = totalPages;
    if (roleCurrentPage < 1) roleCurrentPage = 1;

    const startIdx = (roleCurrentPage - 1) * roleRowsPerPage;
    const endIdx = startIdx + roleRowsPerPage;

    allRoleRows.forEach(row => row.style.display = 'none');
    filteredRoleRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

    const resultCountEl = document.getElementById('role-result-count');
    if (resultCountEl) {
        const displayed = Math.min(total, endIdx - startIdx);
        resultCountEl.textContent = `Mostrando ${displayed} de ${total} perfis`;
    }

    const curPageEl = document.getElementById('role-current-page-text');
    const totalPageEl = document.getElementById('role-total-pages-text');
    if (curPageEl) curPageEl.textContent = roleCurrentPage;
    if (totalPageEl) totalPageEl.textContent = totalPages;

    const prevBtn = document.getElementById('role-btn-prev');
    const nextBtn = document.getElementById('role-btn-next');
    if (prevBtn) prevBtn.disabled = roleCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = roleCurrentPage >= totalPages;

    const pageNumbers = document.getElementById('role-page-numbers');
    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === roleCurrentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = () => {
                roleCurrentPage = i;
                renderRoleTable();
            };
            pageNumbers.appendChild(btn);
        }
    }
}

function changeRolePage(delta) {
    roleCurrentPage += delta;
    renderRoleTable();
}

function deleteRole(id, name) {
    if (confirm(`Deseja realmente excluir o perfil "${name}"? Os usuários vinculados ficarão sem permissões até serem realocados.`)) {
        alert(`Perfil #${id} (${name}) marcado para exclusão.`);
    }
}

function initRolePage() {
    allRoleRows = Array.from(document.querySelectorAll('.role-row'));
    filteredRoleRows = [...allRoleRows];

    const searchInput = document.getElementById('filter-role-search');
    const statusSelect = document.getElementById('filter-role-status');
    const allocSelect = document.getElementById('filter-role-allocation');

    if (searchInput) searchInput.addEventListener('input', applyRoleFilters);
    if (statusSelect) statusSelect.addEventListener('change', applyRoleFilters);
    if (allocSelect) allocSelect.addEventListener('change', applyRoleFilters);

    renderRoleTable();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRolePage);
} else {
    initRolePage();
}
