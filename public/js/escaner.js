document.addEventListener('DOMContentLoaded', () => {
    const radioAcciones = document.querySelectorAll('input[name="accion"]');
    const userField = document.getElementById('userField');
    const inputRut = document.getElementById('rut_usuario');
    const inputCodigo = document.getElementById('codigo');

    function toggleUserField() {
        const accionSeleccionada = document.querySelector('input[name="accion"]:checked').value;

        if (accionSeleccionada === 'prestamo') {
            userField.style.display = 'block';
            inputRut.setAttribute('required', 'true');
            
            if (!inputRut.value.trim()) {
                inputRut.focus();
            } else {
                inputCodigo.focus();
                inputCodigo.select(); // Selecciona el texto para rápido reemplazo al escanear
            }
        } else {
            userField.style.display = 'none';
            inputRut.removeAttribute('required');
            inputCodigo.focus();
            inputCodigo.select();
        }
    }

    radioAcciones.forEach(radio => {
        radio.addEventListener('change', toggleUserField);
    });

    toggleUserField();
});