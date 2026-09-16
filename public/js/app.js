/* ==========================================================================
   ALFASIC - VANILLA JS (Zero Dependências)
   Navegação por Topbar + Dropdowns + Command Palette + Alta Ergonomia
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    WorkspaceTabs.init();
    initDropdowns();
    initCommandPalette();
    initSingleKeyShortcuts();
    initCopyClicks();
    initClientInstantSearch();
    initSupplierInstantSearch();
    initRowActionDropdowns();
    initProductPriceCombobox();
    initLinkInterception();
    CustomSelect.init();
});

/**
 * Intercepta cliques em links internos do Alfasic para troca instantânea de abas e páginas (0ms)
 */
function initLinkInterception() {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('http') || link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        // Intercepta rotas internas do Alfasic
        if (href.startsWith('/') && !href.startsWith('/auth/logout')) {
            e.preventDefault();
            PageCache.navigate(href);
        }
    });
}

/**
 * 0. Gerenciador de Abas Dinâmicas, Livres e Reordenáveis (Workspace Tabs)
 * Aba ÚNICA por cliente • Nome completo sem cortes • Rolagem horizontal por setas e touchpad
 */
const WorkspaceTabs = {
    STORAGE_KEY: 'alfasic_workspace_tabs',

    getTabs() {
        try {
            const raw = sessionStorage.getItem(this.STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    },

    saveTabs(tabs) {
        sessionStorage.setItem(this.STORAGE_KEY, JSON.stringify(tabs));
    },

    getIconSvg(type) {
        switch (type) {
            case 'suppliers':
            case 'supplier':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20"/><path d="M6 20V10l6 4V10l6 4v6"/><path d="M18 4h2v16h-2z"/></svg>';
            case 'employees':
            case 'employee':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/></svg>';
            case 'clients':
            case 'client':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
            case 'products':
            case 'product':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>';
            case 'users':
            case 'user':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>';
            case 'roles':
            case 'role':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
            case 'audit':
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="12 8 12 12 14 14"/></svg>';
            case 'dashboard':
            default:
                return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>';
        }
    },

    init() {
        const container = document.getElementById('workspace-tabs');
        if (!container) return;

        const currentPath = window.location.pathname + window.location.search;
        const isWorkspaceEmpty = (currentPath === '/workspace/empty');

        let tabs = this.getTabs();

        // Registra a página atual se não for empty workspace
        if (!isWorkspaceEmpty) {
            let tabId = currentPath;
            let pageTitle = container.dataset.pageTitle || 'Página';
            let iconType = 'dashboard';

            // 1. Identifica se é página pertencente a um CLIENTE (aba única por cliente)
            // Suporta /clients/123, /clients/123/prices, /clients/123/orders, /clients/123/rentals, /clients/123/biddings, ou legado ?id=123
            let clientMatch = currentPath.match(/^\/clients\/(\d+)(\/(prices|orders|rentals|biddings))?/);
            if (!clientMatch) {
                const legacyMatch = currentPath.match(/^\/clients\/(prices|orders|rentals|biddings|show|details|edit)\?id=(\d+)/);
                if (legacyMatch) {
                    clientMatch = [null, legacyMatch[2]];
                }
            }

            if (clientMatch) {
                const clientId = clientMatch[1];
                tabId = `client_${clientId}`;
                iconType = 'client';

                // Extrai o nome completo do cliente do título da página
                const titleEl = document.querySelector('.page-title, .client-profile-name');
                if (titleEl) {
                    let raw = titleEl.textContent.trim();
                    pageTitle = raw.replace(/^(Detalhes|Editar|Editar Cadastro|Tabela de Preços de|Preços de|Pedidos de|Vasilhames de|Licitações de):\s*/i, '');
                }
            } else if (currentPath.match(/^\/suppliers\/(\d+)/) || currentPath.match(/^\/suppliers\/(show|details|edit)\?id=(\d+)/)) {
                // 1.1. Identifica se é página pertencente a um FORNECEDOR (aba única por fornecedor)
                const sMatch = currentPath.match(/^\/suppliers\/(\d+)/) || currentPath.match(/^\/suppliers\/(show|details|edit)\?id=(\d+)/);
                const supplierId = sMatch[1] || sMatch[2];
                tabId = `supplier_${supplierId}`;
                iconType = 'supplier';

                const titleEl = document.querySelector('.page-title, .supplier-profile-name');
                if (titleEl) {
                    let raw = titleEl.textContent.trim();
                    pageTitle = raw.replace(/^(Fornecedor:\s*|Ficha de:\s*|Editar:\s*|Detalhes:\s*)/i, '').replace(/\s*-\s*Alfagás$/i, '');
                }
            } else if (currentPath.match(/^\/employees\/(\d+)/) || currentPath.match(/^\/employees\/(show|details|edit)\?id=(\d+)/)) {
                // 1.2. Identifica se é página pertencente a um FUNCIONÁRIO (aba única por funcionário)
                const eMatch = currentPath.match(/^\/employees\/(\d+)/) || currentPath.match(/^\/employees\/(show|details|edit)\?id=(\d+)/);
                const empId = eMatch[1] || eMatch[2];
                tabId = `employee_${empId}`;
                iconType = 'employee';

                const titleEl = document.querySelector('.page-title, .employee-profile-name');
                if (titleEl) {
                    let raw = titleEl.textContent.trim();
                    pageTitle = raw.replace(/^(Funcionário:\s*|Ficha de:\s*|Editar:\s*|Detalhes:\s*)/i, '').replace(/\s*-\s*Alfagás$/i, '');
                }
            } else if (currentPath === '/' || currentPath === '') {
                tabId = 'dashboard';
                pageTitle = 'Painel';
                iconType = 'dashboard';
            } else if (currentPath === '/clients' || currentPath.startsWith('/clients?')) {
                tabId = 'clients_list';
                pageTitle = 'Clientes';
                iconType = 'clients';
            } else if (currentPath === '/suppliers' || currentPath.startsWith('/suppliers?')) {
                tabId = 'suppliers_list';
                pageTitle = 'Fornecedores';
                iconType = 'suppliers';
            } else if (currentPath === '/employees' || currentPath.startsWith('/employees?')) {
                tabId = 'employees_list';
                pageTitle = 'Funcionários';
                iconType = 'employees';
            } else if (currentPath === '/products' || currentPath.startsWith('/products?')) {
                tabId = 'products_list';
                pageTitle = 'Produtos';
                iconType = 'products';
            } else if (currentPath === '/users' || currentPath.startsWith('/users?')) {
                tabId = 'users_list';
                pageTitle = 'Usuários';
                iconType = 'users';
            } else if (currentPath === '/roles' || currentPath.startsWith('/roles?')) {
                tabId = 'roles_list';
                pageTitle = 'Perfis';
                iconType = 'roles';
            } else if (currentPath === '/audit' || currentPath.startsWith('/audit?')) {
                tabId = 'audit_list';
                pageTitle = 'Auditoria';
                iconType = 'audit';
            }

            // Procura se já existe uma aba para este ID (ex: client_2817) ou pela URL exata
            const existingIndex = tabs.findIndex(t => t.id === tabId || t.url === currentPath);
            if (existingIndex === -1) {
                tabs.push({
                    id: tabId,
                    url: currentPath,
                    title: pageTitle,
                    icon: iconType
                });
            } else {
                // Atualiza a URL e o título da aba existente (mesma aba atualizada!)
                tabs[existingIndex].id = tabId;
                tabs[existingIndex].url = currentPath;
                tabs[existingIndex].title = pageTitle;
                tabs[existingIndex].icon = iconType;
            }
            this.saveTabs(tabs);
        }

        this.render();
        this.initScrollControls();
    },

    render() {
        const container = document.getElementById('workspace-tabs');
        const wrapper = document.querySelector('.workspace-tabs-wrapper');
        if (!container) return;

        const tabs = this.getTabs();
        const currentPath = window.location.pathname + window.location.search;

        if (tabs.length === 0) {
            container.innerHTML = '';
            if (wrapper) wrapper.style.display = 'none';
            return;
        }

        if (wrapper) wrapper.style.display = 'flex';

        // Identifica se a página atual pertence a algum tabId
        let activeTabId = null;
        const cMatch = currentPath.match(/^\/clients\/(\d+)/) || currentPath.match(/^\/clients\/[^\?]+\?id=(\d+)/);
        if (cMatch) {
            activeTabId = `client_${cMatch[1]}`;
        } else {
            const sMatch = currentPath.match(/^\/suppliers\/(\d+)/) || currentPath.match(/^\/suppliers\/[^\?]+\?id=(\d+)/);
            if (sMatch) {
                activeTabId = `supplier_${sMatch[1]}`;
            } else {
                const eMatch = currentPath.match(/^\/employees\/(\d+)/) || currentPath.match(/^\/employees\/[^\?]+\?id=(\d+)/);
                if (eMatch) {
                    activeTabId = `employee_${eMatch[1]}`;
                } else if (currentPath === '/' || currentPath === '') {
                    activeTabId = 'dashboard';
                } else if (currentPath === '/clients' || currentPath.startsWith('/clients?')) {
                    activeTabId = 'clients_list';
                } else if (currentPath === '/suppliers' || currentPath.startsWith('/suppliers?')) {
                    activeTabId = 'suppliers_list';
                } else if (currentPath === '/employees' || currentPath.startsWith('/employees?')) {
                    activeTabId = 'employees_list';
                } else if (currentPath === '/products' || currentPath.startsWith('/products?')) {
                    activeTabId = 'products_list';
                } else if (currentPath === '/users' || currentPath.startsWith('/users?')) {
                    activeTabId = 'users_list';
                } else if (currentPath === '/roles' || currentPath.startsWith('/roles?')) {
                    activeTabId = 'roles_list';
                } else if (currentPath === '/audit' || currentPath.startsWith('/audit?')) {
                    activeTabId = 'audit_list';
                }
            }
        }

        container.innerHTML = tabs.map((t, index) => {
            const isActive = (t.id && activeTabId && t.id === activeTabId) || (t.url === currentPath);

            return `
                <div class="w-tab ${isActive ? 'active' : ''}" 
                     draggable="true" 
                     data-index="${index}" 
                      data-tab-id="${escapeHtml(t.id || t.url)}"
                      data-action="workspace-goto" data-url="${escapeAttr(t.url)}">
                     ${this.getIconSvg(t.icon)}
                     <span class="w-tab-title">${escapeHtml(t.title)}</span>
                     <span class="w-tab-close" data-action="workspace-close" data-id="${escapeAttr(t.id || t.url)}" title="Fechar aba">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </span>
                </div>
            `;
        }).join('');

        this.initDragAndDrop();
        this.updateScrollButtons();

        // Rola o container horizontalmente (sem rolar a janela principal)
        const activeTabEl = container.querySelector('.w-tab.active');
        const scrollContainer = document.getElementById('workspace-tabs-scroll');
        if (activeTabEl && scrollContainer) {
            const tabLeft = activeTabEl.offsetLeft;
            const tabWidth = activeTabEl.offsetWidth;
            const cWidth = scrollContainer.clientWidth;
            if (tabLeft < scrollContainer.scrollLeft || (tabLeft + tabWidth) > (scrollContainer.scrollLeft + cWidth)) {
                scrollContainer.scrollLeft = Math.max(0, tabLeft - (cWidth / 2) + (tabWidth / 2));
            }
        }
    },

    goTo(url, e) {
        if (e && e.target.closest('.w-tab-close')) return;
        PageCache.navigate(url);
    },

    close(tabKey, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        let tabs = this.getTabs();
        const currentPath = window.location.pathname + window.location.search;
        const targetIndex = tabs.findIndex(t => (t.id === tabKey || t.url === tabKey));

        if (targetIndex === -1) return;

        const closedTab = tabs.splice(targetIndex, 1)[0];
        this.saveTabs(tabs);

        // Se fechou a aba ATIVA exibida na tela
        const isActive = (closedTab.url === currentPath) || (closedTab.id && currentPath.includes(closedTab.id.replace('client_', 'id=')));
        if (isActive) {
            if (tabs.length > 0) {
                const nextIndex = Math.max(0, targetIndex - 1);
                PageCache.navigate(tabs[nextIndex].url);
            } else {
                PageCache.navigate('/workspace/empty');
            }
        } else {
            // Fechou aba em segundo plano
            this.render();
        }
    },

    initScrollControls() {
        const wrapper = document.querySelector('.workspace-tabs-wrapper');
        const scrollContainer = document.getElementById('workspace-tabs-scroll');
        const btnLeft = document.getElementById('tabs-scroll-left');
        const btnRight = document.getElementById('tabs-scroll-right');

        if (!scrollContainer) return;

        // 1. Rolagem por Touchpad (Gesto Vertical -> Horizontal) e Roda do Mouse
        const handleWheel = (e) => {
            const hasOverflow = scrollContainer.scrollWidth > scrollContainer.clientWidth;
            if (!hasOverflow) return;

            // Se for movimento com predominância vertical (touchpad ou mouse wheel vertical)
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && e.deltaY !== 0) {
                e.preventDefault();
                scrollContainer.scrollLeft += (e.deltaY * 1.3);
                WorkspaceTabs.updateScrollButtons();
            } else if (e.deltaX !== 0) {
                // Deixa rolagem nativa horizontal do trackpad correr suavemente
                setTimeout(() => WorkspaceTabs.updateScrollButtons(), 30);
            }
        };

        if (wrapper) {
            wrapper.addEventListener('wheel', handleWheel, { passive: false });
        } else {
            scrollContainer.addEventListener('wheel', handleWheel, { passive: false });
        }

        // 2. Botão Rolar para a Esquerda
        if (btnLeft) {
            btnLeft.addEventListener('click', (e) => {
                e.preventDefault();
                scrollContainer.scrollBy({ left: -260, behavior: 'smooth' });
                setTimeout(() => WorkspaceTabs.updateScrollButtons(), 200);
            });
        }

        // 3. Botão Rolar para a Direita
        if (btnRight) {
            btnRight.addEventListener('click', (e) => {
                e.preventDefault();
                scrollContainer.scrollBy({ left: 260, behavior: 'smooth' });
                setTimeout(() => WorkspaceTabs.updateScrollButtons(), 200);
            });
        }

        // Atualiza botões no redimensionamento da janela e ao rolar
        window.addEventListener('resize', () => {
            WorkspaceTabs.updateScrollButtons();
        });

        scrollContainer.addEventListener('scroll', () => {
            WorkspaceTabs.updateScrollButtons();
        }, { passive: true });
    },

    updateScrollButtons() {
        const scrollContainer = document.getElementById('workspace-tabs-scroll');
        const btnLeft = document.getElementById('tabs-scroll-left');
        const btnRight = document.getElementById('tabs-scroll-right');

        if (!scrollContainer || !btnLeft || !btnRight) return;

        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        const hasOverflow = maxScroll > 2;

        if (hasOverflow) {
            if (scrollContainer.scrollLeft > 6) {
                btnLeft.classList.add('visible');
            } else {
                btnLeft.classList.remove('visible');
            }

            if (scrollContainer.scrollLeft < maxScroll - 6) {
                btnRight.classList.add('visible');
            } else {
                btnRight.classList.remove('visible');
            }
        } else {
            btnLeft.classList.remove('visible');
            btnRight.classList.remove('visible');
        }
    },

    initDragAndDrop() {
        const container = document.getElementById('workspace-tabs');
        if (!container) return;

        let draggedIndex = null;

        container.querySelectorAll('.w-tab').forEach(tab => {
            tab.addEventListener('dragstart', (e) => {
                draggedIndex = parseInt(tab.dataset.index, 10);
                tab.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedIndex);
            });

            tab.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                tab.classList.add('drag-over');
            });

            tab.addEventListener('dragleave', () => {
                tab.classList.remove('drag-over');
            });

            tab.addEventListener('drop', (e) => {
                e.preventDefault();
                tab.classList.remove('drag-over');
                const targetIndex = parseInt(tab.dataset.index, 10);

                if (draggedIndex !== null && draggedIndex !== targetIndex) {
                    let tabs = WorkspaceTabs.getTabs();
                    const movedItem = tabs.splice(draggedIndex, 1)[0];
                    tabs.splice(targetIndex, 0, movedItem);
                    WorkspaceTabs.saveTabs(tabs);
                    WorkspaceTabs.render();
                }
            });

            tab.addEventListener('dragend', () => {
                tab.classList.remove('dragging');
                container.querySelectorAll('.w-tab').forEach(t => t.classList.remove('drag-over'));
            });
        });
    }
};

/**
 * 1. Dropdown Menus no Header
 */
function initDropdowns() {
    document.querySelectorAll('.dd-trigger').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const menuId = btn.dataset.menu;
            const menu = document.getElementById(menuId);
            if (!menu) return;

            const isShown = menu.classList.contains('show');
            closeAllDropdowns();

            if (!isShown) {
                menu.classList.add('show');
                btn.classList.add('active');
            }
        });
    });

    window.addEventListener('click', () => {
        closeAllDropdowns();
    });
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.dd-trigger').forEach(b => {
        if (!b.classList.contains('stay-active')) {
            b.classList.remove('active');
        }
    });
}

/**
 * 2. Command Palette Global (F2 ou Tecla Barra [/])
 */
function initCommandPalette() {
    const overlay = document.getElementById('cmd-overlay');
    const trigger = document.getElementById('cmd-search-trigger');
    const input = document.getElementById('cmd-input');
    const results = document.getElementById('cmd-results');

    if (!overlay || !trigger || !input || !results) return;

    let debounceTimer;

    function openPalette() {
        overlay.style.display = 'flex';
        input.value = '';
        input.focus();
    }

    function closePalette() {
        overlay.style.display = 'none';
    }

    trigger.addEventListener('click', openPalette);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closePalette();
    });

    input.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();

        if (!query) {
            results.innerHTML = `
                <div style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 6px 12px 2px 12px; letter-spacing: 0.05em;">Ações Rápidas</div>
                <a href="/clients/create" class="cmd-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px;">
                    <div>
                        <strong>+ Cadastrar Novo Cliente</strong>
                        <div style="font-size: 11px; color: #64748b;">Abrir formulário de cadastro</div>
                    </div>
                    <span style="font-size: 10px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono);">Tecla N</span>
                </a>
                <a href="/clients" class="cmd-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px;">
                    <div>
                        <strong>Ver Todos os Clientes & Preços</strong>
                        <div style="font-size: 11px; color: #64748b;">Lista e filtros</div>
                    </div>
                    <span style="font-size: 10px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono);">Shift+C</span>
                </a>
            `;
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/clients/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(clients => {
                    if (clients.length === 0) {
                        results.innerHTML = `<div style="padding: 16px; text-align: center; color: #64748b; font-size: 13px;">Nenhum cliente encontrado para "${escapeHtml(query)}"</div>`;
                        return;
                    }

                    results.innerHTML = `
                        <div style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 6px 12px 2px 12px;">Resultados Encontrados (${clients.length})</div>
                        ${clients.map(c => `
                            <a href="/clients/${c.id}/prices" class="cmd-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 6px; text-decoration: none; color: #0f172a; font-size: 13px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <div>
                                    <strong style="color: #0f172a;">${escapeHtml(c.name)}</strong>
                                    <div style="font-size: 11px; color: #64748b;">${escapeHtml(c.city || '-')}/${escapeHtml(c.state || 'AL')} • ${escapeHtml(c.document || 'Sem CNPJ')}</div>
                                </div>
                                <span style="font-size: 11px; color: #0284c7; font-weight: 700;">Ver Preços &rarr;</span>
                            </a>
                        `).join('')}
                    `;
                })
                .catch(err => console.error(err));
        }, 150);
    });
}

/**
 * 3. Atalhos de Tecla Única e Navegação Rápida
 */
function initSingleKeyShortcuts() {
    window.addEventListener('keydown', (e) => {
        const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
        const overlay = document.getElementById('cmd-overlay');

        // Tecla Única: '/' (Barra) abre a Command Palette
        if (!isTyping && e.key === '/') {
            e.preventDefault();
            const trigger = document.getElementById('cmd-search-trigger');
            if (trigger) trigger.click();
            return;
        }

        // Tecla Única: 'n' ou 'N' abre o formulário de Novo Cliente
        if (!isTyping && e.key.toLowerCase() === 'n') {
            e.preventDefault();
            window.location.href = '/clients/create';
            return;
        }

        // Tecla Única: 'Esc' fecha a paleta ou dropdowns
        if (e.key === 'Escape') {
            if (overlay) overlay.style.display = 'none';
            closeAllDropdowns();
        }
    });
}

/**
 * 4. Cópia de Dados em 1 Clique (Mouse-Only)
 */
function initCopyClicks() {
    document.querySelectorAll('.copy-click').forEach(el => {
        el.addEventListener('click', () => {
            const text = el.dataset.copy;
            if (text) {
                navigator.clipboard.writeText(text);
                showToast(`Copiado com sucesso: ${text}`);
            }
        });
    });
}

/**
 * Notificação Toast Global
 */
function showToast(msg) {
    const box = document.getElementById('toast-box');
    const text = document.getElementById('toast-text');
    if (!box || !text) return;

    text.textContent = msg;
    box.style.display = 'flex';

    setTimeout(() => {
        box.style.display = 'none';
    }, 2500);
}

/**
 * 5. Busca Instantânea de Clientes na tabela com debounce
 */
function initClientInstantSearch() {
    const searchInput = document.getElementById('client-search-input');
    const tableBody = document.getElementById('clients-table-body');
    const searchCount = document.getElementById('search-result-count');

    if (!searchInput || !tableBody) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const term = e.target.value.trim();

        debounceTimer = setTimeout(() => {
            fetchClients(term);
        }, 200);
    });

    function fetchClients(term) {
        const url = `/api/clients/search?q=${encodeURIComponent(term)}`;
        
        fetch(url)
            .then(res => res.json())
            .then(clients => {
                renderTableRows(clients);
                if (searchCount) {
                    if (!term) {
                        searchCount.textContent = `Mostrando ${clients.length} de 1.327 clientes`;
                    } else {
                        searchCount.textContent = `Mostrando ${clients.length} de 1.327 clientes encontrados`;
                    }
                }
            })
            .catch(err => {
                console.error('Erro na busca:', err);
            });
    }

    function renderTableRows(clients) {
        if (clients.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">
                        Nenhum cliente encontrado para o termo pesquisado.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = clients.map(c => {
            const regDate = c.created_at ? formatDate(c.created_at) : '-';
            const lastOrder = c.last_order_date ? formatDate(c.last_order_date) : '<span style="color: var(--text-dim); font-weight: normal;">Sem compras</span>';

            return `
                <tr>
                    <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#${c.id}</td>
                    <td>
                        <a href="/clients/${c.id}" style="font-weight: 700; color: var(--text-main); text-decoration: none; font-size: 13px;">
                            ${escapeHtml(c.name)}
                        </a>
                        ${c.trade_name ? `<div style="font-size: 11px; color: var(--text-muted);">${escapeHtml(c.trade_name)}</div>` : ''}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 11px;">
                        <span class="copy-click" data-copy="${escapeHtml(c.document || '')}" title="Clique para copiar">
                            ${escapeHtml(c.document || '-')}
                        </span>
                    </td>
                    <td>${escapeHtml(c.city || '-')}/${escapeHtml(c.state || 'AL')}</td>
                    <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted);">
                        ${regDate}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-main); font-weight: 600;">
                        ${lastOrder}
                    </td>
                    <td style="text-align: center;">
                        ${c.is_active ? '<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;" title="Ativo"></span>' : '<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #94a3b8;" title="Inativo"></span>'}
                    </td>
                    <td style="text-align: center; position: relative;">
                        <div class="row-actions-dropdown">
                            <button type="button" class="btn-row-action" title="Opções" aria-label="Opções">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div class="row-action-menu">
                                <a href="/clients/${c.id}" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>Dados & Cadastro</span>
                                </a>
                                <a href="/clients/${c.id}/prices" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                                    </svg>
                                    <span>Tabela de Preços</span>
                                </a>
                                <a href="/clients/${c.id}/orders" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                                    </svg>
                                    <span>Histórico de Pedidos</span>
                                </a>
                                <a href="/clients/${c.id}/rentals" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                    </svg>
                                    <span>Vasilhames & Locação</span>
                                </a>
                                <a href="/clients/${c.id}/biddings" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                    <span>Licitações (ARP)</span>
                                </a>
                                ${window.CAN_WRITE !== false ? `
                                    <div class="action-menu-divider"></div>
                                    <button type="button" class="action-menu-item" data-action="open-client-drawer" data-payload="${escapeAttr(JSON.stringify(c))}">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        <span>Editar Cadastro</span>
                                    </button>
                                    <form method="POST" action="/clients/delete" style="display: block; width: 100%;" data-confirm="Deseja realmente arquivar este cliente?">
                                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name=csrf_token]') ? document.querySelector('input[name=csrf_token]').value : ''}">
                                        <input type="hidden" name="id" value="${c.id}">
                                        <button type="submit" class="action-menu-item danger">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                            <span>Excluir Cliente</span>
                                        </button>
                                    </form>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        initCopyClicks();
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split(' ')[0].split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }
}

/**
 * 5.1. Busca Instantânea de Fornecedores na tabela com debounce
 */
function initSupplierInstantSearch() {
    const searchInput = document.getElementById('supplier-search-input');
    const tableBody = document.getElementById('suppliers-table-body');
    const searchCount = document.getElementById('supplier-result-count');

    if (!searchInput || !tableBody) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const term = e.target.value.trim();

        debounceTimer = setTimeout(() => {
            fetchSuppliers(term);
        }, 200);
    });

    function fetchSuppliers(term) {
        const url = `/api/suppliers/search?q=${encodeURIComponent(term)}`;
        
        fetch(url)
            .then(res => res.json())
            .then(suppliers => {
                renderTableRows(suppliers);
                if (searchCount) {
                    searchCount.textContent = `Mostrando ${suppliers.length} registro(s)`;
                }
            })
            .catch(err => {
                console.error('Erro na busca de fornecedores:', err);
            });
    }

    function renderTableRows(suppliers) {
        if (suppliers.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 28px; color: var(--text-muted);">
                        Nenhum fornecedor encontrado para o termo pesquisado.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = suppliers.map(s => {
            const rawJson = JSON.stringify(s).replace(/"/g, '&quot;');

            return `
                <tr>
                    <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#${s.id}</td>
                    <td>
                        <a href="/suppliers/${s.id}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                            ${escapeHtml(s.name)}
                        </a>
                        ${s.trade_name ? `<div style="font-size: 11px; color: var(--text-muted);">${escapeHtml(s.trade_name)}</div>` : ''}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 11px;">
                        <span class="copy-click" data-copy="${escapeHtml(s.document || '')}" title="Clique para copiar" style="cursor: pointer;">
                            ${escapeHtml(s.document || '-')}
                        </span>
                    </td>
                    <td>${escapeHtml(s.city || '-')}/${escapeHtml(s.state || 'AL')}</td>
                    <td>
                        <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: 600; color: var(--text-main);">
                            ${escapeHtml(s.supplier_type || 'usina')}
                        </span>
                    </td>
                    <td style="font-size: 11px;">
                        <div>${escapeHtml(s.contact_person || '-')}</div>
                        <div style="color: var(--text-muted); font-family: var(--font-mono);">${escapeHtml(s.phone || '')}</div>
                    </td>
                    <td style="text-align: center; position: relative;">
                        <div class="row-actions-dropdown">
                            <button type="button" class="btn-row-action" title="Opções" aria-label="Opções">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div class="row-action-menu">
                                <a href="/suppliers/${s.id}" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span>Ver Ficha</span>
                                </a>
                                <button type="button" class="action-menu-item" data-action="open-supplier-drawer" data-payload="${escapeAttr(JSON.stringify(s))}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    <span>Editar Cadastro</span>
                                </button>
                                <div class="action-menu-divider"></div>
                                <form method="POST" action="/suppliers/delete" style="display: block; width: 100%; margin: 0;" data-confirm="Deseja realmente arquivar este fornecedor?">
                                    <input type="hidden" name="id" value="${s.id}">
                                    <button type="submit" class="action-menu-item danger" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        <span>Excluir Fornecedor</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        initCopyClicks();
    }
}

/**
 * 6.5. Gerenciador de Cache de Páginas & Navegação Instantânea (0ms SPA Tabs)
 */
const PageCache = {
    cache: new Map(),

    async navigate(url, pushState = true, force = false) {
        if (!url) return;
        const currentPath = window.location.pathname + window.location.search;
        // popstate (voltar/avançar) chega com a URL já trocada: precisa forçar,
        // senão o guard abaixo aborta e o botão do navegador "não faz nada".
        if (!force && url === currentPath) return;

        // 1. Se já está em cache na memória, troca imediatamente o conteúdo
        if (this.cache.has(url)) {
            const pageData = this.cache.get(url);
            this.applyPage(pageData, url, pushState);
            // Revalida em background sem travar a tela (só cache, sem renderizar)
            this.fetchAndCache(url, false, false);
            return;
        }

        // 2. Se não está no cache, busca via fetch assíncrono
        await this.fetchAndCache(url, pushState, true);
    },

    async fetchAndCache(url, pushState = true, apply = true) {
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) {
                window.location.href = url;
                return;
            }

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const contentEl = doc.querySelector('.page-content');
            const title = doc.title || 'Alfasic';
            const tabsEl = doc.querySelector('#workspace-tabs');

            if (!contentEl) {
                window.location.href = url;
                return;
            }

            const pageData = {
                title,
                content: contentEl.innerHTML,
                pageTitle: tabsEl ? (tabsEl.dataset.pageTitle || title) : title
            };

            this.cache.set(url, pageData);

            // popstate (voltar/avançar) chega com pushState=false mas PRECISA renderizar;
            // só a revalidação de background passa apply=false.
            if (apply) {
                this.applyPage(pageData, url, pushState);
            }
        } catch (e) {
            window.location.href = url;
        }
    },

    applyPage(pageData, url, pushState = true) {
        document.title = pageData.title;
        const main = document.querySelector('.page-content');
        
        // 1. Remove qualquer menu flutuante de CustomSelect anterior deixado no body
        document.querySelectorAll('.custom-select-menu').forEach(m => m.remove());

        if (main) {
            main.innerHTML = pageData.content;
            window.scrollTo(0, 0);

            // 2. Reexecuta os scripts internos da página carregada
            const scripts = main.querySelectorAll('script');
            scripts.forEach(s => {
                const newScript = document.createElement('script');
                Array.from(s.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.textContent = s.textContent;
                s.parentNode.replaceChild(newScript, s);
            });
        }

        if (pushState) {
            window.history.pushState({ url }, '', url);
        }

        // 3. Reexecuta inicializadores globais
        initCopyClicks();
        initClientInstantSearch();
        initSupplierInstantSearch();
        initEmployeeInstantSearch();
        initProductPriceCombobox();
        initDelegatedActions();
        CustomSelect.init(document);
        WorkspaceTabs.init();
    }
};

/**
 * 6.8. Combobox Inteligente de Precificação de Produtos
 */
function initProductPriceCombobox() {
    const input = document.getElementById('product_search_input');
    const hiddenId = document.getElementById('product_id');
    const priceInput = document.getElementById('price');
    const dropdown = document.getElementById('product-combobox-dropdown');
    const dataEl = document.getElementById('catalog-products-data');

    if (!input || !hiddenId || !dropdown || !dataEl) return;

    let catalogProducts = [];
    try {
        catalogProducts = JSON.parse(dataEl.textContent);
    } catch (e) {
        return;
    }

    function renderResults(filtered) {
        if (!filtered || filtered.length === 0) {
            dropdown.innerHTML = '<div style="padding: 12px; font-size: 12px; color: var(--text-muted); text-align: center;">Nenhum produto disponível encontrado</div>';
            dropdown.classList.add('show');
            return;
        }

        dropdown.innerHTML = filtered.slice(0, 15).map(g => `
            <div class="combobox-item" data-id="${g.id}" data-base="${g.base_price}" data-name="${escapeHtml(g.name)}">
                <div>
                    <div class="combobox-item-name">${escapeHtml(g.name)}</div>
                    ${g.capacity ? `<span style="font-size: 11px; color: var(--text-dim);">${escapeHtml(g.capacity)}</span>` : ''}
                </div>
                <div class="combobox-item-price">Tabela: R$ ${g.base_price_formatted}</div>
            </div>
        `).join('');

        dropdown.classList.add('show');

        dropdown.querySelectorAll('.combobox-item').forEach(item => {
            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const id = item.dataset.id;
                const name = item.dataset.name;
                const base = item.dataset.base;

                hiddenId.value = id;
                input.value = name;
                if (!priceInput.value || priceInput.value === '0,00') {
                    priceInput.value = parseFloat(base).toFixed(2).replace('.', ',');
                }
                dropdown.classList.remove('show');
                priceInput.focus();
            });
        });
    }

    input.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        if (!query) {
            hiddenId.value = '';
            dropdown.classList.remove('show');
            return;
        }

        const terms = query.split(/\s+/);
        const filtered = catalogProducts.filter(g => {
            const fullText = (g.name + ' ' + (g.capacity || '')).toLowerCase();
            return terms.every(t => fullText.includes(t));
        });

        renderResults(filtered);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length > 0) {
            input.dispatchEvent(new Event('input'));
        } else {
            renderResults(catalogProducts.slice(0, 10));
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.combobox-wrapper')) {
            dropdown.classList.remove('show');
        }
    });
}

window.addEventListener('popstate', (e) => {
    const targetUrl = window.location.pathname + window.location.search;
    PageCache.navigate(targetUrl, false, true);
});

/**
 * 7. Menu de Ações Flutuante por Linha da Tabela (Menu 3 Pontinhos sem scroll)
 */
function initRowActionDropdowns() {
    if (window._rowActionsInitialized) return;
    window._rowActionsInitialized = true;

    function closeAll() {
        document.querySelectorAll('.row-action-menu.show').forEach(m => {
            m.classList.remove('show');
            m.style.display = 'none';
        });
        document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-row-action');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();

            const dropdown = btn.closest('.row-actions-dropdown');
            const menu = dropdown ? dropdown.querySelector('.row-action-menu') : null;
            if (!menu) return;

            const isAlreadyOpen = menu.classList.contains('show');
            closeAll();

            if (!isAlreadyOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = `${rect.bottom + 4}px`;
                menu.style.left = 'auto';
                menu.style.right = `${Math.max(10, window.innerWidth - rect.right)}px`;
                menu.style.zIndex = '999999';
                menu.style.display = 'flex';
                menu.classList.add('show');
                btn.classList.add('active');
            }
            return;
        }

        if (!e.target.closest('.row-action-menu')) {
            closeAll();
        }
    });

    window.addEventListener('scroll', closeAll, true);
    window.addEventListener('resize', closeAll);
}

/**
 * =============================================================================
 * MOTOR GLOBAL DE RASCUNHOS DE DRAWERS (DrawerDrafts)
 * =============================================================================
 * Gerencia persistência inteligente e não-destrutiva de dados preenchidos em
 * todos os slide-over drawers do sistema (Clientes, Fornecedores, Funcionários,
 * Produtos, Usuários e Perfis).
 *
 * Regras:
 * 1. Fechamento via Overlay, 'X' ou Escape -> Salva rascunho daquele alvo.
 * 2. Múltiplos rascunhos coexistem (ex: client:new, client:15, supplier:new).
 * 3. Troca de abas/páginas mantém os rascunhos em sessionStorage.
 * 4. Botão 'Cancelar' -> Descarta o rascunho do item aberto e reseta o form.
 * 5. Submit com sucesso -> Descarta o rascunho do item salvo.
 * 6. F5 / Reload da página -> Limpa todos os rascunhos e reinicia zerado.
 */
const DrawerDrafts = {
    _isRestoring: false,
    _prefix: 'alfasic_draft_',

    init() {
        try {
            const navEntries = performance.getEntriesByType('navigation');
            if (navEntries.length > 0 && navEntries[0].type === 'reload') {
                this.clearAll();
            }
        } catch (e) {
            console.warn('Drafts reload check failed:', e);
        }
    },

    getKey(entity, targetId) {
        const id = targetId !== undefined && targetId !== null && String(targetId).trim() !== '' ? String(targetId).trim() : 'new';
        return `${this._prefix}${entity}_${id}`;
    },

    save(form, entity, targetId) {
        if (!form || this._isRestoring) return;
        const key = this.getKey(entity, targetId);
        
        const data = {};
        const elements = form.querySelectorAll('input, select, textarea');
        
        let hasValue = false;
        elements.forEach(el => {
            if (!el.name || el.type === 'submit' || el.type === 'button' || (el.type === 'hidden' && el.name === 'csrf_token')) return;
            
            if (el.type === 'checkbox') {
                if (!data[el.name]) data[el.name] = [];
                if (el.checked) {
                    data[el.name].push(el.value);
                    hasValue = true;
                }
            } else if (el.type === 'radio') {
                if (el.checked) {
                    data[el.name] = el.value;
                    hasValue = true;
                }
            } else {
                data[el.name] = el.value;
                if (el.value && el.value.trim() !== '') {
                    hasValue = true;
                }
            }
        });

        if (hasValue) {
            sessionStorage.setItem(key, JSON.stringify(data));
        }
    },

    restore(form, entity, targetId) {
        if (!form) return false;
        const key = this.getKey(entity, targetId);
        const raw = sessionStorage.getItem(key);
        if (!raw) return false;

        try {
            const data = JSON.parse(raw);
            if (!data || typeof data !== 'object') return false;

            this._isRestoring = true;

            Object.entries(data).forEach(([name, value]) => {
                const fields = form.querySelectorAll(`[name="${name}"]`);
                if (fields.length === 0) return;

                if (Array.isArray(value)) {
                    fields.forEach(f => {
                        if (f.type === 'checkbox') {
                            f.checked = value.includes(f.value);
                        }
                    });
                } else {
                    const first = fields[0];
                    if (first.type === 'radio') {
                        fields.forEach(f => {
                            f.checked = (f.value === String(value));
                        });
                    } else {
                        first.value = value;
                    }
                }
            });

            return true;
        } catch (e) {
            console.warn('Erro ao restaurar rascunho:', e);
            return false;
        } finally {
            this._isRestoring = false;
        }
    },

    clear(entity, targetId) {
        const key = this.getKey(entity, targetId);
        sessionStorage.removeItem(key);
    },

    clearAll() {
        try {
            const toRemove = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const k = sessionStorage.key(i);
                if (k && k.startsWith(this._prefix)) {
                    toRemove.push(k);
                }
            }
            toRemove.forEach(k => sessionStorage.removeItem(k));
        } catch (e) {
            console.warn('Erro ao limpar rascunhos:', e);
        }
    },

    has(entity, targetId) {
        const key = this.getKey(entity, targetId);
        return sessionStorage.getItem(key) !== null;
    },

    bind(drawerEl, formEl, entity, getTargetIdFn) {
        if (!drawerEl || !formEl || formEl._draftBound) return;
        formEl._draftBound = true;

        const handleInput = () => {
            if (this._isRestoring) return;
            const targetId = getTargetIdFn();
            this.save(formEl, entity, targetId);
        };

        formEl.addEventListener('input', handleInput);
        formEl.addEventListener('change', handleInput);

        formEl.addEventListener('submit', () => {
            const targetId = getTargetIdFn();
            this.clear(entity, targetId);
        });
    }
};

/**
 * 8. Funções do Slide-Over Drawer (Modal Lateral) para Clientes
 */
function openClientDrawer(client = null) {
    // Fecha qualquer menu de 3 pontinhos que esteja aberto
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('client-drawer-overlay');
    const drawer = document.getElementById('client-drawer');
    const form = document.getElementById('client-drawer-form');
    const heading = document.getElementById('drawer-heading');
    const subheading = document.getElementById('drawer-subheading');
    const submitBtn = document.getElementById('drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = client && client.id ? String(client.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        if (client && client.id) {
            // Modo Edição
            heading.textContent = 'Editar Cadastro: ' + client.name;
            subheading.textContent = 'Atualize os dados cadastrais e comerciais';
            submitBtn.textContent = 'Salvar Alterações';
            form.action = '/clients/update';

            document.getElementById('drawer-client-id').value = client.id || '';
            document.getElementById('drawer-name').value = client.name || '';
            document.getElementById('drawer-trade-name').value = client.trade_name || '';
            document.getElementById('drawer-client-type').value = client.client_type || 'company';
            document.getElementById('drawer-document').value = client.document || '';
            document.getElementById('drawer-state-reg').value = client.state_registration || '';
            document.getElementById('drawer-contact').value = client.contact_person || '';
            document.getElementById('drawer-phone').value = client.phone || '';
            document.getElementById('drawer-email').value = client.email || '';
            document.getElementById('drawer-address').value = client.address || '';
            document.getElementById('drawer-address-number').value = client.address_number || '';
            document.getElementById('drawer-neighborhood').value = client.neighborhood || '';
            document.getElementById('drawer-city').value = client.city || 'São Miguel dos Campos';
            document.getElementById('drawer-state').value = client.state || 'AL';
            document.getElementById('drawer-payment-terms').value = client.payment_terms || 'À Vista';
            document.getElementById('drawer-billing-method').value = client.billing_method || 'Boleto Bancário';
            document.getElementById('drawer-notes').value = client.notes || '';
        } else {
            // Modo Novo Cadastro
            heading.textContent = 'Novo Cadastro de Cliente';
            subheading.textContent = 'Preencha as informações cadastrais e comerciais';
            submitBtn.textContent = 'Cadastrar Cliente';
            form.action = '/clients/store';
            form.reset();

            document.getElementById('drawer-client-id').value = '';
            document.getElementById('drawer-city').value = 'São Miguel dos Campos';
            document.getElementById('drawer-state').value = 'AL';
            document.getElementById('drawer-payment-terms').value = 'À Vista';
            document.getElementById('drawer-billing-method').value = 'Boleto Bancário';
        }

        DrawerDrafts.restore(form, 'client', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'client', () => {
        const idVal = document.getElementById('drawer-client-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    document.getElementById('drawer-name').focus();
}

function closeClientDrawer(isCancel = false) {
    const form = document.getElementById('client-drawer-form');
    if (form) {
        const idVal = document.getElementById('drawer-client-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('client', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'client', targetId);
        }
    }

    const overlay = document.getElementById('client-drawer-overlay');
    const drawer = document.getElementById('client-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.1. Funções do Slide-Over Drawer para Fornecedores
 */
function openSupplierDrawer(supplier = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('supplier-drawer-overlay');
    const drawer = document.getElementById('supplier-drawer');
    const form = document.getElementById('supplier-drawer-form');
    const heading = document.getElementById('supplier-drawer-heading');
    const subheading = document.getElementById('supplier-drawer-subheading');
    const submitBtn = document.getElementById('supplier-drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = supplier && supplier.id ? String(supplier.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        if (supplier && supplier.id) {
            heading.textContent = 'Editar Fornecedor: ' + (supplier.trade_name || supplier.name);
            subheading.textContent = 'Atualize as informações cadastrais da usina ou parceiro';
            submitBtn.textContent = 'Salvar Alterações';
            form.action = '/suppliers/update';

            const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v !== null && v !== undefined ? v : ''); };
            setVal('supplier-drawer-id', supplier.id);
            setVal('supplier-drawer-name', supplier.name);
            setVal('supplier-drawer-trade-name', supplier.trade_name);
            setVal('supplier-drawer-type', supplier.supplier_type || 'usina');
            setVal('supplier-drawer-document', supplier.document);
            setVal('supplier-drawer-state-reg', supplier.state_registration);
            setVal('supplier-drawer-contact', supplier.contact_person);
            setVal('supplier-drawer-phone', supplier.phone);
            setVal('supplier-drawer-email', supplier.email);
            setVal('supplier-drawer-address', supplier.address);
            setVal('supplier-drawer-address-number', supplier.address_number);
            setVal('supplier-drawer-neighborhood', supplier.neighborhood);
            setVal('supplier-drawer-city', supplier.city || 'Maceió');
            setVal('supplier-drawer-state', supplier.state || 'AL');
            setVal('supplier-drawer-is-active', supplier.is_active !== undefined ? (supplier.is_active ? '1' : '0') : '1');
            setVal('supplier-drawer-notes', supplier.notes);
        } else {
            heading.textContent = 'Novo Fornecedor';
            subheading.textContent = 'Preencha as informações cadastrais da usina ou parceiro';
            submitBtn.textContent = 'Cadastrar Fornecedor';
            form.action = '/suppliers/store';
            form.reset();

            const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v !== null && v !== undefined ? v : ''); };
            setVal('supplier-drawer-id', '');
            setVal('supplier-drawer-type', 'usina');
            setVal('supplier-drawer-city', 'Maceió');
            setVal('supplier-drawer-state', 'AL');
            setVal('supplier-drawer-is-active', '1');
        }

        DrawerDrafts.restore(form, 'supplier', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'supplier', () => {
        const idVal = document.getElementById('supplier-drawer-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    document.getElementById('supplier-drawer-name').focus();
}

function closeSupplierDrawer(isCancel = false) {
    const form = document.getElementById('supplier-drawer-form');
    if (form) {
        const idVal = document.getElementById('supplier-drawer-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('supplier', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'supplier', targetId);
        }
    }

    const overlay = document.getElementById('supplier-drawer-overlay');
    const drawer = document.getElementById('supplier-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.2. Funções do Slide-Over Drawer para Funcionários
 */
function openEmployeeDrawer(employee = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('employee-drawer-overlay');
    const drawer = document.getElementById('employee-drawer');
    const form = document.getElementById('employee-drawer-form');
    const heading = document.getElementById('employee-drawer-heading');
    const subheading = document.getElementById('employee-drawer-subheading');
    const submitBtn = document.getElementById('employee-drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = employee && employee.id ? String(employee.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v !== null && v !== undefined ? v : ''); };
        if (employee && employee.id) {
            heading.textContent = 'Editar Funcionário: ' + employee.name;
            subheading.textContent = 'Atualize as informações contratuais e de habilitação';
            submitBtn.textContent = 'Salvar Alterações';
            form.action = '/employees/update';

            setVal('employee-drawer-id', employee.id);
            setVal('employee-drawer-name', employee.name);
            setVal('employee-drawer-role', employee.role_title || '');
            setVal('employee-drawer-hire-date', employee.hire_date);
            setVal('employee-drawer-document', employee.document);
            setVal('employee-drawer-rg', employee.rg);
            setVal('employee-drawer-birth-date', employee.birth_date);
            setVal('employee-drawer-birth-city', employee.birth_city);
            setVal('employee-drawer-branch', employee.branch);
            setVal('employee-drawer-department', employee.department);
            setVal('employee-drawer-cnh', employee.driver_license);
            setVal('employee-drawer-cnh-cat', employee.driver_license_category);
            setVal('employee-drawer-cnh-expiry', employee.driver_license_expiry);
            setVal('employee-drawer-certs', employee.certifications);
            setVal('employee-drawer-phone', employee.phone);
            setVal('employee-drawer-email', employee.email);
            setVal('employee-drawer-em-name', employee.emergency_contact_name);
            setVal('employee-drawer-em-phone', employee.emergency_contact_phone);
            setVal('employee-drawer-salary', employee.base_salary);
            setVal('employee-drawer-benefits', employee.benefits_cost);
            setVal('employee-drawer-pix', employee.pix_key);
            setVal('employee-drawer-address', employee.address);
            setVal('employee-drawer-address-number', employee.address_number);
            setVal('employee-drawer-neighborhood', employee.neighborhood);
            setVal('employee-drawer-city', employee.city || 'São Miguel dos Campos');
            setVal('employee-drawer-state', employee.state || 'AL');
            setVal('employee-drawer-notes', employee.notes);
        } else {
            heading.textContent = 'Novo Funcionário';
            subheading.textContent = 'Preencha as informações profissionais e contratuais';
            submitBtn.textContent = 'Cadastrar Funcionário';
            form.action = '/employees/store';
            form.reset();

            setVal('employee-drawer-id', '');
            setVal('employee-drawer-role', '');
            setVal('employee-drawer-city', 'São Miguel dos Campos');
            setVal('employee-drawer-state', 'AL');
        }

        DrawerDrafts.restore(form, 'employee', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'employee', () => {
        const idVal = document.getElementById('employee-drawer-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    document.getElementById('employee-drawer-name').focus();
}

function closeEmployeeDrawer(isCancel = false) {
    const form = document.getElementById('employee-drawer-form');
    if (form) {
        const idVal = document.getElementById('employee-drawer-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('employee', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'employee', targetId);
        }
    }

    const overlay = document.getElementById('employee-drawer-overlay');
    const drawer = document.getElementById('employee-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.3. Funções do Slide-Over Drawer para Produtos
 */
function openProductDrawer(product = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('product-drawer-overlay');
    const drawer = document.getElementById('product-drawer');
    const form = document.getElementById('product-drawer-form');
    const heading = document.getElementById('product-drawer-heading');
    const subheading = document.getElementById('product-drawer-subheading');
    const submitBtn = document.getElementById('drawer-product-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = product && product.id ? String(product.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        if (product && product.id) {
            heading.textContent = 'Editar Produto: ' + product.name;
            subheading.textContent = 'Atualize as especificações e preços do item #' + product.id;
            if (submitBtn) submitBtn.textContent = 'Salvar Alterações';

            if (document.getElementById('drawer-product-id')) document.getElementById('drawer-product-id').value = product.id || '';
            if (document.getElementById('drawer-product-name')) document.getElementById('drawer-product-name').value = product.name || '';
            if (document.getElementById('drawer-product-cyltype')) document.getElementById('drawer-product-cyltype').value = product.cylinder_type_id || '';
            if (document.getElementById('drawer-product-type')) document.getElementById('drawer-product-type').value = product.product_type || 'gas';
            if (document.getElementById('drawer-product-usage')) document.getElementById('drawer-product-usage').value = product.usage_segment || 'industrial';
            if (document.getElementById('drawer-product-capacity')) document.getElementById('drawer-product-capacity').value = product.capacity || '';
            if (document.getElementById('drawer-product-unit')) document.getElementById('drawer-product-unit').value = product.unit || 'm³';
            
            // Campo type=number exige ponto decimal (a máscara pt-BR cuida da exibição).
            let price = product.standard_price ? parseFloat(product.standard_price).toFixed(2) : '0.00';
            if (document.getElementById('drawer-product-price')) document.getElementById('drawer-product-price').value = price;
            if (document.getElementById('drawer-product-ncm')) document.getElementById('drawer-product-ncm').value = product.ncm || '';
            if (document.getElementById('drawer-product-notes')) document.getElementById('drawer-product-notes').value = product.notes || '';
        } else {
            heading.textContent = 'Novo Cadastro de Produto';
            subheading.textContent = 'Preencha as informações do produto, gás ou equipamento';
            if (submitBtn) submitBtn.textContent = 'Cadastrar Produto';
            
            form.reset();
            if (document.getElementById('drawer-product-id')) document.getElementById('drawer-product-id').value = '';
            if (document.getElementById('drawer-product-price')) document.getElementById('drawer-product-price').value = '0.00';
        }

        DrawerDrafts.restore(form, 'product', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'product', () => {
        const idVal = document.getElementById('drawer-product-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    const nameInput = document.getElementById('drawer-product-name');
    if (nameInput) nameInput.focus();
}

function closeProductDrawer(isCancel = false) {
    const form = document.getElementById('product-drawer-form');
    if (form) {
        const idVal = document.getElementById('drawer-product-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('product', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'product', targetId);
        }
    }

    const overlay = document.getElementById('product-drawer-overlay');
    const drawer = document.getElementById('product-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.3b. Funções do Slide-Over Drawer para Tipos de Cilindro
 */
function openCylinderDrawer(type = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('cylinder-drawer-overlay');
    const drawer = document.getElementById('cylinder-drawer');
    const form = document.getElementById('cylinder-drawer-form');
    const heading = document.getElementById('cylinder-drawer-heading');
    const subheading = document.getElementById('cylinder-drawer-subheading');
    const submitBtn = document.getElementById('cylinder-drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = type && type.id ? String(type.id) : 'new';
    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v !== null && v !== undefined ? v : ''); };

    DrawerDrafts._isRestoring = true;
    try {
        if (type && type.id) {
            if (heading) heading.textContent = 'Editar Tipo: ' + type.name;
            if (subheading) subheading.textContent = 'Atualize as especificações do vasilhame #' + type.id;
            if (submitBtn) submitBtn.textContent = 'Salvar Alterações';
            setVal('cylinder-drawer-id', type.id);
            setVal('cylinder-drawer-name', type.name);
            setVal('cylinder-drawer-code', type.code);
            setVal('cylinder-drawer-capacity', type.capacity);
            setVal('cylinder-drawer-unit', type.unit || 'm³');
            setVal('cylinder-drawer-pressure', type.working_pressure_bar);
            setVal('cylinder-drawer-tare', type.tare_weight_kg);
            setVal('cylinder-drawer-value', type.replacement_value);
            setVal('cylinder-drawer-notes', type.notes);
            setVal('cylinder-drawer-status', type.is_active !== undefined ? (type.is_active ? '1' : '0') : '1');
            setVal('cylinder-drawer-product', '');
            setVal('cylinder-drawer-new-product', '');
            const cur = document.getElementById('cylinder-drawer-current');
            if (cur) {
                if (type.product_name) {
                    cur.style.display = 'block';
                    cur.textContent = 'Vinculado hoje a: ' + type.product_name + ' (escolha outro acima para trocar)';
                } else {
                    cur.style.display = 'none';
                    cur.textContent = '';
                }
            }
        } else {
            if (heading) heading.textContent = 'Novo Tipo de Cilindro';
            if (subheading) subheading.textContent = 'Especificações do vasilhame e vínculo 1:1 com o gás';
            if (submitBtn) submitBtn.textContent = 'Cadastrar Tipo';
            form.reset();
            setVal('cylinder-drawer-id', '');
            setVal('cylinder-drawer-unit', 'm³');
            setVal('cylinder-drawer-status', '1');
            const cur = document.getElementById('cylinder-drawer-current');
            if (cur) { cur.style.display = 'none'; cur.textContent = ''; }
        }

        DrawerDrafts.restore(form, 'cylinder', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'cylinder', () => {
        const idVal = document.getElementById('cylinder-drawer-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    const nameInput = document.getElementById('cylinder-drawer-name');
    if (nameInput) nameInput.focus();
}

function closeCylinderDrawer(isCancel = false) {
    const form = document.getElementById('cylinder-drawer-form');
    if (form) {
        const idVal = document.getElementById('cylinder-drawer-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('cylinder', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'cylinder', targetId);
        }
    }

    const overlay = document.getElementById('cylinder-drawer-overlay');
    const drawer = document.getElementById('cylinder-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.4. Funções do Slide-Over Drawer para Usuários
 */
function openUserDrawer(user = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('user-drawer-overlay');
    const drawer = document.getElementById('user-drawer');
    const form = document.getElementById('user-drawer-form');
    const heading = document.getElementById('user-drawer-heading');
    const subheading = document.getElementById('user-drawer-subheading');
    const submitBtn = document.getElementById('user-drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = user && user.id ? String(user.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        if (user && user.id) {
            if (heading) heading.textContent = 'Editar Usuário: ' + user.username;
            if (subheading) subheading.textContent = 'Alterando dados de acesso da conta #' + user.id;
            if (submitBtn) submitBtn.textContent = 'Salvar Alterações';

            if (document.getElementById('user-drawer-id')) document.getElementById('user-drawer-id').value = user.id || '';
            if (document.getElementById('user-drawer-username')) document.getElementById('user-drawer-username').value = user.username || '';
            if (document.getElementById('user-drawer-email')) document.getElementById('user-drawer-email').value = user.email || '';
            if (document.getElementById('user-drawer-role')) document.getElementById('user-drawer-role').value = user.role_id || user.role_name || '';
            if (document.getElementById('user-drawer-employee')) document.getElementById('user-drawer-employee').value = user.employee_id || '';
            if (document.getElementById('user-drawer-status')) document.getElementById('user-drawer-status').value = user.is_active ? '1' : '0';
            
            const pass = document.getElementById('user-drawer-password');
            if (pass) {
                pass.placeholder = 'Deixe em branco para manter a atual';
                pass.required = false;
            }
        } else {
            if (heading) heading.textContent = 'Novo Usuário';
            if (subheading) subheading.textContent = 'Preencha os dados de acesso e vincule a um colaborador';
            if (submitBtn) submitBtn.textContent = 'Cadastrar Usuário';

            form.reset();
            if (document.getElementById('user-drawer-id')) document.getElementById('user-drawer-id').value = '';
            const pass = document.getElementById('user-drawer-password');
            if (pass) {
                pass.placeholder = 'Mínimo 8 caracteres';
                pass.required = true;
            }
        }

        DrawerDrafts.restore(form, 'user', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'user', () => {
        const idVal = document.getElementById('user-drawer-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    const uInput = document.getElementById('user-drawer-username');
    if (uInput) uInput.focus();
}

function closeUserDrawer(isCancel = false) {
    const form = document.getElementById('user-drawer-form');
    if (form) {
        const idVal = document.getElementById('user-drawer-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('user', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'user', targetId);
        }
    }

    const overlay = document.getElementById('user-drawer-overlay');
    const drawer = document.getElementById('user-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 8.5. Funções do Slide-Over Drawer para Perfis de Acesso
 */
function openRoleDrawer(role = null) {
    document.querySelectorAll('.row-action-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-row-action.active').forEach(b => b.classList.remove('active'));

    const overlay = document.getElementById('role-drawer-overlay');
    const drawer = document.getElementById('role-drawer');
    const form = document.getElementById('role-drawer-form');
    const heading = document.getElementById('role-drawer-heading');
    const subheading = document.getElementById('role-drawer-subheading');
    const submitBtn = document.getElementById('role-drawer-submit-btn');

    if (!overlay || !drawer || !form) return;

    const targetId = role && role.id ? String(role.id) : 'new';

    DrawerDrafts._isRestoring = true;
    try {
        if (role && role.id) {
            if (heading) heading.textContent = 'Editar Perfil: ' + role.name;
            if (subheading) subheading.textContent = 'Ajuste as permissões deste grupo de acesso';
            if (submitBtn) submitBtn.textContent = 'Salvar Alterações';

            if (document.getElementById('role-drawer-id')) document.getElementById('role-drawer-id').value = role.id || '';
            if (document.getElementById('role-drawer-name')) document.getElementById('role-drawer-name').value = role.name || '';
            if (document.getElementById('role-drawer-desc')) document.getElementById('role-drawer-desc').value = role.description || '';
            if (document.getElementById('role-drawer-status')) document.getElementById('role-drawer-status').value = role.is_active !== undefined ? (role.is_active ? '1' : '0') : '1';

            const isAll = (role.permissions || []).includes('all');
            document.querySelectorAll('.perm-check').forEach(cb => {
                cb.checked = isAll || (role.permissions || []).includes(cb.value);
            });
        } else {
            if (heading) heading.textContent = 'Novo Perfil de Acesso';
            if (subheading) subheading.textContent = 'Defina um nome e selecione as permissões de cada módulo';
            if (submitBtn) submitBtn.textContent = 'Cadastrar Perfil';

            form.reset();
            if (document.getElementById('role-drawer-id')) document.getElementById('role-drawer-id').value = '';
            document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
        }

        DrawerDrafts.restore(form, 'role', targetId);
    } finally {
        DrawerDrafts._isRestoring = false;
    }

    DrawerDrafts.bind(drawer, form, 'role', () => {
        const idVal = document.getElementById('role-drawer-id')?.value;
        return idVal && idVal.trim() !== '' ? idVal : 'new';
    });

    drawer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    drawer.querySelectorAll('.form-feedback-error').forEach(el => el.remove());
    InputMasks.init(drawer);
    FormValidator.init(drawer);
    CustomSelect.syncAll(drawer);
    overlay.classList.add('show');
    drawer.classList.add('show');
    const rInput = document.getElementById('role-drawer-name');
    if (rInput) rInput.focus();
}

function closeRoleDrawer(isCancel = false) {
    const form = document.getElementById('role-drawer-form');
    if (form) {
        const idVal = document.getElementById('role-drawer-id')?.value;
        const targetId = idVal && idVal.trim() !== '' ? idVal : 'new';

        if (isCancel) {
            DrawerDrafts.clear('role', targetId);
            form.reset();
        } else {
            DrawerDrafts.save(form, 'role', targetId);
        }
    }

    const overlay = document.getElementById('role-drawer-overlay');
    const drawer = document.getElementById('role-drawer');
    if (overlay) overlay.classList.remove('show');
    if (drawer) drawer.classList.remove('show');
    CustomSelect.closeAll();
}

/**
 * 5.2. Busca Instantânea de Funcionários na tabela com debounce
 */
function initEmployeeInstantSearch() {
    const searchInput = document.getElementById('employee-search-input');
    const tableBody = document.getElementById('employees-table-body');
    const searchCount = document.getElementById('employee-result-count');

    if (!searchInput || !tableBody) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const term = e.target.value.trim();

        debounceTimer = setTimeout(() => {
            fetchEmployees(term);
        }, 200);
    });

    function fetchEmployees(term) {
        fetch(`/api/employees/search?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(employees => {
                renderTableRows(employees);
                if (searchCount) {
                    searchCount.textContent = `Mostrando ${employees.length} de ${employees.length} funcionários`;
                }
            })
            .catch(err => console.error('Erro na busca de funcionários:', err));
    }

    function renderTableRows(employees) {
        if (employees.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                        Nenhum funcionário encontrado para o termo pesquisado.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = employees.map(e => {
            const rawJson = JSON.stringify(e).replace(/"/g, '&quot;');

            return `
                <tr>
                    <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#${e.id}</td>
                    <td>
                        <a href="/employees/${e.id}" style="font-weight: 700; color: var(--text-main); text-decoration: none;">
                            ${escapeHtml(e.name)}
                        </a>
                        <div style="font-size: 11px; color: var(--text-muted);">${escapeHtml(e.role_title || 'Operacional')}</div>
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 11px;">
                        ${escapeHtml(e.document || '-')}
                    </td>
                    <td style="font-size: 11px;">
                        <div style="font-family: var(--font-mono);">${escapeHtml(e.phone || '-')}</div>
                        ${e.email ? `<div style="color: var(--text-muted);">${escapeHtml(e.email)}</div>` : ''}
                    </td>
                    <td>${escapeHtml(e.city || 'São Miguel dos Campos')}/${escapeHtml(e.state || 'AL')}</td>
                    <td style="font-size: 11px;">
                        ${e.driver_license ? `
                            <span style="font-family: var(--font-mono); font-weight: 600; color: var(--text-main);">Cat. ${escapeHtml(e.driver_license_category || 'B')}</span>
                            <span style="color: var(--text-muted); font-size: 10px;">(${escapeHtml(e.driver_license)})</span>
                        ` : '<span style="color: var(--text-dim);">-</span>'}
                    </td>
                    <td style="text-align: center; position: relative;">
                        <div class="row-actions-dropdown">
                            <button type="button" class="btn-row-action" title="Opções" aria-label="Opções">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div class="row-action-menu">
                                <a href="/employees/${e.id}" class="action-menu-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>Dados & Cadastro</span>
                                </a>
                                <div class="action-menu-divider"></div>
                                <button type="button" class="action-menu-item" data-action="open-employee-drawer" data-payload="${escapeAttr(JSON.stringify(e))}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    <span>Editar Cadastro</span>
                                </button>
                                <form method="POST" action="/employees/delete" style="display: block; width: 100%; margin: 0;" data-confirm="Deseja realmente arquivar/desligar este funcionário?">
                                    <input type="hidden" name="id" value="${e.id}">
                                    <button type="submit" class="action-menu-item danger" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        <span>Desligar Funcionário</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        initCopyClicks();
    }
}

// ==========================================================================
// COMPONENTE GLOBAL CUSTOM SELECT (Vanilla JS Puro)
// Transforma <select> nativo em dropdown flutuante 100% estilizado com checkmark
// ==========================================================================
const CustomSelect = {
    init(container = document) {
        const selects = container.querySelectorAll('select.form-select, select.filter-select, select.custom-select');
        selects.forEach(select => this.enhance(select));
    },

    enhance(select) {
        if (!select || select.dataset.customSelectInit === 'true' || select.dataset.noCustom === 'true') return;
        select.dataset.customSelectInit = 'true';

        // 1. Cria o wrapper relativo
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        if (select.classList.contains('filter-select') || select.classList.contains('form-select-sm')) {
            wrapper.classList.add('custom-select-sm');
        }

        // 2. Trigger visível customizado (substitui a caixa fechada)
        const trigger = document.createElement('div');
        const isSm = select.classList.contains('filter-select') || select.classList.contains('form-select-sm');
        trigger.className = 'custom-select-trigger' + (isSm ? ' custom-select-trigger-sm' : '');
        trigger.tabIndex = 0;

        const label = document.createElement('span');
        label.className = 'custom-select-trigger-label';
        
        const selectedOpt = select.options[select.selectedIndex] || select.options[0];
        label.textContent = selectedOpt ? selectedOpt.textContent : 'Selecione...';

        const arrowSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        arrowSvg.setAttribute('class', 'custom-select-arrow');
        arrowSvg.setAttribute('viewBox', '0 0 24 24');
        arrowSvg.setAttribute('fill', 'none');
        arrowSvg.setAttribute('stroke', 'currentColor');
        arrowSvg.setAttribute('stroke-width', '2');
        arrowSvg.setAttribute('stroke-linecap', 'round');
        arrowSvg.setAttribute('stroke-linejoin', 'round');
        arrowSvg.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>';

        trigger.appendChild(label);
        trigger.appendChild(arrowSvg);

        // 3. Menu flutuante de opções (anexado diretamente ao body via Portal para nunca ser cortado por transforms ou drawers)
        const menu = document.createElement('ul');
        menu.className = 'custom-select-menu';

        this.renderOptions(select, menu, label);

        // 4. Insere no DOM e oculta o select original de forma acessível
        select.parentNode.insertBefore(wrapper, select);
        select.classList.add('custom-select-hidden');
        wrapper.appendChild(select);
        wrapper.appendChild(trigger);
        document.body.appendChild(menu); // Portal global no body

        wrapper._customMenu = menu;
        trigger._customMenu = menu;
        menu._customSelect = select;

        // 5. Interações de Abertura/Fechamento
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = menu.classList.contains('show');
            CustomSelect.closeAll();
            if (!isOpen) {
                CustomSelect.renderOptions(select, menu, label);
                menu.classList.add('show');
                trigger.classList.add('active');
                CustomSelect.positionMenu(trigger, menu);
            }
        });

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            } else if (e.key === 'Escape') {
                CustomSelect.closeAll();
            }
        });

        // 6. Sincronização se o valor mudar programaticamente
        select.addEventListener('change', () => {
            const opt = select.options[select.selectedIndex];
            if (opt) {
                label.textContent = opt.textContent;
                CustomSelect.updateSelectedClass(menu, select.value);
            }
        });
    },

    positionMenu(trigger, menu) {
        if (!trigger || !menu) return;
        const rect = trigger.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const menuHeight = Math.min(menu.scrollHeight || 180, 260);

        menu.style.position = 'fixed';
        menu.style.left = `${rect.left}px`;
        menu.style.width = `${rect.width}px`;
        menu.style.zIndex = '99999999';

        // Se houver espaço para baixo, abre embaixo. Caso contrário, abre para cima.
        if (rect.bottom + menuHeight + 10 <= viewportHeight || rect.top < menuHeight) {
            menu.style.top = `${rect.bottom + 4}px`;
            menu.style.bottom = 'auto';
        } else {
            menu.style.top = `${rect.top - menuHeight - 4}px`;
            menu.style.bottom = 'auto';
        }
    },

    renderOptions(select, menu, label) {
        menu.innerHTML = '';
        Array.from(select.options).forEach((opt) => {
            const li = document.createElement('li');
            li.className = 'custom-select-option' + (opt.selected ? ' selected' : '');
            li.dataset.value = opt.value;
            
            const spanText = document.createElement('span');
            spanText.textContent = opt.textContent;

            const checkSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            checkSvg.setAttribute('class', 'custom-select-check');
            checkSvg.setAttribute('viewBox', '0 0 24 24');
            checkSvg.setAttribute('fill', 'none');
            checkSvg.setAttribute('stroke', 'currentColor');
            checkSvg.setAttribute('stroke-width', '2.5');
            checkSvg.setAttribute('stroke-linecap', 'round');
            checkSvg.setAttribute('stroke-linejoin', 'round');
            checkSvg.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';

            li.appendChild(spanText);
            li.appendChild(checkSvg);

            li.addEventListener('click', (e) => {
                e.stopPropagation();
                select.value = opt.value;
                label.textContent = opt.textContent;
                CustomSelect.updateSelectedClass(menu, opt.value);
                CustomSelect.closeAll();

                // Dispara o evento change para compatibilidade com qualquer listener existente
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            menu.appendChild(li);
        });
    },

    updateSelectedClass(menu, val) {
        if (!menu) return;
        menu.querySelectorAll('.custom-select-option').forEach(li => {
            if (li.dataset.value === String(val)) {
                li.classList.add('selected');
            } else {
                li.classList.remove('selected');
            }
        });
    },

    sync(select) {
        if (!select) return;
        const wrapper = select.closest('.custom-select-wrapper');
        if (!wrapper) return;
        const label = wrapper.querySelector('.custom-select-trigger-label');
        const menu = wrapper._customMenu;
        const opt = select.options[select.selectedIndex];
        if (label && opt) {
            label.textContent = opt.textContent;
        }
        if (menu) {
            this.updateSelectedClass(menu, select.value);
        }
    },

    syncAll(container = document) {
        const selects = container.querySelectorAll('select.form-select, select.filter-select, select.custom-select');
        selects.forEach(select => this.sync(select));
    },

    closeAll() {
        document.querySelectorAll('.custom-select-menu.show').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.custom-select-trigger.active').forEach(t => t.classList.remove('active'));
    }
};

// Fecha todos ao clicar fora ou apertar ESC ou ao rolar a página
document.addEventListener('click', () => CustomSelect.closeAll());
window.addEventListener('scroll', (e) => {
    // Se o scroll não for dentro do próprio menu aberto, fecha
    if (e.target && e.target.classList && e.target.classList.contains('custom-select-menu')) return;
    CustomSelect.closeAll();
}, true);
window.addEventListener('resize', () => CustomSelect.closeAll());
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        CustomSelect.closeAll();
        closeClientDrawer();
        closeSupplierDrawer();
        closeEmployeeDrawer();
        closeProductDrawer();
        closeUserDrawer();
        closeRoleDrawer();
        if (typeof closeAuditDrawer === 'function') closeAuditDrawer();
        closeAuditActionModal();
    }
});

/**
 * 8.6. Funções Globais do Modal de Justificativa de Auditoria & Ações Críticas
 */
function openAuditActionModal(options = {}) {
    const overlay = document.getElementById('audit-action-modal-overlay');
    const form = document.getElementById('audit-action-form');
    if (!overlay || !form) return;

    const titleEl = document.getElementById('audit-modal-title');
    const msgEl = document.getElementById('audit-modal-message');
    const entityLabelEl = document.getElementById('audit-modal-entity-label');
    const recordTitleEl = document.getElementById('audit-modal-record-title');
    const recordDescEl = document.getElementById('audit-modal-record-desc');
    const submitBtn = document.getElementById('audit-modal-submit-btn');
    const submitTextEl = document.getElementById('audit-modal-submit-text');
    const iconEl = document.getElementById('audit-modal-icon');
    const hardDeleteWarning = document.getElementById('audit-modal-hard-delete-warning');
    const reasonInput = document.getElementById('audit-modal-reason-input');
    const reasonError = document.getElementById('audit-modal-reason-error');

    if (titleEl) titleEl.textContent = options.title || 'Confirmar Ação';
    if (msgEl) msgEl.textContent = options.message || 'Informe a justificativa para prosseguir.';
    if (entityLabelEl) entityLabelEl.textContent = options.entityLabel || 'Registro Afetado:';
    if (recordTitleEl) recordTitleEl.textContent = options.recordTitle || '-';
    if (recordDescEl) recordDescEl.textContent = options.recordDesc || '';
    if (submitTextEl) submitTextEl.textContent = options.submitText || 'Confirmar';

    form.action = options.submitUrl || '';
    document.getElementById('audit-modal-target-id').value = options.entityId || options.id || '';
    const entityInput = document.getElementById('audit-modal-entity');
    if (entityInput) entityInput.value = options.entity || '';
    document.getElementById('audit-modal-redirect-to').value = options.redirectTo || window.location.pathname;
    document.getElementById('audit-modal-action-type').value = options.actionType || '';

    const isReasonMandatory = !['create', 'update'].includes(options.actionType);
    const reasonReq = document.getElementById('audit-modal-reason-req');
    if (reasonReq) {
        reasonReq.style.display = isReasonMandatory ? 'inline' : 'none';
    }
    if (reasonInput) {
        reasonInput.value = '';
        reasonInput.required = isReasonMandatory;
        reasonInput.classList.remove('is-invalid');
    }
    if (reasonError) reasonError.style.display = 'none';

    if (hardDeleteWarning) {
        hardDeleteWarning.style.display = options.isHardDelete ? 'block' : 'none';
    }

    if (submitBtn) {
        if (options.isDanger) {
            submitBtn.className = 'btn btn-danger btn-sm';
            if (iconEl) {
                iconEl.style.background = '#fee2e2';
                iconEl.style.color = '#ef4444';
            }
        } else {
            submitBtn.className = 'btn btn-primary btn-sm';
            if (iconEl) {
                iconEl.style.background = '#e0f2fe';
                iconEl.style.color = '#0284c7';
            }
        }
    }

    overlay.style.display = 'flex';
    setTimeout(() => {
        if (reasonInput) reasonInput.focus();
    }, 50);
}

function closeAuditActionModal() {
    const overlay = document.getElementById('audit-action-modal-overlay');
    if (overlay) overlay.style.display = 'none';
}

function handleAuditFormSubmit(e) {
    const reasonInput = document.getElementById('audit-modal-reason-input');
    const reasonError = document.getElementById('audit-modal-reason-error');
    const actionType = document.getElementById('audit-modal-action-type')?.value;

    // Cadastro e Edição não exigem motivo. Todas as outras operações exigem obrigatoriamente.
    if (!['create', 'update'].includes(actionType)) {
        if (!reasonInput || reasonInput.value.trim().length < 5) {
            e.preventDefault();
            if (reasonInput) reasonInput.classList.add('is-invalid');
            if (reasonError) reasonError.style.display = 'block';
            return false;
        }
    }
    return true;
}

// Inicialização automática de componentes na carga do DOM
function initAppComponents() {
    initDelegatedActions();
    initClientInstantSearch();
    initSupplierInstantSearch();
    initEmployeeInstantSearch();
    initRowActionDropdowns();
    initProductPriceCombobox();
    initCommandPalette();
    initSingleKeyShortcuts();
    initCopyClicks();
    CustomSelect.init();
    WorkspaceTabs.init();
    DrawerDrafts.init();
    InputMasks.init();
    FormValidator.init();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAppComponents);
} else {
    initAppComponents();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Escapa texto para dentro de atributo HTML entre aspas duplas.
 * escapeHtml() NÃO escapa aspas — para data-payload/data-text use esta.
 */
function escapeAttr(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Ações delegadas (CSP fase 3): substitui onclick/onsubmit/onchange/oninput/onkeydown inline.
 * Uso no HTML: <button data-action="fechar">, <select data-action-change="filtrar">,
 * <input data-action-input="mascara">, <input data-action-keydown="so-digitos">,
 * <form data-action-submit="auditoria"> ou <form data-confirm="Tem certeza?">.
 * Handlers registrados uma única vez via DelegatedActions.on(); sobrevivem à navegação SPA.
 */
const DelegatedActions = {
    handlers: Object.create(null),
    on(name, fn) { this.handlers[name] = fn; },
};

function parseActionPayload(el) {
    try {
        return el.dataset.payload ? JSON.parse(el.dataset.payload) : null;
    } catch (e) {
        return null;
    }
}

function initDelegatedActions() {
    if (window.__delegatedActionsBound) return;
    window.__delegatedActionsBound = true;

    document.addEventListener('click', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-action]') : null;
        if (!el || el.disabled) return;
        const fn = DelegatedActions.handlers[el.dataset.action];
        if (typeof fn === 'function') fn(el, e);
    });

    document.addEventListener('change', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-action-change]') : null;
        if (!el || el.disabled) return;
        const fn = DelegatedActions.handlers[el.dataset.actionChange];
        if (typeof fn === 'function') fn(el, e);
    });

    document.addEventListener('input', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-action-input]') : null;
        if (!el || el.disabled) return;
        const fn = DelegatedActions.handlers[el.dataset.actionInput];
        if (typeof fn === 'function') fn(el, e);
    });

    document.addEventListener('keydown', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-action-keydown]') : null;
        if (!el || el.disabled) return;
        const fn = DelegatedActions.handlers[el.dataset.actionKeydown];
        if (typeof fn === 'function') fn(el, e);
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.hasAttribute('data-action-submit')) {
            const fn = DelegatedActions.handlers[form.dataset.actionSubmit];
            if (typeof fn === 'function' && fn(form, e) === false) e.preventDefault();
            return;
        }
        if (form.hasAttribute('data-confirm')) {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        }
    });
}

// Ações genéricas usadas por vários módulos
DelegatedActions.on('dismiss-parent', (el) => {
    if (el.parentElement) el.parentElement.remove();
});
DelegatedActions.on('copy-text', (el) => {
    if (navigator.clipboard) navigator.clipboard.writeText(el.dataset.text || '');
    showToast(el.dataset.toast || 'Copiado!');
});
DelegatedActions.on('change-page', (el) => {
    const fn = window[el.dataset.fn];
    if (typeof fn === 'function') fn(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('fill-city', (el) => {
    const city = document.getElementById('city-input');
    const state = document.getElementById('state-input');
    if (city) city.value = el.dataset.city || '';
    if (state) state.value = el.dataset.state || '';
});
DelegatedActions.on('close-audit-modal', () => closeAuditActionModal());
DelegatedActions.on('audit-form-submit', (form, e) => handleAuditFormSubmit(e));
DelegatedActions.on('workspace-goto', (el, e) => WorkspaceTabs.goTo(el.dataset.url, e));
DelegatedActions.on('workspace-close', (el, e) => WorkspaceTabs.close(el.dataset.id, e));
// Clientes (drawers em app.js; filtros/paginação em public/js/pages/clients-index.js)
DelegatedActions.on('open-client-drawer', (el) => {
    if (typeof openClientDrawer === 'function') openClientDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-client-drawer', (el) => {
    if (typeof closeClientDrawer === 'function') closeClientDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-client-filters', () => {
    if (typeof resetAllClientFilters === 'function') resetAllClientFilters();
});
DelegatedActions.on('change-client-page', (el) => {
    if (typeof changeClientPage === 'function') changeClientPage(parseInt(el.dataset.delta || '0', 10));
});
// Cilindros
DelegatedActions.on('open-cylinder-drawer', (el) => {
    if (typeof openCylinderDrawer === 'function') openCylinderDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-cylinder-drawer', (el) => {
    if (typeof closeCylinderDrawer === 'function') closeCylinderDrawer(el.dataset.cancel === '1');
});
// Fornecedores
DelegatedActions.on('open-supplier-drawer', (el) => {
    if (typeof openSupplierDrawer === 'function') openSupplierDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-supplier-drawer', (el) => {
    if (typeof closeSupplierDrawer === 'function') closeSupplierDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-supplier-filters', () => {
    if (typeof resetAllSupplierFilters === 'function') resetAllSupplierFilters();
});
DelegatedActions.on('change-supplier-page', (el) => {
    if (typeof changeSupplierPage === 'function') changeSupplierPage(parseInt(el.dataset.delta || '0', 10));
});
// Produtos
DelegatedActions.on('open-product-drawer', (el) => {
    if (typeof openProductDrawer === 'function') openProductDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-product-drawer', (el) => {
    if (typeof closeProductDrawer === 'function') closeProductDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-product-filters', () => {
    if (typeof resetAllFilters === 'function') resetAllFilters();
});
DelegatedActions.on('change-product-page', (el) => {
    if (typeof changeProductPage === 'function') changeProductPage(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('product-type-change', (el) => {
    if (typeof handleProductDrawerTypeChange === 'function') handleProductDrawerTypeChange(el.value);
});
DelegatedActions.on('product-unit-change', (el) => {
    if (typeof handleUnitChange === 'function') handleUnitChange(el.value);
});
DelegatedActions.on('product-price-input', () => {
    if (typeof calculateStandardPrice === 'function') calculateStandardPrice();
});
// Funcionários
DelegatedActions.on('open-employee-drawer', (el) => {
    if (typeof openEmployeeDrawer === 'function') openEmployeeDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-employee-drawer', (el) => {
    if (typeof closeEmployeeDrawer === 'function') closeEmployeeDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-employee-filters', () => {
    if (typeof resetAllEmployeeFilters === 'function') resetAllEmployeeFilters();
});
DelegatedActions.on('change-employee-page', (el) => {
    if (typeof changeEmployeePage === 'function') changeEmployeePage(parseInt(el.dataset.delta || '0', 10));
});
// Usuários
DelegatedActions.on('open-user-drawer', (el) => {
    if (typeof openUserDrawer === 'function') openUserDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-user-drawer', (el) => {
    if (typeof closeUserDrawer === 'function') closeUserDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-user-filters', () => {
    if (typeof resetAllUserFilters === 'function') resetAllUserFilters();
});
DelegatedActions.on('change-user-page', (el) => {
    if (typeof changeUserPage === 'function') changeUserPage(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('delete-user', (el) => {
    if (typeof deleteUser === 'function') deleteUser(el.dataset.id, el.dataset.name || '');
});
DelegatedActions.on('generate-password', () => {
    if (typeof generateRandomPassword === 'function') generateRandomPassword();
});
// Perfis
DelegatedActions.on('open-role-drawer', (el) => {
    if (typeof openRoleDrawer === 'function') openRoleDrawer(parseActionPayload(el));
});
DelegatedActions.on('close-role-drawer', (el) => {
    if (typeof closeRoleDrawer === 'function') closeRoleDrawer(el.dataset.cancel === '1');
});
DelegatedActions.on('reset-role-filters', () => {
    if (typeof resetAllRoleFilters === 'function') resetAllRoleFilters();
});
DelegatedActions.on('change-role-page', (el) => {
    if (typeof changeRolePage === 'function') changeRolePage(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('delete-role', (el) => {
    if (typeof deleteRole === 'function') deleteRole(el.dataset.id, el.dataset.name || '');
});
DelegatedActions.on('toggle-permissions', (el) => {
    if (typeof toggleAllPermissions === 'function') toggleAllPermissions(el.dataset.on === '1');
});
// Auditoria
DelegatedActions.on('open-audit-drawer', (el) => {
    if (typeof openAuditDrawer === 'function') openAuditDrawer(parseActionPayload(el));
});
DelegatedActions.on('audit-rollback', (el) => {
    if (typeof triggerAuditRollback === 'function') triggerAuditRollback(parseActionPayload(el));
});
DelegatedActions.on('audit-hard-delete', (el) => {
    if (typeof triggerAuditHardDelete === 'function') triggerAuditHardDelete(parseActionPayload(el));
});
DelegatedActions.on('close-audit-drawer', () => {
    if (typeof closeAuditDrawer === 'function') closeAuditDrawer();
});
DelegatedActions.on('reset-audit-filters', () => {
    if (typeof resetAuditFilters === 'function') resetAuditFilters();
});
DelegatedActions.on('change-audit-page', (el) => {
    if (typeof changeAuditPage === 'function') changeAuditPage(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('audit-filter-change', () => {
    if (typeof applyAuditFilters === 'function') applyAuditFilters();
});
DelegatedActions.on('audit-date-input', (el, e) => {
    if (typeof formatDateFilterInput === 'function') formatDateFilterInput(el, e);
});
DelegatedActions.on('audit-digits-only', (el, e) => {
    if (typeof allowOnlyDigitsAndNav === 'function') allowOnlyDigitsAndNav(e);
});
// Login (funções em public/js/pages/login.js; typeof evita erro fora da tela)
DelegatedActions.on('goto-card', (el) => {
    if (typeof goToCard === 'function') goToCard(parseInt(el.dataset.idx || '0', 10));
});
DelegatedActions.on('slide-step', (el) => {
    if (typeof slideInfinite === 'function') slideInfinite(parseInt(el.dataset.delta || '0', 10));
});
DelegatedActions.on('toggle-password', () => {
    if (typeof togglePasswordVisibility === 'function') togglePasswordVisibility();
});

/**
 * ==========================================================================
 * 9. Motor Reativo de Máscaras (Estilo Fintech / Bancos - 100% Livre)
 * ==========================================================================
 */
const InputMasks = {
    init(container = document) {
        container.querySelectorAll('input[data-mask]').forEach(input => {
            if (input._hasMaskAttached) return;
            input._hasMaskAttached = true;

            const maskType = input.dataset.mask;

            input.addEventListener('input', (e) => {
                this.applyMask(input, maskType, e);
            });

            // Aplica máscara inicial se já houver valor preenchido
            if (input.value) {
                this.applyMask(input, maskType);
            }
        });
    },

    applyMask(input, type, e = null) {
        if (!input) return;

        switch (type) {
            case 'cpf-cnpj':
                this.maskCpfCnpj(input);
                break;
            case 'cpf':
                this.maskCpf(input);
                break;
            case 'cnpj':
                this.maskCnpj(input);
                break;
            case 'phone':
                this.maskPhone(input);
                break;
            case 'date':
                this.maskDate(input);
                break;
            case 'money':
                this.maskMoney(input);
                break;
            case 'cep':
                this.maskCep(input);
                break;
            case 'ncm':
                this.maskNcm(input);
                break;
            case 'cnh':
                this.maskCnh(input);
                break;
        }
    },

    maskCpfCnpj(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 14);
        if (v.length <= 11) {
            if (v.length > 9) {
                v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');
            } else if (v.length > 6) {
                v = v.replace(/^(\d{3})(\d{3})(\d{1,3})$/, '$1.$2.$3');
            } else if (v.length > 3) {
                v = v.replace(/^(\d{3})(\d{1,3})$/, '$1.$2');
            }
        } else {
            if (v.length > 12) {
                v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})$/, '$1.$2.$3/$4-$5');
            } else if (v.length > 8) {
                v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})$/, '$1.$2.$3/$4');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{3})(\d{1,3})$/, '$1.$2.$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{1,3})$/, '$1.$2');
            }
        }
        input.value = v;
    },

    maskCpf(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 9) {
            v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');
        } else if (v.length > 6) {
            v = v.replace(/^(\d{3})(\d{3})(\d{1,3})$/, '$1.$2.$3');
        } else if (v.length > 3) {
            v = v.replace(/^(\d{3})(\d{1,3})$/, '$1.$2');
        }
        input.value = v;
    },

    maskCnpj(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 14);
        if (v.length > 12) {
            v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})$/, '$1.$2.$3/$4-$5');
        } else if (v.length > 8) {
            v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})$/, '$1.$2.$3/$4');
        } else if (v.length > 5) {
            v = v.replace(/^(\d{2})(\d{3})(\d{1,3})$/, '$1.$2.$3');
        } else if (v.length > 2) {
            v = v.replace(/^(\d{2})(\d{1,3})$/, '$1.$2');
        }
        input.value = v;
    },

    maskPhone(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 10) {
            v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        } else if (v.length > 6) {
            v = v.replace(/^(\d{2})(\d{4})(\d{1,4})$/, '($1) $2-$3');
        } else if (v.length > 2) {
            v = v.replace(/^(\d{2})(\d{1,4})$/, '($1) $2');
        } else if (v.length > 0) {
            v = v.replace(/^(\d{1,2})$/, '($1');
        }
        input.value = v;
    },

    maskDate(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 4) {
            v = v.replace(/^(\d{2})(\d{2})(\d{1,4})$/, '$1/$2/$3');
        } else if (v.length > 2) {
            v = v.replace(/^(\d{2})(\d{1,2})$/, '$1/$2');
        }
        input.value = v;
    },

    maskMoney(input) {
        let v = input.value.replace(/\D/g, '');
        if (v === '') {
            input.value = '';
            return;
        }
        let num = parseInt(v, 10) / 100;
        input.value = num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    maskCep(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) {
            v = v.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
        }
        input.value = v;
    },

    maskNcm(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 6) {
            v = v.replace(/^(\d{4})(\d{2})(\d{1,2})$/, '$1.$2.$3');
        } else if (v.length > 4) {
            v = v.replace(/^(\d{4})(\d{1,2})$/, '$1.$2');
        }
        input.value = v;
    },

    maskCnh(input) {
        input.value = input.value.replace(/\D/g, '').slice(0, 11);
    }
};

/**
 * ==========================================================================
 * 10. Validador Central e Verificação de Unicidade
 * ==========================================================================
 */
const FormValidator = {
    init(container = document) {
        // Validação no Blur para campos com verificação de unicidade ou documentos
        container.querySelectorAll('input[data-entity], input[data-mask="cpf-cnpj"], input[data-mask="cpf"], input[data-mask="cnpj"]').forEach(input => {
            if (input._hasValidatorAttached) return;
            input._hasValidatorAttached = true;

            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                this.clearError(input);
            });
        });

        // Intercepta envio de todos os formulários de Drawer
        const formIds = [
            'client-drawer-form',
            'supplier-drawer-form',
            'employee-drawer-form',
            'product-drawer-form',
            'user-drawer-form',
            'role-drawer-form'
        ];

        formIds.forEach(id => {
            const form = document.getElementById(id);
            if (form && !form._hasSubmitValidator) {
                form._hasSubmitValidator = true;
                form.addEventListener('submit', (e) => {
                    const isValid = this.validateForm(form);
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
        });
    },

    setError(input, message) {
        if (!input) return;
        input.classList.add('is-invalid');

        let feedback = input.parentNode.querySelector('.form-feedback-error');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'form-feedback-error';
            input.parentNode.appendChild(feedback);
        }
        feedback.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> <span>${escapeHtml(message)}</span>`;
    },

    clearError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        const feedback = input.parentNode.querySelector('.form-feedback-error');
        if (feedback) {
            feedback.remove();
        }
    },

    validateField(input) {
        const val = input.value.trim();
        const mask = input.dataset.mask;
        const entity = input.dataset.entity;
        const field = input.dataset.field;

        // 1. Validação de CPF / CNPJ Matemático
        if (mask === 'cpf-cnpj' && val) {
            const digits = val.replace(/\D/g, '');
            if (digits.length === 11) {
                if (!this.isValidCpf(digits)) {
                    this.setError(input, 'CPF informado é inválido.');
                    return false;
                }
            } else if (digits.length === 14) {
                if (!this.isValidCnpj(digits)) {
                    this.setError(input, 'CNPJ informado é inválido.');
                    return false;
                }
            } else {
                this.setError(input, 'Documento incompleto (deve ter 11 ou 14 dígitos).');
                return false;
            }
        } else if (mask === 'cpf' && val) {
            const digits = val.replace(/\D/g, '');
            if (digits.length !== 11 || !this.isValidCpf(digits)) {
                this.setError(input, 'CPF informado é inválido.');
                return false;
            }
        } else if (mask === 'cnpj' && val) {
            const digits = val.replace(/\D/g, '');
            if (digits.length !== 14 || !this.isValidCnpj(digits)) {
                this.setError(input, 'CNPJ informado é inválido.');
                return false;
            }
        }

        // 2. Validação de Unicidade Assíncrona (se o campo tiver data-entity)
        if (entity && field && val) {
            const form = input.closest('form');
            const idInput = form ? form.querySelector('input[name="id"]') : null;
            const excludeId = idInput && idInput.value ? idInput.value : '';

            fetch(`/api/validate-unique?entity=${encodeURIComponent(entity)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(val)}&exclude_id=${encodeURIComponent(excludeId)}`)
                .then(r => r.json())
                .then(res => {
                    if (res && res.available === false) {
                        this.setError(input, res.message || 'Este valor já está em uso no sistema.');
                    } else if (res && res.available === true) {
                        this.clearError(input);
                    }
                })
                .catch(() => {});
        }

        this.clearError(input);
        return true;
    },

    validateForm(form) {
        let isValid = true;
        let firstInvalidInput = null;

        // 1. Campos obrigatórios nativos
        const requiredInputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        requiredInputs.forEach(input => {
            if (!input.value || !input.value.trim()) {
                isValid = false;
                this.setError(input, 'Este campo é obrigatório.');
                if (!firstInvalidInput) firstInvalidInput = input;
            }
        });

        // 2. Validação de Documento (CPF / CNPJ)
        const docInputs = form.querySelectorAll('input[data-mask="cpf-cnpj"], input[data-mask="cpf"], input[data-mask="cnpj"]');
        docInputs.forEach(input => {
            const val = input.value.trim();
            if (val) {
                const digits = val.replace(/\D/g, '');
                if (input.dataset.mask === 'cpf-cnpj') {
                    if (digits.length === 11 && !this.isValidCpf(digits)) {
                        isValid = false;
                        this.setError(input, 'CPF informado é matematicamente inválido.');
                        if (!firstInvalidInput) firstInvalidInput = input;
                    } else if (digits.length === 14 && !this.isValidCnpj(digits)) {
                        isValid = false;
                        this.setError(input, 'CNPJ informado é matematicamente inválido.');
                        if (!firstInvalidInput) firstInvalidInput = input;
                    } else if (digits.length !== 11 && digits.length !== 14) {
                        isValid = false;
                        this.setError(input, 'Documento incompleto (11 ou 14 dígitos).');
                        if (!firstInvalidInput) firstInvalidInput = input;
                    }
                } else if (input.dataset.mask === 'cpf' && (digits.length !== 11 || !this.isValidCpf(digits))) {
                    isValid = false;
                    this.setError(input, 'CPF informado é inválido.');
                    if (!firstInvalidInput) firstInvalidInput = input;
                } else if (input.dataset.mask === 'cnpj' && (digits.length !== 14 || !this.isValidCnpj(digits))) {
                    isValid = false;
                    this.setError(input, 'CNPJ informado é inválido.');
                    if (!firstInvalidInput) firstInvalidInput = input;
                }
            }
        });

        // 3. Validação de Senhas no Cadastro de Usuário
        const passInput = form.querySelector('input[name="password"]');
        const confirmInput = form.querySelector('input[name="password_confirm"]');
        const idInput = form.querySelector('input[name="id"]');
        const isEditing = idInput && idInput.value !== '';

        if (passInput && confirmInput) {
            const pass = passInput.value;
            const confirm = confirmInput.value;

            if (!isEditing && (!pass || pass.length < 8)) {
                isValid = false;
                this.setError(passInput, 'A senha deve conter no mínimo 8 caracteres.');
                if (!firstInvalidInput) firstInvalidInput = passInput;
            } else if (pass && pass.length < 8) {
                isValid = false;
                this.setError(passInput, 'A nova senha deve conter no mínimo 8 caracteres.');
                if (!firstInvalidInput) firstInvalidInput = passInput;
            }

            if (pass !== confirm) {
                isValid = false;
                this.setError(confirmInput, 'A confirmação de senha não confere com a senha digitada.');
                if (!firstInvalidInput) firstInvalidInput = confirmInput;
            }
        }

        // Se houver erro impeditivo já marcado com .is-invalid
        const invalidFields = form.querySelectorAll('.is-invalid');
        if (invalidFields.length > 0) {
            isValid = false;
            if (!firstInvalidInput) firstInvalidInput = invalidFields[0];
        }

        if (!isValid && firstInvalidInput) {
            firstInvalidInput.focus();
        }

        return isValid;
    },

    isValidCpf(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let sum = 0, rem;
        for (let i = 1; i <= 9; i++) sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        rem = (sum * 10) % 11;
        if (rem === 10 || rem === 11) rem = 0;
        if (rem !== parseInt(cpf.substring(9, 10))) return false;
        sum = 0;
        for (let i = 1; i <= 10; i++) sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        rem = (sum * 10) % 11;
        if (rem === 10 || rem === 11) rem = 0;
        return rem === parseInt(cpf.substring(10, 11));
    },

    isValidCnpj(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
        let size = cnpj.length - 2;
        let numbers = cnpj.substring(0, size);
        let digits = cnpj.substring(size);
        let sum = 0, pos = size - 7;
        for (let i = size; i >= 1; i--) {
            sum += parseInt(numbers.charAt(size - i)) * pos--;
            if (pos < 2) pos = 9;
        }
        let result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        if (result !== parseInt(digits.charAt(0))) return false;
        size = size + 1;
        numbers = cnpj.substring(0, size);
        sum = 0; pos = size - 7;
        for (let i = size; i >= 1; i--) {
            sum += parseInt(numbers.charAt(size - i)) * pos--;
            if (pos < 2) pos = 9;
        }
        result = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        return result === parseInt(digits.charAt(1));
    }
};



