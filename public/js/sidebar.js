const sidebar = document.getElementById('sidebar');
const openBtn = document.getElementById('openBtn');

openBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    sidebar.classList.add('open');
});
sidebar.addEventListener('mouseleave', () => {
    sidebar.classList.remove('open');
});