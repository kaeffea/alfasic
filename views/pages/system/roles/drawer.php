<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Perfil de Permissões -->
<div id="role-drawer-overlay" class="drawer-overlay" data-action="close-role-drawer"></div>

<div id="role-drawer" class="drawer-panel" style="width: 680px; max-width: 95vw;">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="role-drawer-heading">Novo Perfil de Acesso</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="role-drawer-subheading">Configure as permissões granulares de acesso às telas e ações do sistema</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-role-drawer">&times;</button>
    </div>

    <form method="POST" action="/roles/store" id="role-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="role-drawer-id" value="">

        <div class="drawer-body" style="padding: 20px 24px;">
            <!-- 1. Dados Básicos do Perfil -->
            <div class="form-group">
                <label class="form-label">Nome do Perfil <span class="required">*</span></label>
                <input type="text" name="name" id="role-drawer-name" data-entity="role" data-field="name" required placeholder="Ex: Vendedor Comercial / Operador de Usina" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição Operacional</label>
                <input type="text" name="description" id="role-drawer-desc" placeholder="Ex: Acesso a emissão de pedidos e consulta de clientes e preços" class="form-control">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; margin-top: 8px; border-top: 1px solid var(--border-color);">
                <div class="form-section-title" style="margin-bottom: 0;">Matriz de Permissões por Ação</div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline" data-action="toggle-permissions" data-on="1" style="padding: 4px 10px; font-size: 11px; height: 26px; border-radius: 4px; font-weight: 600;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Marcar Todas</span>
                    </button>
                    <button type="button" class="btn btn-outline" data-action="toggle-permissions" data-on="0" style="padding: 4px 10px; font-size: 11px; height: 26px; border-radius: 4px; font-weight: 600;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span>Desmarcar Todas</span>
                    </button>
                </div>
            </div>

            <!-- 2. Matriz Granular de Ações para os Módulos Ativos do Sistema -->
            <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 12px;">
                
                <!-- 1. Módulo Clientes -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">
                        Clientes
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.view" class="perm-check" style="accent-color: #0284c7;">
                            <span>Visualizar Clientes</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.create" class="perm-check" style="accent-color: #0284c7;">
                            <span>Cadastrar Clientes</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.edit" class="perm-check" style="accent-color: #0284c7;">
                            <span>Editar Cadastros</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.delete" class="perm-check" style="accent-color: #0284c7;">
                            <span>Excluir Clientes</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.prices_view" class="perm-check" style="accent-color: #0284c7;">
                            <span>Ver Preços</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="clients.prices_edit" class="perm-check" style="accent-color: #0284c7;">
                            <span>Alterar Preços</span>
                        </label>
                    </div>
                </div>

                <!-- 2. Módulo Fornecedores -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">
                        Fornecedores
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="suppliers.view" class="perm-check" style="accent-color: #0284c7;">
                            <span>Visualizar Fornecedores</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="suppliers.create" class="perm-check" style="accent-color: #0284c7;">
                            <span>Cadastrar Fornecedores</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="suppliers.edit" class="perm-check" style="accent-color: #0284c7;">
                            <span>Editar Fornecedores</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="suppliers.delete" class="perm-check" style="accent-color: #0284c7;">
                            <span>Excluir Fornecedores</span>
                        </label>
                    </div>
                </div>

                <!-- 3. Módulo Funcionários -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">
                        Funcionários
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="employees.view" class="perm-check" style="accent-color: #0284c7;">
                            <span>Visualizar Funcionários</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="employees.create" class="perm-check" style="accent-color: #0284c7;">
                            <span>Cadastrar Funcionários</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="employees.edit" class="perm-check" style="accent-color: #0284c7;">
                            <span>Editar Funcionários</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="employees.delete" class="perm-check" style="accent-color: #0284c7;">
                            <span>Excluir Funcionários</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Módulo Produtos -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">
                        Produtos
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="products.view" class="perm-check" style="accent-color: #0284c7;">
                            <span>Visualizar Produtos</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="products.create" class="perm-check" style="accent-color: #0284c7;">
                            <span>Cadastrar Produtos</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="products.edit" class="perm-check" style="accent-color: #0284c7;">
                            <span>Editar Produtos</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="products.delete" class="perm-check" style="accent-color: #0284c7;">
                            <span>Excluir Produtos</span>
                        </label>
                    </div>
                </div>

                <!-- 5. Módulo Sistema & Administração -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">
                        Sistema & Administração
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="users.manage" class="perm-check" style="accent-color: #0284c7;">
                            <span>Gerenciar Usuários</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="roles.manage" class="perm-check" style="accent-color: #0284c7;">
                            <span>Gerenciar Perfis & Permissões</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 3. Status do Perfil -->
            <div class="form-section-divider">
                <div class="form-section-title">Status do Perfil</div>
                <div class="form-group" style="max-width: 260px; margin-bottom: 0;">
                    <select name="is_active" id="role-drawer-status" class="form-select">
                        <option value="1">Ativo (Permissões Vigentes)</option>
                        <option value="0">Inativo (Acesso Suspenso)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-role-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="role-drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar Perfil</button>
        </div>
    </form>
</div>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/roles-drawer.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
