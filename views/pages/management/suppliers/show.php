<?php
/**
 * View: Detalhes do Fornecedor (Perfil Cadastral, Contatos, Endereço e Usina)
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
                    <?= htmlspecialchars($supplier['name']) ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Nome Fantasia</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($supplier['trade_name'] ?: '-') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Tipo de Fornecedor</div>
                <div class="info-item-value">
                    <span style="font-weight: 600; text-transform: uppercase;">
                        <?= htmlspecialchars($supplier['supplier_type'] ?? 'usina') ?>
                    </span>
                </div>
            </div>

            <div>
                <div class="info-item-label">CNPJ / CPF</div>
                <div class="info-item-value" style="font-family: var(--font-mono); font-weight: 600;">
                    <?php if (!empty($supplier['document'])): ?>
                        <span class="copy-click" data-copy="<?= htmlspecialchars($supplier['document']) ?>" title="Clique para copiar" style="cursor: pointer; border-bottom: 1px dashed var(--border-color);">
                            <?= htmlspecialchars($supplier['document']) ?>
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Inscrição Estadual (IE)</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($supplier['state_registration'] ?: 'Isento / Não informada') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Código Interno</div>
                <div class="info-item-value" style="font-family: var(--font-mono); color: var(--text-muted);">
                    #<?= $supplier['id'] ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Data de Cadastro</div>
                <div class="info-item-value" style="font-family: var(--font-mono); color: var(--text-muted);">
                    <?= !empty($supplier['created_at']) ? date('d/m/Y', strtotime($supplier['created_at'])) : '-' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bloco 2: Contato & Endereço -->
    <div class="panel" style="padding: 20px;">
        <h2 class="panel-title" style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            Contato Principal & Localização
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div style="grid-column: span 2;">
                <div class="info-item-label">Pessoa de Contato / Setor</div>
                <div class="info-item-value" style="font-weight: 600;">
                    <?= htmlspecialchars($supplier['contact_person'] ?: 'Comercial / Distribuição') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Telefone / WhatsApp</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($supplier['phone'] ?: '-') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">E-mail</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($supplier['email'] ?: '-') ?>
                </div>
            </div>

            <div style="grid-column: span 2;">
                <div class="info-item-label">Endereço / Logradouro</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($supplier['address'] ?: 'Não informado') ?>, <?= htmlspecialchars($supplier['address_number'] ?: 'S/N') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">Bairro</div>
                <div class="info-item-value"><?= htmlspecialchars($supplier['neighborhood'] ?: '-') ?></div>
            </div>

            <div>
                <div class="info-item-label">Cidade / UF</div>
                <div class="info-item-value">
                    <?= htmlspecialchars($supplier['city'] ?: 'Maceió') ?> / <?= htmlspecialchars($supplier['state'] ?: 'AL') ?>
                </div>
            </div>

            <div>
                <div class="info-item-label">CEP</div>
                <div class="info-item-value" style="font-family: var(--font-mono);">
                    <?= htmlspecialchars($supplier['zip_code'] ?: '-') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bloco 3: Equipe de Representantes Comerciais -->
<div class="panel">
    <div class="panel-header" style="justify-content: space-between;">
        <div>
            <h2 class="panel-title">Equipe Comercial & Representantes</h2>
            <p class="panel-subtitle">Contatos adicionais da usina, supervisores de vendas e plantão técnico</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cargo / Função</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            Nenhum contato adicional cadastrado para este fornecedor.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['role'] ?: 'Comercial') ?></td>
                            <td style="font-family: var(--font-mono); font-size: 11px;"><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($c['email'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
