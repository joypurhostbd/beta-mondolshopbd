/**
 * Frontend Live Search Module
 */
export function initLiveSearch() {
    const searchInput = document.querySelector('.search_keyword');
    const searchResults = document.querySelector('.search_result');

    if (!searchInput || !searchResults) return;

    let debounceTimer;

    searchInput.addEventListener('keyup', function () {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/livesearch?search=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    searchResults.innerHTML = html;
                    searchResults.style.display = 'block';
                })
                .catch(err => console.error('Live search error:', err));
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });
}