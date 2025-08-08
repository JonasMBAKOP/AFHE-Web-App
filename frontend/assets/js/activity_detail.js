// Variables globales pour la galerie
let currentImageIndex = 0;
let galleryImages = [];

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeGallery();
    addScrollAnimations();
    addImageLazyLoading();
});

// Initialisation de la galerie
function initializeGallery() {
    const galleryItems = document.querySelectorAll('.secondary-image-item img');
    galleryImages = Array.from(galleryItems).map(img => img.src);
    
    // Ajout d'événements de clic pour chaque image
    galleryItems.forEach((img, index) => {
        img.addEventListener('click', function() {
            currentImageIndex = index;
            openModal(this.src);
        });
    });
}

// Fonction pour ouvrir le modal d'image
function openModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.style.display = 'block';
    modalImg.src = imageSrc;

    // Trouver l'index de l'image actuelle
    currentImageIndex = galleryImages.findIndex(src => src === imageSrc);
    
    // Ajouter l'événement de fermeture avec Escape
    document.addEventListener('keydown', handleKeyPress);
    
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';
}

// Fonction pour fermer le modal
function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';

    // Retirer l'événement de fermeture avec Escape
    document.removeEventListener('keydown', handleKeyPress);
    
    // Rétablir le scroll du body
    document.body.style.overflow = 'auto';
}

// Changer d'image dans le modal
function changeImage(direction) {
    if (galleryImages.length === 0) return;
    
    currentImageIndex += direction;
    
    // Gestion des limites
    if (currentImageIndex >= galleryImages.length) {
        currentImageIndex = 0;
    } else if (currentImageIndex < 0) {
        currentImageIndex = galleryImages.length - 1;
    }
    
    const modalImg = document.getElementById('modalImage');
    modalImg.style.opacity = '0';
    
    setTimeout(() => {
        modalImg.src = galleryImages[currentImageIndex];
        modalImg.style.opacity = '1';
    }, 150);
}

// Gestion des touches du clavier
function handleKeyPress(event) {
    switch(event.key) {
        case 'Escape':
            closeModal();
            break;
        case 'ArrowLeft':
            changeImage(-1);
            break;
        case 'ArrowRight':
            changeImage(1);
            break;
    }
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Animations au scroll
function addScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observer les éléments à animer
    const elementsToAnimate = document.querySelectorAll('.main-image-container, .description-section, .secondary-images, .action-buttons');
    elementsToAnimate.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Chargement paresseux des images
function addImageLazyLoading() {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    });
    
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        imageObserver.observe(img);
    });
}

// Fonction pour partager l'activité
function shareActivity() {
    const button = event.target.closest('.btn-share');
    const originalText = button.innerHTML;
    
    // Vérification du support de l'API Web Share
    if (navigator.share) {
        navigator.share({
            title: document.querySelector('.activity-title').textContent,
            text: 'Découvrez cette activité incroyable !',
            url: window.location.href
        }).then(() => {
            showNotification('Activité partagée avec succès !', 'success');
        }).catch((error) => {
            console.log('Erreur lors du partage:', error);
            fallbackShare();
        });
    } 
    else {
        fallbackShare();
    }
    
    function fallbackShare() {
        // Copie du lien dans le presse-papiers
        navigator.clipboard.writeText(window.location.href).then(() => {
            button.innerHTML = '<i class="fas fa-check"></i> Lien copié !';
            showNotification('Lien copié dans le presse-papiers !', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }).catch(() => {
            showNotification('Erreur lors de la copie du lien', 'error');
        });
    }
}

// Fonction pour afficher des notifications
function showNotification(message, type = 'info') {
    // Création de l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getIconForType(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Ajout au DOM
    document.body.appendChild(notification);
    
    // Animation d'apparition
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Suppression automatique après 7 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 7000);
}

// Fonction auxiliaire pour obtenir l'icône selon le type
function getIconForType(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

// // Fonction pour le scroll fluide
// function smoothScrollTo(target) {
//     const element = document.querySelector(target);
//     if (element) {
//         element.scrollIntoView({
//             behavior: 'smooth',
//             block: 'start'
//         });
//     }
// }

// Gestionnaires d'événements
document.addEventListener('DOMContentLoaded', function() {
    // Ajout des écouteurs d'événements pour les images
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.addEventListener('click', function() {
            openModal(this.src);
        });
    }
    
    // Ajout des écouteurs pour les images secondaires
    const secondaryImages = document.querySelectorAll('.gallery-item');
    secondaryImages.forEach(image => {
        image.addEventListener('click', function() {
            openModal(this.src);
        });
    });
});

// Ajout des styles CSS pour les notifications via JavaScript
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        color: var(--text-color);
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1001;
        border-left: 4px solid var(--primary-color);
    }
    
    .notification-success {
        border-left-color: #28a745;
    }
    
    .notification-error {
        border-left-color: #dc3545;
    }
    
    .notification-warning {
        border-left-color: #ffc107;
    }
    
    .notification-info {
        border-left-color: var(--primary-color);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-content i {
        font-size: 1.2em;
        color: var(--primary-color);
    }
    
    .notification-success .notification-content i {
        color: #28a745;
    }
    
    .notification-error .notification-content i {
        color: #dc3545;
    }
    
    .notification-warning .notification-content i {
        color: #ffc107;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 1.1em;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .notification-close:hover {
        background-color: #f0f0f0;
        color: var(--text-color);
    }
    
    @keyframes heartPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }
    
    /* Styles pour les effets de hover améliorés */
    .secondary-image-item {
        position: relative;
        overflow: hidden;
    }
    
    .secondary-image-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
        border-radius: var(--border-radius);
    }
    
    .secondary-image-item:hover::before {
        opacity: 0.3;
    }
    
    .secondary-image-item::after {
        content: '👁️';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5em;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
        color: white;
    }
    
    .secondary-image-item:hover::after {
        opacity: 1;
    }
    
    /* Animation de loading pour les boutons */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    /* Responsive pour les notifications */
    @media (max-width: 768px) {
        .notification {
            right: 10px;
            left: 10px;
            min-width: auto;
            max-width: none;
        }
    }
`;

// Injection des styles dans le DOM
if (!document.getElementById('notification-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'notification-styles';
    styleSheet.textContent = notificationStyles;
    document.head.appendChild(styleSheet);
}

// Optimisation des performances
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Préchargement des images de la galerie
function preloadGalleryImages() {
    galleryImages.forEach(src => {
        const img = new Image();
        img.src = src;
    });
}

// Lancer le préchargement après le chargement de la page
window.addEventListener('load', preloadGalleryImages);

// Gestion du redimensionnement de la fenêtre
window.addEventListener('resize', debounce(() => {
    // Ajuster la taille du modal si nécessaire
    const modal = document.getElementById('imageModal');
    if (modal.style.display === 'block') {
        const modalImg = document.getElementById('modalImage');
        modalImg.style.maxWidth = '90%';
        modalImg.style.maxHeight = '80%';
    }
}, 250));

// Fonction pour les interactions tactiles sur mobile
function handleTouchInteractions() {
    const buttons = document.querySelectorAll('.btn-reserve, .btn-share, .btn-favorite');
    
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Fonction pour gérer les erreurs d'images
function handleImageErrors() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik0xMDAgNzBDMTA1LjUyMyA3MCAxMTAgNzQuNDc3IDExMCA4MFY5MEM5NS4xNzc0IDkwIDg4Ljc2MTMgOTAgODQuMzgwNyA5NC4zODA3Qzg0LjM4MDcgOTguNzYxMyA4NC4zODA3IDEwNS4xNzcgOTQuMzgwNyAxMjBIMTIwVjE1MEg4MFY5MEM2NC40NzcgOTAgNjAgODUuNTIzIDYwIDgwVjUwQzYwIDQ0LjQ3NyA2NC40NzcgNDAgNzAgNDBIMTMwQzEzNS41MjMgNDAgMTQwIDQ0LjQ3NyAxNDAgNTBWODBDMTQwIDg1LjUyMyAxMzUuNTIzIDkwIDEzMCA5MEgxMDBWNzBaIiBmaWxsPSIjREREREREIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTIwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5OTk5OTkiPkltYWdlIG5vbiB0cm91dsOpZTwvdGV4dD4KPC9zdmc+';
            this.alt = 'Image non disponible';
        });
    });
}

// Initialisation complète au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {    
    // Gestion des erreurs d'images
    handleImageErrors();
    
    // Préchargement des images
    preloadImages();
    
    // Redimensionnement des images
    handleImageResize();
    
    // Interactions tactiles
    handleTouchInteractions();
    
    // Mise à jour de la fonction toggleFavorite pour inclure la sauvegarde
    const originalToggleFavorite = toggleFavorite;
    toggleFavorite = function() {
        originalToggleFavorite.call(this);
        
        // Sauvegarde de l'état
        const urlParams = new URLSearchParams(window.location.search);
        const activityId = urlParams.get('id');
        if (activityId) {
            saveFavoriteState(activityId, isFavorite);
        }
    };
    
    console.log('Activity detail page initialized successfully!');
});