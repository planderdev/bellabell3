function initProductFilters() {
    const filters = [...document.querySelectorAll('.product-filter[data-filter]')];
    const cards = [...document.querySelectorAll('.product-card')];
    if (!filters.length) return;

    function apply(filter) {
        filters.forEach(button => {
            const active = button.dataset.filter === filter;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        cards.forEach(card => { card.hidden = filter !== 'all' && card.dataset.category !== filter; });
        const empty = document.querySelector('.product-empty');
        if (empty) empty.hidden = cards.some(card => !card.hidden);
    }

    filters.forEach(button => button.addEventListener('click', () => {
        apply(button.dataset.filter);
        history.replaceState(null, '', button.dataset.filter === 'all' ? location.pathname : '#' + button.dataset.filter);
    }));

    // The header sub-menu links to /product#stick and so on; those ids are filters, not elements.
    const showHashFilter = () => {
        const filter = location.hash.slice(1);
        if (!filters.some(button => button.dataset.filter === filter)) return;
        apply(filter);
        document.getElementById('products').scrollIntoView();
    };
    window.addEventListener('hashchange', showHashFilter);
    showHashFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    initProductFilters();
});
