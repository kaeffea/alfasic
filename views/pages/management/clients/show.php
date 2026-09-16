<?php
/**
 * View: Detalhes do Cliente (Perfil Cadastral, Setores, Endereço e Resumo)
 */
$activeTab = 'show';
require __DIR__ . '/profile_header.php';
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Bloco 1: Identificação Cadastral & Fiscal -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Identificação Cadastral & Fiscal
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="grid-column: span 2;">
                <div class="info-item-label">Razão Social / Nome Completo</div>
                <div class="info-item-value" style="font-size: 14px; font-weight: 700; color: var(--text-main);">
                    <?= htmlspecialchars($client['name']) ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Nome Fantasia</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($client['trade_name'] ?: '-') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Tipo de Cadastro</div>
                <div class="info-item-value">
                    <?= ($client['client_type'] ?? 'PJ') === 'PJ' ? 'Pessoa Jurídica (PJ)' : (($client['client_type'] ?? '') === 'PF' ? 'Pessoa Física (PF)' : 'Órgão Público') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">CNPJ / CPF</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 600;">
                    <?php if (!empty($client['document'])): ?>
                        <span class="copy-click" data-copy="<?= htmlspecialchars($client['document']) ?>" title="Clique para copiar" style="cursor: pointer; border-bottom: 1px dashed var(--border-color);">
                            <?= htmlspecialchars($client['document']) ?>
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Inscrição Estadual (IE)</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($client['state_registration'] ?: 'Isento / Não informada') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Código Interno</div>
                <div class="info-item-value" style="font-family: var(--font-mono); color: var(--text-muted);">
                    #<?= $client['id'] ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Status Cadastral</div>
                <div class="info-item-value" style="color: #166534; font-weight: 600;">
                    Ativo
                </div>
            </div>
        </div>
    </div>

    <!-- Bloco 2: Localização & Endereços (Matriz & Locais de Entrega) -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Endereços & Locais de Entrega (<?= count($addresses ?? []) ?: 1 ?>)
        </h2>

        <?php if (!empty($addresses)): ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($addresses as $addr): ?>
                    <div style="background: var(--bg-secondary); padding: 12px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong style="font-size: 13px; color: var(--text-main);">
                                <?= htmlspecialchars($addr['label'] ?: 'Local de Entrega / Endereço') ?>
                            </strong>
                        </div>
                        <div style="font-size: 12px; color: var(--text-main);">
                            <?= htmlspecialchars($addr['street']) ?><?= !empty($addr['number']) ? ', Nº ' . htmlspecialchars($addr['number']) : '' ?>
                            <?= !empty($addr['complement']) ? ' (' . htmlspecialchars($addr['complement']) . ')' : '' ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            <?= htmlspecialchars($addr['neighborhood'] ?: '-') ?> • 
                            <?= htmlspecialchars($addr['city'] ?: 'São Miguel dos Campos') ?>/<?= htmlspecialchars($addr['state'] ?: 'AL') ?>
                            <?= !empty($addr['zip_code']) ? ' • CEP: ' . htmlspecialchars($addr['zip_code']) : '' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div style="grid-column: span 2;">
                    <div class="info-item-label">Logradouro / Endereço</div>
                    <div class="info-item-value" style="font-weight: 600;">
                        <?= htmlspecialchars($client['address'] ?: 'Endereço não cadastrado') ?>
                        <?= !empty($client['address_number']) ? ', Nº ' . htmlspecialchars($client['address_number']) : '' ?>
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Bairro</div>
                    <div class="info-item-value">
                        <?= htmlspecialchars($client['neighborhood'] ?: '-') ?>
                    </div>
                </div>

                <div>
                    <div class="info-item-label">CEP</div>
                    <div class="info-item-value" style="font-family: var(--font-mono);">
                        <?= htmlspecialchars($client['zip_code'] ?: '-') ?>
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Cidade</div>
                    <div class="info-item-value" style="font-weight: 600;">
                        <?= htmlspecialchars($client['city'] ?: 'São Miguel dos Campos') ?>
                    </div>
                </div>

                <div>
                    <div class="info-item-label">Estado (UF)</div>
                    <div class="info-item-value">
                        <?= htmlspecialchars($client['state'] ?: 'AL') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bloco 3: Contatos & Setores da Empresa (Oculto se vazio) -->
<?php if (!empty($contacts)): ?>
<div class="panel" style="margin-bottom: 24px; padding: 20px;">
    <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
        Contatos & Setores da Empresa (<?= count($contacts) ?> setores cadastrados)
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <?php foreach ($contacts as $ct): ?>
            <div style="background: var(--bg-secondary); padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 14px; color: var(--text-main);"><?= htmlspecialchars($ct['name']) ?></strong>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;"><?= htmlspecialchars($ct['role'] ?: 'Setor') ?></span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                    <?php if (!empty($ct['phone'])): ?>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Telefone / WhatsApp:</span>
                            <strong style="color: var(--text-main); font-family: var(--font-mono);"><?= htmlspecialchars($ct['phone']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($ct['email'])): ?>
                        <div style="display: flex; justify-content: space-between;">
                            <span>E-mail:</span>
                            <strong style="color: var(--brand-primary);"><?= htmlspecialchars($ct['email']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
    <!-- Bloco 4: Condições Comerciais & Financeiras -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Condições Comerciais & Financeiras
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <div class="info-item-label">Prazo de Pagamento</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($client['payment_terms'] ?: 'A combinar / À vista') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Limite de Crédito</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= (float)($client['credit_limit'] ?? 0) > 0 ? 'R$ ' . number_format((float)$client['credit_limit'], 2, ',', '.') : 'Livre / Conforme Faturamento' ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Situação Comercial</div>
                <div class="info-item-value" style="color: #166534; font-weight: 600;">
                    Liberado para Vendas
                </div>
            </div>

            <div>
                <div class="info-item-label">Preços Diferenciados</div>
                <div class="info-item-value">
                    <?= count($negotiatedPrices) ?> produtos com preço aplicado
                </div>
            </div>
        </div>
    </div>

    <!-- Bloco 5: Resumo Cadastral (operacional/pedidos virá no motor comercial) -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Resumo Cadastral
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <div class="info-item-label">Data de Cadastro</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?php
                        if (!empty($client['created_at'])) {
                            $hasTime = (date('H:i', strtotime($client['created_at'])) !== '00:00');
                            echo $hasTime ? date('d/m/Y \à\s H:i', strtotime($client['created_at'])) : date('d/m/Y', strtotime($client['created_at']));
                        } else {
                            echo '-';
                        }
                    ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Endereços / Contatos</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 600;">
                    <?= count($addresses ?? []) ?> endereços • <?= count($contacts ?? []) ?> contatos
                </div>
            </div>

            <div>
                <div class="info-item-label">Preços Negociados</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 700; color: var(--text-main);">
                    <?= count($negotiatedPrices ?? []) ?> produtos
                </div>
            </div>

            <div>
                <div class="info-item-label">Histórico Comercial</div>
                <div class="info-item-value" style="color: var(--text-muted);">
                    Pedidos, cilindros e licitações serão modelados nas próximas Sprints.
                </div>
            </div>
        </div>
    </div>
</div>
