const sidebar = document.getElementById('sidebar');
const openBtn = document.getElementById('openBtn');

// 1. Activar el sidebar al hacer clic en el botón
openBtn.addEventListener('click', (e) => {
    e.stopPropagation(); // Evita conflictos con clics globales
    sidebar.classList.add('open');
});

// 2. Ocultar automáticamente cuando el mouse sale del sidebar
sidebar.addEventListener('mouseleave', () => {
    sidebar.classList.remove('open');
});