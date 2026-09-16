<?php
$isEdit = !empty($client['id']);
?>

<div class="page-header">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
        <a href="<?= $isEdit ? '/clients/' . $client['id'] . '/prices' : '/clients' ?>"
            class="btn btn-outline btn-sm">&larr; Voltar</a>
    </div>
    <h1 class="page-title">
        <?= $isEdit ? 'Editar Cadastro: ' . htmlspecialchars($client['name']) : 'Novo Cadastro de Cliente' ?></h1>
    <p class="page-subtitle">
        <?= $isEdit ? 'Atualize as informações cadastrais e comerciais' : 'Preencha os dados do novo cliente ou órgão parceiro' ?>
    </p>
</div>

<?php if (!empty($errors)): ?>
    <div
        style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: var(--radius); margin-bottom: 20px; font-size: 13px;">
        <strong>Corrija os erros abaixo:</strong>
        <ul style="margin: 6px 0 0 16px; padding: 0;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $isEdit ? '/clients/update' : '/clients/store' ?>">
    <!-- 1. Token de Proteção CSRF -->
    <?= \Alfasic\Core\Csrf::input() ?>

    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
    <?php endif; ?>

    <!-- Bloco 1: Identificação e Dados Fiscais -->
    <div class="panel" style="margin-bottom: 20px; padding: 20px;">
        <h2 class="panel-title"
            style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            1. Identificação & Dados Fiscais
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <div>
                <label class="info-item-label" for="client_type">Tipo de Cliente</label>
                <select name="client_type" id="client_type" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;">
                    <option value="PJ" <?= ($client['client_type'] ?? 'PJ') === 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica
                        (PJ)</option>
                    <option value="PF" <?= ($client['client_type'] ?? '') === 'PF' ? 'selected' : '' ?>>Pessoa Física (PF)
                    </option>
                    <option value="ORGAO_PUBLICO" <?= ($client['client_type'] ?? '') === 'ORGAO_PUBLICO' ? 'selected' : '' ?>>Órgão Público / Prefeitura</option>
                </select>
            </div>

            <div style="grid-column: span 2;">
                <label class="info-item-label" for="name">Razão Social / Nome Completo *</label>
                <input type="text" name="name" id="name" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['name'] ?? '') ?>" required autofocus>
            </div>

            <div>
                <label class="info-item-label" for="trade_name">Nome Fantasia</label>
                <input type="text" name="trade_name" id="trade_name" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['trade_name'] ?? '') ?>">
            </div>

            <div>
                <label class="info-item-label" for="document">CNPJ / CPF</label>
                <input type="text" name="document" id="document" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px; font-family: var(--font-mono);"
                    value="<?= htmlspecialchars($client['document'] ?? '') ?>" placeholder="00.000.000/0000-00">
            </div>

            <div>
                <label class="info-item-label" for="state_registration">Inscrição Estadual (IE)</label>
                <input type="text" name="state_registration" id="state_registration" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px; font-family: var(--font-mono);"
                    value="<?= htmlspecialchars($client['state_registration'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- Bloco 2: Contato & Localização -->
    <div class="panel" style="margin-bottom: 20px; padding: 20px;">
        <h2 class="panel-title"
            style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            2. Contato & Localização
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div>
                <label class="info-item-label" for="contact_person">Pessoa de Contato / Responsável</label>
                <input type="text" name="contact_person" id="contact_person" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['contact_person'] ?? '') ?>">
            </div>

            <div>
                <label class="info-item-label" for="phone">Telefone / Celular</label>
                <input type="text" name="phone" id="phone" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px; font-family: var(--font-mono);"
                    value="<?= htmlspecialchars($client['phone'] ?? '') ?>" placeholder="(82) 99999-9999">
            </div>

            <div>
                <label class="info-item-label" for="email">E-mail</label>
                <input type="email" name="email" id="email" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['email'] ?? '') ?>" placeholder="contato@empresa.com">
            </div>

            <div style="grid-column: span 2;">
                <label class="info-item-label" for="address">Endereço (Rua / Av / Rodovia)</label>
                <input type="text" name="address" id="address" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['address'] ?? '') ?>">
            </div>

            <div>
                <label class="info-item-label" for="address_number">Número</label>
                <input type="text" name="address_number" id="address_number" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['address_number'] ?? '') ?>">
            </div>

            <div>
                <label class="info-item-label" for="neighborhood">Bairro</label>
                <input type="text" name="neighborhood" id="neighborhood" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['neighborhood'] ?? '') ?>">
            </div>

            <div>
                <label class="info-item-label" for="city">Cidade</label>
                <input type="text" name="city" id="city" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['city'] ?? 'São Miguel dos Campos') ?>">
            </div>

            <div>
                <label class="info-item-label" for="state">Estado (UF)</label>
                <input type="text" name="state" id="state" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px; text-transform: uppercase;" maxlength="2"
                    value="<?= htmlspecialchars($client['state'] ?? 'AL') ?>">
            </div>

            <div>
                <label class="info-item-label" for="zip_code">CEP</label>
                <input type="text" name="zip_code" id="zip_code" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px; font-family: var(--font-mono);"
                    value="<?= htmlspecialchars($client['zip_code'] ?? '') ?>" placeholder="57240-000">
            </div>
        </div>
    </div>

    <!-- Bloco 3: Condições Comerciais -->
    <div class="panel" style="margin-bottom: 24px; padding: 20px;">
        <h2 class="panel-title"
            style="margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            3. Condições Comerciais & Financeiras
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <div>
                <label class="info-item-label" for="payment_terms">Prazo / Condição de Pagamento</label>
                <input type="text" name="payment_terms" id="payment_terms" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['payment_terms'] ?? 'A combinar') ?>"
                    placeholder="Ex: 28 dias / Quinzenal">
            </div>

            <div>
                <label class="info-item-label" for="billing_method">Forma de Cobrança</label>
                <input type="text" name="billing_method" id="billing_method" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 38px;"
                    value="<?= htmlspecialchars($client['billing_method'] ?? 'Boleto 28 dias') ?>"
                    placeholder="Ex: Boleto Bancário / PIX">
            </div>

            <div style="grid-column: span 2;">
                <label class="info-item-label" for="notes">Observações Internas</label>
                <textarea name="notes" id="notes" class="search-input"
                    style="width: 100%; padding: 8px 12px; height: 72px; resize: vertical;"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Barra de Ações -->
    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
        <a href="<?= $isEdit ? '/clients/' . $client['id'] . '/prices' : '/clients' ?>"
            class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 13px; font-weight: 600;">
            <?= $isEdit ? 'Atualizar Cadastro' : 'Salvar Novo Cliente' ?>
        </button>
    </div>
</form>