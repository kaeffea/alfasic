/* Extraido de views/pages/system/users/drawer.php (CSP Fase 2) - Vanilla JS, sem dependencias. */

// Preenche o e-mail a partir do funcionario (so se o campo estiver vazio = editavel).
(function () {
    var empSelect = document.getElementById('user-drawer-employee');
    if (!empSelect) return;
    empSelect.addEventListener('change', function () {
        var opt = empSelect.options[empSelect.selectedIndex];
        if (!opt) return;
        var emailInput = document.getElementById('user-drawer-email');
        if (emailInput && emailInput.value.trim() === '' && opt.dataset.email) {
            emailInput.value = opt.dataset.email;
        }
    });
})();

function generateRandomPassword() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const passInput = document.getElementById('user-drawer-password');
    const confirmInput = document.getElementById('user-drawer-password-confirm');
    if (passInput && confirmInput) {
        passInput.type = 'text';
        confirmInput.type = 'text';
        passInput.value = pass;
        confirmInput.value = pass;
        alert('Senha gerada com sucesso:\n\n' + pass + '\n\nCopie e guarde esta senha antes de salvar!');
    }
}
