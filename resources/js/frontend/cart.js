/**
 * Frontend Cart AJAX Module
 */
export function initCart() {
    // Quickview modal trigger
    document.querySelectorAll('.quick_view_btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            if (!id) return;

            fetch(`/quick-view?id=${id}`)
                .then(res => res.text())
                .then(html => {
                    const container = document.getElementById('quickview_modal_body');
                    if (container) {
                        container.innerHTML = html;
                    }
                })
                .catch(err => console.error('Quickview load error:', err));
        });
    });
}