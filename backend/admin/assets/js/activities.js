// Script de la page de création des activités (add.php)

document.addEventListener('DOMContentLoaded', function() {
    // Preview des images uploadées
    const mainImageInput = document.getElementById('main_image');
    const secondaryImagesInput = document.getElementById('secondary_images');
    const mainImagePreview = document.getElementById('mainImagePreview');
    const secondaryImagesPreview = document.getElementById('secondaryImagesPreview');

    if (mainImageInput) {
        mainImageInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    mainImagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    mainImagePreview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (secondaryImagesInput) {
        secondaryImagesInput.addEventListener('change', function(e) {
            secondaryImagesPreview.innerHTML = '';
            if (this.files) {
                for (let i = 0; i < this.files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = "Preview " + (i + 1);
                        img.style.maxWidth = '100px';
                        img.style.margin = '0 5px 5px 0';
                        secondaryImagesPreview.appendChild(img);
                    }
                    reader.readAsDataURL(this.files[i]);
                }
                secondaryImagesPreview.style.display = 'block';
            }
        });
    }

    // Validation du formulaire
    const form = document.querySelector('.activity-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const category = document.getElementById('category_id');
            
            if (title.value.trim() === '') {
                e.preventDefault();
                alert('Le titre est obligatoire');
                title.focus();
                return false;
            }
            
            if (category.value === '') {
                e.preventDefault();
                alert('La catégorie est obligatoire');
                category.focus();
                return false;
            }
            
            return true;
        });
    }
});



// Script de la page d'affichage d'une activité (view.php)

document.addEventListener('DOMContentLoaded', function() {
    // Animation des éléments de la galerie
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.1)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });

    // Zoom sur les images au clic
    const images = document.querySelectorAll('.main-image, .gallery-image');
    images.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="${this.src}" alt="${this.alt}">
                </div>
            `;
            document.body.appendChild(modal);
            
            modal.querySelector('.close').addEventListener('click', function() {
                modal.remove();
            });
        });
    });
});

// Styles pour le modal d'image
const imageModalStyle = document.createElement('style');
imageModalStyle.textContent = `
    .image-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .image-modal .modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }
    
    .image-modal img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
    }
    
    .image-modal .close {
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 30px;
        cursor: pointer;
    }
`;
document.head.appendChild(imageModalStyle);



// Script de la page de modification d'une activité (edit.php)

// Dans admin_script.js ou un nouveau fichier
document.addEventListener('DOMContentLoaded', function() {
    // Preview des nouvelles images uploadées
    const mainImageInput = document.getElementById('main_image');
    const secondaryImagesInput = document.getElementById('secondary_images');
    
    if (mainImageInput) {
        mainImageInput.addEventListener('change', function(e) {
            const previewContainer = document.createElement('div');
            previewContainer.className = 'file-upload-preview';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<p>Nouvelle image :</p><img src="${e.target.result}" alt="Preview">`;
                    this.parentNode.appendChild(previewContainer);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (secondaryImagesInput) {
        secondaryImagesInput.addEventListener('change', function(e) {
            const previewContainer = document.createElement('div');
            previewContainer.className = 'file-upload-preview';
            previewContainer.innerHTML = '<p>Nouvelles images :</p>';
            
            if (this.files) {
                for (let i = 0; i < this.files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = "Preview " + (i + 1);
                        img.style.maxWidth = '100px';
                        img.style.margin = '0 5px 5px 0';
                        previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(this.files[i]);
                }
                this.parentNode.appendChild(previewContainer);
            }
        });
    }

    // Confirmation avant de quitter sans sauvegarder
    const form = document.querySelector('.activity-form');
    if (form) {
        let formChanged = false;
        
        // Détecter les changements dans le formulaire
        form.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('change', function() {
                formChanged = true;
            });
        });

        // Confirmation avant de quitter
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?';
            }
        });

        // Ne pas afficher la confirmation après soumission
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    }

    // Zoom sur les images existantes
    const existingImages = document.querySelectorAll('.current-image, .secondary-image');
    existingImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="${this.src}" alt="${this.alt}">
                </div>
            `;
            document.body.appendChild(modal);
            
            modal.querySelector('.close').addEventListener('click', function() {
                modal.remove();
            });
        });
    });
});