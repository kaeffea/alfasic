<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Cliente -->
<div id="client-drawer-overlay" class="drawer-overlay" data-action="close-client-drawer"></div>

<div id="client-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="drawer-heading">Novo Cadastro de Cliente</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="drawer-subheading">Preencha as informações cadastrais e comerciais</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-client-drawer">&times;</button>
    </div>

    <form method="POST" action="/clients/store" id="client-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="drawer-client-id" value="">

        <div class="drawer-body">
            <!-- 1. Dados Principais -->
            <div class="form-group">
                <label class="form-label">Razão Social / Nome Completo <span class="required">*</span></label>
                <input type="text" name="name" id="drawer-name" required placeholder="Ex: Hospital Dr. Clodolfo Rodrigues" class="form-control">
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">Nome Fantasia</label>
                    <input type="text" name="trade_name" id="drawer-trade-name" placeholder="Ex: Hospital Regional" class="form-control">
                </div>
                <div>
                    <label class="form-label">Tipo de Cliente</label>
                    <select name="client_type" id="drawer-client-type" class="form-select">
                        <option value="company">Pessoa Jurídica (Privada)</option>
                        <option value="government">Órgão Público / Prefeitura / Hospital</option>
                        <option value="individual">Pessoa Física</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">CNPJ / CPF</label>
                    <input type="text" name="document" id="drawer-document" data-mask="cpf-cnpj" data-entity="client" data-field="document" placeholder="00.000.000/0000-00" class="form-control font-mono">
                </div>
                <div>
                    <label class="form-label">Inscrição Estadual</label>
                    <input type="text" name="state_registration" id="drawer-state-reg" placeholder="Isento ou Nº" class="form-control font-mono">
                </div>
            </div>

            <!-- 2. Contato & Endereço -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Pessoa de Contato</label>
                        <input type="text" name="contact_person" id="drawer-contact" placeholder="Responsável / Setor" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Telefone / WhatsApp</label>
                        <input type="text" name="phone" id="drawer-phone" data-mask="phone" placeholder="(82) 99999-9999" class="form-control font-mono">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" id="drawer-email" data-entity="client" data-field="email" placeholder="financeiro@cliente.com.br" class="form-control">
                </div>

                <div class="form-grid-2-1">
                    <div>
                        <label class="form-label">Endereço / Logradouro</label>
                        <input type="text" name="address" id="drawer-address" placeholder="Rua / Avenida" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" id="drawer-address-number" placeholder="S/N ou Nº" class="form-control">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div>
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" id="drawer-neighborhood" placeholder="Centro" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" id="drawer-city" value="São Miguel dos Campos" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">UF</label>
                        <input type="text" name="state" id="drawer-state" value="AL" maxlength="2" class="form-control" style="text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <!-- 3. Condições Comerciais -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Prazo de Pagamento</label>
                        <input type="text" name="payment_terms" id="drawer-payment-terms" value="À Vista" placeholder="Ex: 30 dias / 15/30 DDL" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Forma de Cobrança</label>
                        <input type="text" name="billing_method" id="drawer-billing-method" value="Boleto Bancário" placeholder="Ex: Boleto / PIX" class="form-control">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Status do Cliente</label>
                        <select name="is_active" id="drawer-is-active" class="form-select">
                            <option value="1">Ativo (Operações Liberadas)</option>
                            <option value="0">Inativo (Bloqueado para Pedidos)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observações Internas</label>
                    <textarea name="notes" id="drawer-notes" rows="2" placeholder="Informações de acesso à usina, restrições de horários de descarga, etc." class="form-textarea"></textarea>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-client-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar</button>
        </div>
    </form>
</div>
