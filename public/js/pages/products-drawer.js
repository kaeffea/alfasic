/* Extraido de views/pages/management/products/drawer.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
function handleProductDrawerTypeChange(type) {
    const unitSelect = document.getElementById('drawer-product-unit');
    const labelUnitPrice = document.getElementById('label-unit-price');
    const capInput = document.getElementById('drawer-product-capacity');

    if (type === 'gas') {
        if (unitSelect.value === 'un') {
            unitSelect.value = 'm³';
        }
        if (labelUnitPrice) labelUnitPrice.textContent = 'Preço Unitário (R$ / ' + unitSelect.value + ')';
    } else {
        if (unitSelect.value === 'm³' || unitSelect.value === 'kg') {
            unitSelect.value = 'un';
        }
        if (capInput && (!capInput.value || capInput.value == 0)) {
            capInput.value = '1.00';
        }
        if (labelUnitPrice) labelUnitPrice.textContent = 'Preço Unitário (R$ / un)';
    }
    calculateStandardPrice();
}

function handleUnitChange(unit) {
    const labelUnitPrice = document.getElementById('label-unit-price');
    if (labelUnitPrice) {
        labelUnitPrice.textContent = 'Preço Unitário (R$ / ' + unit.toLowerCase() + ')';
    }
    calculateStandardPrice();
}

function calculateStandardPrice() {
    const type = document.getElementById('drawer-product-type').value;
    const cap = parseFloat(document.getElementById('drawer-product-capacity').value || '0');
    const unitPrice = parseFloat(document.getElementById('drawer-product-unit-price').value || '0');
    const priceInput = document.getElementById('drawer-product-price');

    if (type === 'gas' && cap > 0 && unitPrice > 0) {
        const total = cap * unitPrice;
        priceInput.value = total.toFixed(2);
    }
}
