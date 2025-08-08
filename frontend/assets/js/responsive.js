// Script qui gère la responsivité du site

document.addEventListener("DOMContentLoaded", function () {
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector("nav ul");

    // Cacher le menu en mode mobile
    function updateMenuDisplay() {
        if (window.innerWidth <= 800) {  // Définit la limite de taille d’écran
            navMenu.style.display = "none";  // Cache la barre de navigation
            hamburger.style.display = "block";  // Affiche le hamburger
        } else {
            navMenu.style.display = "flex";  // Affiche la barre de navigation normale
            hamburger.style.display = "none";  // Cache le hamburger
        }
    }

    // Gérer l'affichage du menu au clic sur le hamburger
    hamburger.addEventListener("click", function () {
        if (navMenu.style.display === "none" || navMenu.style.display === "") {
            navMenu.style.display = "flex";  // Affiche le menu
            navMenu.style.flexDirection = "column";  // Aligner verticalement
            navMenu.style.position = "fixed";  // Position fixe pour le menu
            navMenu.style.top = "0";  // Positionner sous le hamburger
            navMenu.style.left = "0";
            navMenu.style.width = "100%";
            navMenu.style.backgroundColor = "rgba(30, 144, 255, 0.9)";  // Fond bleu
            navMenu.style.padding = "2rem";
            navMenu.style.textAlign = "center";
            navMenu.style.zIndex = "9999";  // Au-dessus de tout
        } else {
            navMenu.style.display = "none";  // Cache le menu
        }
    });

    // Mettre à jour l'affichage lors du redimensionnement de la fenêtre
    window.addEventListener("resize", updateMenuDisplay);

    // Initialiser l'affichage du menu au chargement
    updateMenuDisplay();
});