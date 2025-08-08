let slideIndex = 0;
const slides = document.querySelector('.slider');
const dots = document.querySelectorAll('.dot');

function showSlide(index) {
    slideIndex = index;
    const translateValue = -100 * index + "%";
    slides.style.transform = "translateX(" + translateValue + ")";
    
    // Met à jour les ronds actifs
    dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
    });
}

function nextSlide() {
    slideIndex = (slideIndex + 1) % 5;
    showSlide(slideIndex);
}

// Change de slide toutes les 5 secondes
setInterval(nextSlide, 5000);

// Permet de changer le slide en cliquant sur un rond
function changeSlide(index) {
    showSlide(index);
}