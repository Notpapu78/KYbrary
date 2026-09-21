const sidebar = document.getElementById('sidebar');
const openBtn = document.getElementById('openBtn');

if (sidebar && openBtn) {
    openBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('open');
    });

    sidebar.addEventListener('mouseleave', () => {
        sidebar.classList.remove('open');
    });

    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !openBtn.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}