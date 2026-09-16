/* Extraido de views/pages/system/roles/drawer.php (CSP Fase 2) - Vanilla JS, sem dependencias. */
function toggleAllPermissions(checked) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = checked);
}
