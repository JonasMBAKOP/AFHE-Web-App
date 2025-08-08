// Script de la page d'Affichage d'un projet en paticulier (view.php)

document.addEventListener('DOMContentLoaded', function() {
    // Agrandir les images au clic
    document.querySelectorAll('.gallery-image, .project-main-image').forEach(img => {
        img.addEventListener('click', function() {
            Swal.fire({
                imageUrl: this.src,
                imageAlt: this.alt,
                showConfirmButton: false,
                background: 'rgba(0,0,0,0.8)',
                backdrop: true,
                showCloseButton: true
            });
        });
    });

    // Confirmation avant suppression
    window.confirmDelete = function(projectId) {
        Swal.fire({
            title: "Êtes-vous sûr ?",
            text: "Cette action supprimera définitivement le projet et toutes ses images associées.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Suppression en cours...",
                    html: '<div class="swal2-loader"></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    willOpen: () => {
                        setTimeout(() => {
                            window.location.href = "delete.php?id=" + projectId;
                        }, 1500);
                    }
                });
            }
        });
    };
});



// Script de la page d'Ajout d'un projet (add.php)

document.addEventListener('DOMContentLoaded', function() {
    // Aperçu de l'image principale
    const mainImageInput = document.querySelector('input[name="main_image"]');
    const mainImagePreview = document.getElementById('mainImagePreview');
    
    mainImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                mainImagePreview.innerHTML = `<img src="${e.target.result}" alt="Aperçu image principale">`;
                mainImagePreview.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Aperçu des images secondaires
    const secondaryImagesInput = document.querySelector('input[name="secondary_images[]"]');
    const secondaryImagesPreview = document.getElementById('secondaryImagesPreview');
    
    secondaryImagesInput.addEventListener('change', function() {
        secondaryImagesPreview.innerHTML = '';
        secondaryImagesPreview.style.display = 'block';
        
        if (this.files) {
            for (let i = 0; i < Math.min(this.files.length, 5); i++) { // Limite à 5 aperçus
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '80px';
                    img.style.marginRight = '5px';
                    secondaryImagesPreview.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[i]);
            }
            
            if (this.files.length > 5) {
                const moreText = document.createElement('div');
                moreText.textContent = `+ ${this.files.length - 5} autres...`;
                moreText.style.fontSize = '0.8rem';
                moreText.style.color = '#6c757d';
                secondaryImagesPreview.appendChild(moreText);
            }
        }
    });

    // Validation du formulaire
    const form = document.querySelector('.project-form');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        
        if (title === '') {
            e.preventDefault();
            Swal.fire({
                title: 'Erreur',
                text: 'Le titre du projet est obligatoire',
                icon: 'error',
                confirmButtonColor: '#3085d6',
            });
        }
    });
});



// Script de la page d'Édition d'un projet (edit.php)

function initEditPage() {
    // Aperçu de l'image principale
    const mainImageInput = document.querySelector('input[name="main_image"]');
    if (mainImageInput) {
        const mainImagePreview = document.getElementById('mainImagePreview');
        
        mainImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    mainImagePreview.innerHTML = `<img src="${e.target.result}" alt="Nouvelle image principale">`;
                    mainImagePreview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Aperçu des nouvelles images secondaires
    const secondaryImagesInput = document.querySelector('input[name="secondary_images[]"]');
    if (secondaryImagesInput) {
        const secondaryImagesPreview = document.getElementById('secondaryImagesPreview');
        
        secondaryImagesInput.addEventListener('change', function() {
            secondaryImagesPreview.innerHTML = '';
            secondaryImagesPreview.style.display = 'block';
            
            if (this.files) {
                for (let i = 0; i < Math.min(this.files.length, 5); i++) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '80px';
                        img.style.marginRight = '5px';
                        secondaryImagesPreview.appendChild(img);
                    }
                    
                    reader.readAsDataURL(this.files[i]);
                }
                
                if (this.files.length > 5) {
                    const moreText = document.createElement('div');
                    moreText.textContent = `+ ${this.files.length - 5} autres...`;
                    moreText.style.fontSize = '0.8rem';
                    moreText.style.color = '#6c757d';
                    secondaryImagesPreview.appendChild(moreText);
                }
            }
        });
    }

    // Confirmation avant suppression d'images
    const deleteCheckboxes = document.querySelectorAll('.delete-checkbox');
    deleteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: 'Cette image sera définitivement supprimée du projet.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        this.checked = false;
                    }
                });
            }
        });
    });

    // Validation du formulaire
    const form = document.querySelector('.project-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            
            if (title === '') {
                e.preventDefault();
                Swal.fire({
                    title: 'Erreur',
                    text: 'Le titre du projet est obligatoire',
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                });
            }
        });
    }
}

// Initialiser la page d'édition
if (document.querySelector('.edit-project-content')) {
    document.addEventListener('DOMContentLoaded', initEditPage);
}