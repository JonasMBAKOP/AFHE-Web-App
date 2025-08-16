// Scripts de ma page d'Accueil
// Fonction de prévisualisation d'image (si soumise) du formulaire d'ajout de témoignage 
function previewImage() {
  const fileInput = document.getElementById('image');
  const previewImage = document.getElementById('preview');
  const file = fileInput.files[0];
  if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
          previewImage.src = e.target.result;
          previewImage.style.display = 'block';
      }
      reader.readAsDataURL(file);
  } else {
      previewImage.src = '#';
      previewImage.style.display = 'none';
  }
}



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
