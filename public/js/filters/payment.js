const statusFilter = document.getElementById('filter-status');
statusFilter.addEventListener('change', ({ target: { value }}) => {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('status', value);
    } else {
        url.searchParams.delete('status');
    }
    url.searchParams.delete('page');
    window.location.href = url
})

const periodFilter = document.getElementById('filter-period');
periodFilter.addEventListener('change', ({ target: { value }}) => {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('period', value);
    } else {
        url.searchParams.delete('period');
    }
    url.searchParams.delete('page');
    window.location.href = url
})