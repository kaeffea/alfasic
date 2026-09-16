<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Usuário -->
<div id="user-drawer-overlay" class="drawer-overlay" data-action="close-user-drawer"></div>

<div id="user-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="user-drawer-heading">Novo Usuário</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="user-drawer-subheading">Preencha os dados de acesso e vincule a um colaborador</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-user-drawer">&times;</button>
    </div>

    <form method="POST" action="/users/store" id="user-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="user-drawer-id" value="">

        <div class="drawer-body">
            <!-- 1. Vínculo Obrigatório com Funcionário -->
            <div class="form-group">
                <label class="form-label">Colaborador Vinculado</label>
                <select name="employee_id" id="user-drawer-employee" class="form-select">
                    <option value="">Nenhum (Usuário Externo / Sistema)</option>
                    <?php if (!empty($employees)): ?>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" data-name="<?= htmlspecialchars($emp['name']) ?>" data-email="<?= htmlspecialchars($emp['email'] ?? '') ?>">
                                <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['role_title'] ?? 'Funcionário') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 2. Credenciais de Acesso -->
            <div class="form-section-divider">
                <div class="form-section-title">Credenciais de Acesso</div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Nome de Usuário (Login) <span class="required">*</span></label>
                        <input type="text" name="username" id="user-drawer-username" data-entity="user" data-field="username" required placeholder="Ex: kaue.ferreira" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Perfil de Permissão <span class="required">*</span></label>
                        <select name="role_id" id="user-drawer-role" required class="form-select">
                            <option value="">Selecione um perfil...</option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail Corporativo <span class="required">*</span></label>
                    <input type="email" name="email" id="user-drawer-email" data-entity="user" data-field="email" required placeholder="usuario@alfagas.com.br" class="form-control">
                </div>
            </div>

            <!-- 3. Senha de Acesso -->
            <div class="form-section-divider">
                <div class="form-section-title">Senha de Acesso</div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label" id="user-drawer-password-label">Senha <span class="required">*</span></label>
                        <input type="password" name="password" id="user-drawer-password" placeholder="Mínimo 8 caracteres" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Confirmar Senha <span class="required">*</span></label>
                        <input type="password" name="password_confirm" id="user-drawer-password-confirm" placeholder="Repita a senha" class="form-control">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                    <button type="button" class="btn btn-outline btn-sm" data-action="generate-password" style="font-size: 11px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-6 6l-3 3-4-4 3-3m7-7l4 4-10 10-4-4 10-10z"/></svg>
                        <span>Gerar Senha Segura</span>
                    </button>
                </div>
            </div>

            <!-- 4. Status da Conta -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Status da Conta</label>
                        <select name="is_active" id="user-drawer-status" class="form-select">
                            <option value="1">Ativo (Acesso Liberado)</option>
                            <option value="0">Inativo (Acesso Suspenso)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-user-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="user-drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar Usuário</button>
        </div>
    </form>
</div>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/users-drawer.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
