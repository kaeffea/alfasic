<?php
$isEdit = !empty($supplier['id']);
$formAction = $isEdit ? '/suppliers/edit' : '/suppliers/create';
?>

<div class="page-header">
    <div style="margin-bottom: 6px;">
        <a href="<?= $isEdit ? '/suppliers/' . $supplier['id'] : '/suppliers' ?>" class="btn btn-outline btn-sm">&larr; Voltar</a>
    </div>
    <h1 class="page-title"><?= $isEdit ? 'Editar Fornecedor' : 'Novo Fornecedor' ?></h1>
    <p class="page-subtitle"><?= $isEdit ? 'Atualize os dados cadastrais do parceiro' : 'Preencha as informações do novo fornecedor' ?></p>
</div>

<div class="panel" style="max-width: 800px; margin: 0 auto;">
    <form action="<?= $formAction ?>" method="POST" style="padding: 20px;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Razão Social / Nome *</label>
                <input type="text" name="name" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                    value="<?= htmlspecialchars($supplier['name'] ?? '') ?>" required autofocus>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Nome Fantasia</label>
                <input type="text" name="trade_name" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                    value="<?= htmlspecialchars($supplier['trade_name'] ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">CNPJ / CPF</label>
                <input type="text" name="document" class="search-input" style="width: 100%; border: 1px solid var(--border-color); font-family: var(--font-mono);"
                    placeholder="00.000.000/0000-00" value="<?= htmlspecialchars($supplier['document'] ?? '') ?>">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Inscrição Estadual</label>
                <input type="text" name="state_registration" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                    value="<?= htmlspecialchars($supplier['state_registration'] ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Pessoa de Contato</label>
                <input type="text" name="contact_person" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                    value="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Telefone / WhatsApp</label>
                <input type="text" name="phone" class="search-input" style="width: 100%; border: 1px solid var(--border-color); font-family: var(--font-mono);"
                    placeholder="(82) 90000-0000" value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">E-mail Comercial</label>
                <input type="email" name="email" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                    value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); margin: 20px 0 16px 0; padding-top: 16px;">
            <div style="font-size: 13px; font-weight: 700; margin-bottom: 12px;">Endereço & Localização</div>
            
            <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Logradouro / Endereço</label>
                    <input type="text" name="address" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                        value="<?= htmlspecialchars($supplier['address'] ?? '') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Número</label>
                    <input type="text" name="address_number" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                        value="<?= htmlspecialchars($supplier['address_number'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 80px 120px; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Bairro</label>
                    <input type="text" name="neighborhood" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                        value="<?= htmlspecialchars($supplier['neighborhood'] ?? '') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Cidade</label>
                    <input type="text" id="city-input" name="city" class="search-input" style="width: 100%; border: 1px solid var(--border-color);"
                        value="<?= htmlspecialchars($supplier['city'] ?? 'São Miguel dos Campos') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">UF</label>
                    <input type="text" id="state-input" name="state" class="search-input" style="width: 100%; border: 1px solid var(--border-color); text-transform: uppercase;"
                        maxlength="2" value="<?= htmlspecialchars($supplier['state'] ?? 'AL') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">CEP</label>
                    <input type="text" name="zip_code" class="search-input" style="width: 100%; border: 1px solid var(--border-color); font-family: var(--font-mono);"
                        placeholder="00000-000" value="<?= htmlspecialchars($supplier['zip_code'] ?? '') ?>">
                </div>
            </div>

            <!-- Chips de Preenchimento Rápido -->
            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Cidades frequentes:</span>
                <button type="button" class="btn btn-outline btn-sm" style="font-size: 10px; padding: 2px 8px;" data-action="fill-city" data-city="São Miguel dos Campos" data-state="AL">São Miguel/AL</button>
                <button type="button" class="btn btn-outline btn-sm" style="font-size: 10px; padding: 2px 8px;" data-action="fill-city" data-city="Maceió" data-state="AL">Maceió/AL</button>
                <button type="button" class="btn btn-outline btn-sm" style="font-size: 10px; padding: 2px 8px;" data-action="fill-city" data-city="Jaboatão dos Guararapes" data-state="PE">Jaboatão/PE</button>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="<?= $isEdit ? '/suppliers/' . $supplier['id'] : '/suppliers' ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-weight: 700;">
                <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Fornecedor' ?>
            </button>
        </div>
    </form>
</div>
