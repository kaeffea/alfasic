<?php
/**
 * View: Detalhes do Funcionário (Perfil Profissional, CNH, Contato e Endereço)
 */
$activeTab = 'show';
require __DIR__ . '/profile_header.php';
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Bloco 1: Identificação Profissional & Lotação -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Identificação & Vínculo Profissional
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="grid-column: span 2;">
                <div class="info-item-label">Nome Completo</div>
                <div class="info-item-value" style="font-size: 14px; font-weight: 700; color: var(--text-main);">
                    <?= htmlspecialchars($employee['name']) ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Cargo / Função</div>
                <div class="info-item-value" style="font-weight: 700; color: var(--text-main);">
                    <?= htmlspecialchars($employee['role_title'] ?? 'Operacional') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Filial / Unidade</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['branch'] ?? 'Matriz - São Miguel dos Campos') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Departamento</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['department'] ?? 'Logística & Frota') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Data de Admissão</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= !empty($employee['hire_date']) ? date('d/m/Y', strtotime($employee['hire_date'])) : '-' ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">CPF</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 600;">
                    <?php if (!empty($employee['document'])): ?>
                        <span class="copy-click" data-copy="<?= htmlspecialchars($employee['document']) ?>" title="Clique para copiar" style="cursor: pointer; border-bottom: 1px dashed var(--border-color);">
                            <?= htmlspecialchars($employee['document']) ?>
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">RG</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($employee['rg'] ?: 'Não informado') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Data de Nascimento</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= !empty($employee['birth_date']) ? date('d/m/Y', strtotime($employee['birth_date'])) : '-' ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Naturalidade</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['birth_city'] ?: 'São Miguel dos Campos') ?><?= !empty($employee['birth_state']) ? '/' . htmlspecialchars($employee['birth_state']) : '' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bloco 2: Contatos & Emergência -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Contatos, Emergência & Endereço
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <div class="info-item-label">Telefone / WhatsApp</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 600;">
                    <?= htmlspecialchars($employee['phone'] ?: '-') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">E-mail</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['email'] ?: '-') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Contato de Emergência</div>
                <div class="info-item-value" style="font-weight: 600;">
                    <?= htmlspecialchars($employee['emergency_contact_name'] ?: 'Não informado') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Telefone de Emergência</div>
                <div class="info-item-value" style="font-family: var(--font-mono); color: #dc2626; font-weight: 600;">
                    <?= htmlspecialchars($employee['emergency_contact_phone'] ?: '-') ?>
                </div>
            </div>

            <div style="grid-column: span 2;">
                <div class="info-item-label">Endereço Residencial</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['address'] ?: 'Não informado') ?>, <?= htmlspecialchars($employee['address_number'] ?: 'S/N') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Bairro</div>
                <div class="info-item-value"><?= htmlspecialchars($employee['neighborhood'] ?: '-') ?></div>
            </div>

            <div>
                <div class="info-item-label">Cidade / UF</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($employee['city'] ?: 'São Miguel dos Campos') ?> / <?= htmlspecialchars($employee['state'] ?: 'AL') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Bloco 3: Habilitação & Cargas Perigosas -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Habilitação (CNH & MOPP)
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <div class="info-item-label">CNH / Categoria</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?php if (!empty($employee['driver_license'])): ?>
                        <strong style="font-size: 13px;">Cat. <?= htmlspecialchars($employee['driver_license_category'] ?: 'B') ?></strong> (<?= htmlspecialchars($employee['driver_license']) ?>)
                    <?php else: ?>
                        Não possui CNH
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Validade da CNH</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= !empty($employee['driver_license_expiry']) ? date('d/m/Y', strtotime($employee['driver_license_expiry'])) : '-' ?>
                </div>
            </div>

            <div style="grid-column: span 2;">
                <div class="info-item-label">Certificações de Segurança & NRs</div>
                <div class="info-item-value">
                    <?php if (!empty($employee['certifications']) || !empty($employee['has_mopp'])): ?>
                        <span style="display: inline-block; padding: 2px 8px; font-weight: 700; border-radius: 4px; background: #e0f2fe; color: #0369a1; font-size: 11px;">
                            <?= htmlspecialchars($employee['certifications'] ?: 'MOPP (Produtos Perigosos)') ?>
                        </span>
                    <?php else: ?>
                        <span style="color: var(--text-dim);">Nenhuma certificação registrada</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bloco 4: Custos & Financeiro (Para DRE) -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Custos com Pessoal & Financeiro
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <div class="info-item-label">Salário Base Contratual</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 700; color: var(--text-main);">
                    R$ <?= number_format((float)($employee['base_salary'] ?? 0), 2, ',', '.') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Benefícios / VT / VR</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    R$ <?= number_format((float)($employee['benefits_cost'] ?? 0), 2, ',', '.') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Periculosidade (30%)</div>
                <div class="info-item-value">
                    <?= !empty($employee['has_peril_bonus']) ? '<span style="color: #15803d; font-weight: 700;">Sim (Gases Pressurizados)</span>' : '<span style="color: var(--text-dim);">Não aplicável</span>' ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Chave PIX (Diárias)</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($employee['pix_key'] ?: '-') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bloco 5: Observações Internas & Certificações -->
<div class="panel">
    <div class="panel-header" style="justify-content: space-between;">
        <div>
            <h2 class="panel-title">Observações Operacionais & Rotas</h2>
            <p class="panel-subtitle">Histórico interno, rotas habituais e detalhes de segurança</p>
        </div>
    </div>

    <div style="padding: 20px;">
        <?php if (!empty($employee['notes'])): ?>
            <p style="font-size: 13px; color: var(--text-main); line-height: 1.6; margin: 0;">
                <?= nl2br(htmlspecialchars($employee['notes'])) ?>
            </p>
        <?php else: ?>
            <p style="font-size: 13px; color: var(--text-muted); font-style: italic; margin: 0;">
                Nenhuma observação interna registrada para este funcionário.
            </p>
        <?php endif; ?>
    </div>
</div>
