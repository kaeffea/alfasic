<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Fornecedor -->
<div id="supplier-drawer-overlay" class="drawer-overlay" data-action="close-supplier-drawer"></div>

<div id="supplier-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="supplier-drawer-heading">Novo Fornecedor</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="supplier-drawer-subheading">Preencha as informações cadastrais da usina ou parceiro</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-supplier-drawer">&times;</button>
    </div>

    <form method="POST" action="/suppliers/store" id="supplier-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="supplier-drawer-id" value="">

        <div class="drawer-body">
            <!-- 1. Dados Principais -->
            <div class="form-group">
                <label class="form-label">Razão Social / Nome Completo <span class="required">*</span></label>
                <input type="text" name="name" id="supplier-drawer-name" required placeholder="Ex: White Martins Gases Industriais Ltda" class="form-control">
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">Nome Fantasia</label>
                    <input type="text" name="trade_name" id="supplier-drawer-trade-name" placeholder="Ex: White Martins" class="form-control">
                </div>
                <div>
                    <label class="form-label">Tipo de Fornecedor</label>
                    <select name="supplier_type" id="supplier-drawer-type" class="form-select">
                        <option value="usina">Usina de Gases</option>
                        <option value="equipamentos">Equipamentos & Acessórios</option>
                        <option value="oficina">Peças & Frota / Oficina</option>
                        <option value="servicos">Serviços & Terceirizados</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">CNPJ / CPF</label>
                    <input type="text" name="document" id="supplier-drawer-document" data-mask="cpf-cnpj" data-entity="supplier" data-field="document" placeholder="00.000.000/0000-00" class="form-control font-mono">
                </div>
                <div>
                    <label class="form-label">Inscrição Estadual</label>
                    <input type="text" name="state_registration" id="supplier-drawer-state-reg" placeholder="Isento ou Nº" class="form-control font-mono">
                </div>
            </div>

            <!-- 2. Contato & Endereço -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Pessoa de Contato</label>
                        <input type="text" name="contact_person" id="supplier-drawer-contact" placeholder="Responsável / Setor" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Telefone / WhatsApp</label>
                        <input type="text" name="phone" id="supplier-drawer-phone" data-mask="phone" placeholder="(82) 3315-0000" class="form-control font-mono">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" id="supplier-drawer-email" data-entity="supplier" data-field="email" placeholder="comercial@fornecedor.com.br" class="form-control">
                </div>

                <div class="form-grid-2-1">
                    <div>
                        <label class="form-label">Endereço / Logradouro</label>
                        <input type="text" name="address" id="supplier-drawer-address" placeholder="Av. Principal" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" id="supplier-drawer-address-number" placeholder="S/N ou Nº" class="form-control">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div>
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" id="supplier-drawer-neighborhood" placeholder="Centro / Distrito Industrial" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" id="supplier-drawer-city" value="Maceió" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">UF</label>
                        <input type="text" name="state" id="supplier-drawer-state" value="AL" maxlength="2" class="form-control" style="text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <!-- 3. Status & Observações -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Status do Fornecedor</label>
                        <select name="is_active" id="supplier-drawer-is-active" class="form-select">
                            <option value="1">Ativo (Homologado para Compras)</option>
                            <option value="0">Inativo (Bloqueado)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observações Internas</label>
                    <textarea name="notes" id="supplier-drawer-notes" rows="2" placeholder="Informações operacionais, horários de carregamento na usina, restrições, etc." class="form-textarea"></textarea>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-supplier-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="supplier-drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar</button>
        </div>
    </form>
</div>
