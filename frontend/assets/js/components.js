// JavaScript optionnel pour améliorer l'interaction du menu déroulant
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.dropdown');
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    // Gérer le clic sur le menu déroulant pour mobile
    if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            // Sur mobile, empêcher le lien de naviguer si on clique sur la flèche
            if (window.innerWidth <= 768) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    }
    
    // Fermer le menu si on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    // Gérer le redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            dropdown.classList.remove('active');
        }
    });
});