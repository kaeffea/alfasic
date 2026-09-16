/* Extraido de views/pages/system/audit/index.php (CSP Fase 2) - Vanilla JS. */
// Dicionário JavaScript para tradução de campos no Drawer
// var de proposito: o SPA (PageCache.applyPage) reexecuta este arquivo;
// let/const no topo lancariam SyntaxError na segunda execucao.
var FIELD_LABELS = JSON.parse(document.getElementById('audit-field-labels').textContent);

// Estado da Paginação
var auditCurrentPage = 1;
var auditPageSize = 5;
var auditFilteredItems = [];

function allowOnlyDigitsAndNav(e) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter', 'Home', 'End'];
    if (allowedKeys.includes(e.key) || (e.ctrlKey || e.metaKey)) return;
    if (!/^\d$/.test(e.key)) {
        e.preventDefault();
    }
}

function formatDateFilterInput(input, e) {
    let v = input.value.replace(/\D/g, '').slice(0, 8);
    if (v.length > 4) {
        v = v.replace(/^(\d{2})(\d{2})(\d{1,4})$/, '$1/$2/$3');
    } else if (v.length > 2) {
        v = v.replace(/^(\d{2})(\d{1,2})$/, '$1/$2');
    }
    input.value = v;

    if (v.length === 10) {
        const parts = v.split('/');
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10);
        const year = parseInt(parts[2], 10);
        const isValid = day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= 2100;
        if (!isValid) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    } else {
        input.classList.remove('is-invalid');
    }
    applyAuditFilters();
}

function applyAuditFilters() {
    const entity = document.getElementById('filter-audit-entity').value.toLowerCase();
    const action = document.getElementById('filter-audit-action').value.toLowerCase();
    const user = document.getElementById('filter-audit-user').value;
    const date = document.getElementById('filter-audit-date').value.trim();
    const search = document.getElementById('filter-audit-search').value.trim().toLowerCase();
    const resetBtn = document.getElementById('btn-reset-filters');

    const hasFilter = entity !== '' || action !== '' || user !== '' || date !== '' || search !== '';
    if (resetBtn) {
        if (hasFilter) {
            resetBtn.style.visibility = 'visible';
            resetBtn.style.opacity = '1';
        } else {
            resetBtn.style.visibility = 'hidden';
            resetBtn.style.opacity = '0';
        }
    }

    const items = Array.from(document.querySelectorAll('.audit-item'));
    auditFilteredItems = items.filter(item => {
        const nEntity = item.dataset.entity || '';
        const nAction = item.dataset.action || '';
        const nUser = item.dataset.user || '';
        const nDate = item.dataset.date || '';
        const nSearch = item.dataset.search || '';

        const matchEntity = !entity || nEntity === entity;
        const matchAction = !action || nAction === action;
        const matchUser = !user || nUser === user;
        const matchDate = !date || date.length < 10 || nDate === date;
        const matchSearch = !search || nSearch.includes(search);

        return matchEntity && matchAction && matchUser && matchDate && matchSearch;
    });

    auditCurrentPage = 1;
    renderAuditPagination();
}

function renderAuditPagination() {
    const totalItems = auditFilteredItems.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / auditPageSize));
    if (auditCurrentPage > totalPages) auditCurrentPage = totalPages;

    const startIdx = (auditCurrentPage - 1) * auditPageSize;
    const endIdx = startIdx + auditPageSize;

    // Fecha qualquer dropdown de ações aberto
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    // Oculta todos e exibe os da página atual
    document.querySelectorAll('.audit-item').forEach(item => item.style.display = 'none');
    const visibleItems = auditFilteredItems.slice(startIdx, endIdx);
    visibleItems.forEach(item => item.style.display = 'block');

    // Ajusta a linha vertical da timeline dinamicamente
    const line = document.querySelector('.timeline-line');
    if (line) {
        if (visibleItems.length <= 1) {
            line.style.display = 'none';
        } else {
            line.style.display = 'block';
            setTimeout(() => {
                const firstItem = visibleItems[0];
                const lastItem = visibleItems[visibleItems.length - 1];
                if (firstItem && lastItem) {
                    const topOffset = firstItem.offsetTop + 17;
                    const bottomOffset = lastItem.offsetTop + 17;
                    line.style.top = `${topOffset}px`;
                    line.style.height = `${Math.max(0, bottomOffset - topOffset)}px`;
                    line.style.bottom = 'auto';
                }
            }, 0);
        }
    }

    // Atualiza contadores
    document.getElementById('audit-current-page-text').textContent = auditCurrentPage;
    document.getElementById('audit-total-pages-text').textContent = totalPages;
    document.getElementById('audit-showing-count').textContent = visibleItems.length;
    document.getElementById('audit-total-records').textContent = totalItems;

    // Atualiza botões Prev/Next
    const prevBtn = document.getElementById('audit-btn-prev');
    const nextBtn = document.getElementById('audit-btn-next');
    if (prevBtn) prevBtn.disabled = auditCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = auditCurrentPage >= totalPages;

    // Gera os números de página
    const numbersContainer = document.getElementById('audit-page-numbers');
    if (numbersContainer) {
        numbersContainer.innerHTML = '';
        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (p === auditCurrentPage ? ' active' : '');
            btn.textContent = p;
            btn.onclick = () => {
                auditCurrentPage = p;
                renderAuditPagination();
            };
            numbersContainer.appendChild(btn);
        }
    }
}

function changeAuditPage(direction) {
    auditCurrentPage += direction;
    renderAuditPagination();
}

function resetAuditFilters() {
    document.getElementById('filter-audit-search').value = '';
    document.getElementById('filter-audit-entity').value = '';
    document.getElementById('filter-audit-action').value = '';
    document.getElementById('filter-audit-user').value = '';
    const dInput = document.getElementById('filter-audit-date');
    dInput.value = '';
    dInput.classList.remove('is-invalid');
    applyAuditFilters();
}

function openAuditDrawer(eventData) {
    // Fecha qualquer dropdown de ações aberto
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('audit-drawer-overlay');
    const drawer = document.getElementById('audit-drawer');
    if (!overlay || !drawer) return;

    document.getElementById('audit-drawer-title').textContent = eventData.entity_label || 'Transação #' + eventData.id;
    document.getElementById('audit-drawer-subtitle').textContent = eventData.action_label + ' • Log #' + eventData.id + ' • ' + eventData.created_at_exact;
    
    document.getElementById('audit-drawer-user').textContent = eventData.user_name + ' (' + eventData.user_role + ')';
    document.getElementById('audit-drawer-module').textContent = eventData.context_module;
    document.getElementById('audit-drawer-ip').textContent = eventData.ip_address || '127.0.0.1';
    document.getElementById('audit-drawer-date-exact').textContent = eventData.created_at_exact;
    document.getElementById('audit-drawer-user-agent').textContent = eventData.user_agent || '-';

    const reasonBox = document.getElementById('audit-drawer-reason-box');
    const reasonText = document.getElementById('audit-drawer-reason-text');
    if (eventData.reason) {
        reasonText.textContent = eventData.reason;
        reasonBox.style.display = 'block';
    } else {
        reasonBox.style.display = 'none';
    }

    renderAuditDiffTable(eventData.old_values, eventData.new_values, eventData.diff_fields);

    overlay.classList.add('show');
    drawer.classList.add('show');
}

function closeAuditDrawer() {
    const overlay = document.getElementById('audit-drawer-overlay');
    const drawer = document.getElementById('audit-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
}

function renderAuditDiffTable(oldVals, newVals, diffFields) {
    const tbody = document.getElementById('audit-diff-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const allKeys = new Set([...Object.keys(oldVals || {}), ...Object.keys(newVals || {})]);

    if (allKeys.size === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="padding: 20px; text-align: center; color: var(--text-muted);">Nenhum payload de dados registrado.</td></tr>';
        return;
    }

    allKeys.forEach(key => {
        if (key === 'id' || key === 'created_at' || key === 'updated_at') return;

        const oldV = oldVals ? oldVals[key] : null;
        const newV = newVals ? newVals[key] : null;
        const isChanged = JSON.stringify(oldV) !== JSON.stringify(newV);

        const tr = document.createElement('tr');
        tr.className = isChanged ? 'diff-row-changed' : 'diff-row-same';
        tr.style.borderBottom = '1px solid var(--border-color)';

        const formatVal = (v) => {
            if (v === null || v === undefined) return '<span style="color: var(--text-dim); font-style: italic;">(vazio)</span>';
            if (typeof v === 'boolean') return v ? 'Sim (1)' : 'Não (0)';
            return escapeHtml(String(v));
        };

        const fieldFriendlyName = FIELD_LABELS[key] || key;

        tr.innerHTML = `
            <td style="padding: 8px 12px; font-weight: 600; color: var(--text-main); font-size: 12px;">
                ${escapeHtml(fieldFriendlyName)}
            </td>
            <td style="padding: 8px 12px; font-size: 12px; ${isChanged ? 'background: #fef2f2; color: #991b1b; text-decoration: line-through;' : 'color: var(--text-muted);'}">
                ${formatVal(oldV)}
            </td>
            <td style="padding: 8px 12px; font-size: 12px; ${isChanged ? 'background: #f0fdf4; color: #166534; font-weight: 600;' : 'color: var(--text-muted);'}">
                ${formatVal(newV)}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function triggerAuditRollback(eventData) {
    // Fecha qualquer dropdown de ações aberto
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    openAuditActionModal({
        title: 'Confirmar Reversão',
        message: 'Você está prestes a reverter as alterações feitas em ' + eventData.entity_label + '. Os dados anteriores serão restaurados e uma nova entrada será gerada na auditoria.',
        entityLabel: 'Registro Alvo:',
        recordTitle: eventData.entity_label + ' (ID #' + eventData.entity_id + ')',
        recordDesc: 'Operação original: ' + eventData.action_label + ' em ' + eventData.created_at_exact,
        id: eventData.id,
        actionType: 'rollback',
        submitUrl: '/audit/rollback',
        redirectTo: '/audit',
        submitText: 'Confirmar Reversão',
        isDanger: false,
        requiresReason: true
    });
}

function triggerAuditHardDelete(eventData) {
    // Fecha qualquer dropdown de ações aberto
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    openAuditActionModal({
        title: 'Exclusão Permanente',
        message: 'Atenção: O sistema fará a checagem de integridade relacional. Se não houver vínculos impeditivos, o registro será removido fisicamente do banco de dados.',
        entityLabel: 'Registro a Excluir Permanentemente:',
        recordTitle: eventData.entity_label + ' (ID #' + eventData.entity_id + ')',
        recordDesc: 'Esta ação é irreversível e será registrada como Hard Delete.',
        id: eventData.entity_id,
        entity: eventData.entity,
        actionType: 'force_delete',
        submitUrl: '/audit/force-delete',
        redirectTo: '/audit',
        submitText: 'Excluir Permanentemente',
        isDanger: true,
        isHardDelete: true,
        requiresReason: true
    });
}

// Inicializa a paginação na carga
document.addEventListener('DOMContentLoaded', () => {
    applyAuditFilters();
});
