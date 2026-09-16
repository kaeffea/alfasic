<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Funcionário -->
<div id="employee-drawer-overlay" class="drawer-overlay" data-action="close-employee-drawer"></div>

<div id="employee-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="employee-drawer-heading">Novo Funcionário</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="employee-drawer-subheading">Preencha as informações profissionais e cadastrais</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-employee-drawer">&times;</button>
    </div>

    <form method="POST" action="/employees/store" id="employee-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="employee-drawer-id" value="">

        <div class="drawer-body">
            <!-- 1. Dados Pessoais & Identificação -->
            <div class="form-group">
                <label class="form-label">Nome Completo <span class="required">*</span></label>
                <input type="text" name="name" id="employee-drawer-name" required placeholder="Ex: Carlos Eduardo de Oliveira" class="form-control">
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">CPF</label>
                    <input type="text" name="document" id="employee-drawer-document" data-mask="cpf" data-entity="employee" data-field="document" placeholder="000.000.000-00" class="form-control font-mono">
                </div>
                <div>
                    <label class="form-label">RG</label>
                    <input type="text" name="rg" id="employee-drawer-rg" placeholder="0000000 SSP/AL" class="form-control font-mono">
                </div>
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">Data de Nascimento</label>
                    <input type="date" name="birth_date" id="employee-drawer-birth-date" class="form-control font-mono">
                </div>
                <div>
                    <label class="form-label">Naturalidade / UF</label>
                    <input type="text" name="birth_city" id="employee-drawer-birth-city" placeholder="Ex: Maceió / AL" class="form-control">
                </div>
            </div>

            <!-- 2. Lotação, Cargo & Vínculo -->
            <div class="form-section-divider">
                <div class="form-section-title">Lotação & Cargo</div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Cargo / Função <span class="required">*</span></label>
                        <input type="text" name="role_title" id="employee-drawer-role" required list="employee-roles-suggestions" placeholder="Ex: Desenvolvedor, Motorista Entregador..." class="form-control">
                        <datalist id="employee-roles-suggestions">
                            <option value="Desenvolvedor">
                            <option value="Motorista Entregador">
                            <option value="Operador de Usina">
                            <option value="Ajudante de Carga / Descarga">
                            <option value="Vendedor Comercial">
                            <option value="Técnico de Manutenção / Válvulas">
                            <option value="Administrativo / Financeiro">
                            <option value="Gerente Operacional">
                            <option value="Diretor Geral">
                            <option value="Supervisor de Logística">
                        </datalist>
                    </div>
                    <div>
                        <label class="form-label">Filial / Unidade</label>
                        <select name="branch" id="employee-drawer-branch" class="form-select">
                            <option value="Matriz - São Miguel dos Campos">Matriz - São Miguel dos Campos</option>
                            <option value="Filial - Maceió">Filial - Maceió</option>
                            <option value="Filial - Arapiraca">Filial - Arapiraca</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Departamento / Setor</label>
                        <select name="department" id="employee-drawer-department" class="form-select">
                            <option value="Logística & Frota">Logística & Frota</option>
                            <option value="Usina & Enchimento">Usina & Enchimento</option>
                            <option value="Comercial & Vendas">Comercial & Vendas</option>
                            <option value="Manutenção & Qualidade">Manutenção & Qualidade</option>
                            <option value="Administrativo & Financeiro">Administrativo & Financeiro</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Data de Admissão</label>
                        <input type="date" name="hire_date" id="employee-drawer-hire-date" class="form-control font-mono">
                    </div>
                </div>
            </div>

            <!-- 3. Habilitação & Cargas Perigosas (Gases) -->
            <div class="form-section-divider">
                <div class="form-section-title">Habilitação (CNH & MOPP)</div>
                
                <div class="form-grid-2-1">
                    <div>
                        <label class="form-label">Número da CNH</label>
                        <input type="text" name="driver_license" id="employee-drawer-cnh" data-mask="cnh" placeholder="00000000000" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Categoria</label>
                        <select name="driver_license_category" id="employee-drawer-cnh-cat" class="form-select font-mono" style="font-weight: 700;">
                            <option value="">Não possui</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                            <option value="AB">AB</option>
                            <option value="AD">AD</option>
                            <option value="AE">AE</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Validade da CNH</label>
                        <input type="date" name="driver_license_expiry" id="employee-drawer-cnh-expiry" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Certificações / NRs</label>
                        <input type="text" name="certifications" id="employee-drawer-certs" placeholder="Ex: MOPP, NR-13, NR-20" class="form-control">
                    </div>
                </div>
            </div>

            <!-- 4. Contato & Emergência -->
            <div class="form-section-divider">
                <div class="form-section-title">Contatos & Emergência</div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Telefone / WhatsApp <span class="required">*</span></label>
                        <input type="text" name="phone" id="employee-drawer-phone" data-mask="phone" required placeholder="(82) 99999-9999" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" id="employee-drawer-email" data-entity="employee" data-field="email" placeholder="carlos@alfagas.com.br" class="form-control">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Contato de Emergência</label>
                        <input type="text" name="emergency_contact_name" id="employee-drawer-em-name" placeholder="Nome do parente" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Telefone de Emergência</label>
                        <input type="text" name="emergency_contact_phone" id="employee-drawer-em-phone" placeholder="(82) 99999-9999" class="form-control font-mono">
                    </div>
                </div>
            </div>

            <!-- 5. Custos & Financeiro -->
            <div class="form-section-divider">
                <div class="form-section-title">Custos & Financeiro (Opcional)</div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Salário Base (R$)</label>
                        <input type="number" step="0.01" name="base_salary" id="employee-drawer-salary" placeholder="0,00" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Benefícios / VT / VR (R$)</label>
                        <input type="number" step="0.01" name="benefits_cost" id="employee-drawer-benefits" placeholder="0,00" class="form-control font-mono">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Chave PIX (Diárias / Comissões)</label>
                    <input type="text" name="pix_key" id="employee-drawer-pix" placeholder="CPF, e-mail ou chave aleatória" class="form-control font-mono">
                </div>
            </div>

            <!-- 6. Endereço Residencial -->
            <div class="form-section-divider">
                <div class="form-section-title">Endereço Residencial</div>

                <div class="form-grid-2-1">
                    <div>
                        <label class="form-label">Logradouro</label>
                        <input type="text" name="address" id="employee-drawer-address" placeholder="Rua / Avenida" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" id="employee-drawer-address-number" placeholder="Nº ou S/N" class="form-control">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div>
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" id="employee-drawer-neighborhood" placeholder="Centro" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" id="employee-drawer-city" value="São Miguel dos Campos" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">UF</label>
                        <input type="text" name="state" id="employee-drawer-state" value="AL" maxlength="2" class="form-control" style="text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <!-- 7. Status & Observações Internas -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Status do Colaborador</label>
                        <select name="is_active" id="employee-drawer-is-active" class="form-select">
                            <option value="1">Ativo (Em Atividade Normal)</option>
                            <option value="0">Inativo (Afastado / Desligado)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observações Internas</label>
                    <textarea name="notes" id="employee-drawer-notes" rows="2" placeholder="Turno de trabalho, rotas habituais, detalhes adicionais, etc." class="form-textarea"></textarea>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-employee-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="employee-drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar</button>
        </div>
    </form>
</div>
