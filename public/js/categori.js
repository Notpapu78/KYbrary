document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const books = document.querySelectorAll('.bookLibrary .cBook');

    function filterBooks() {
        const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedCategory = categorySelect ? categorySelect.value.toLowerCase().trim() : '';

        books.forEach(book => {
            const title = book.querySelector('h1') ? book.querySelector('h1').textContent.toLowerCase() : '';
            const category = book.querySelector('small') ? book.querySelector('small').textContent.toLowerCase() : '';

            const matchesSearch = title.includes(searchText);
            const matchesCategory = selectedCategory === '' || category.includes(selectedCategory);

            if (matchesSearch && matchesCategory) {
                book.style.display = '';
            } else {
                book.style.display = 'none';
            }
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', filterBooks);
    }
    if (searchInput) {
        searchInput.addEventListener('input', filterBooks);
    }
});