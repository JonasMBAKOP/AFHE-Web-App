// Scripts de la page Activities
document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('categoryFilter');

  filter.addEventListener('change', () => {
    const params = new URLSearchParams(window.location.search);
    const val    = filter.value;

    if (val === 'all') {
      params.delete('category');
    } else {
      params.set('category', val);
    }
    // Réinitialiser à la première page
    params.set('page', 1);
    window.location.search = params.toString();
  });
});



// Scripts de la page Projects
document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('statusFilter');
  filter.addEventListener('change', () => {
    const val = filter.value;
    const params = new URLSearchParams(window.location.search);
    if (val === 'all') {
      params.delete('status');
    } else {
      params.set('status', val);
    }
    params.set('page', 1);
    window.location.search = params.toString();
  });
});
