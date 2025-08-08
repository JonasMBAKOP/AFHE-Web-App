<?php
    // site_stats_report.php
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php";
    require_once "../../models/TestimonialModel.php";

    verifierExpirationSession();

    $current_page = 'create_testimonial';
    
    $testimonialModel = new TestimonialModel();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $position = $_POST['position'];
        $company = $_POST['company'];
        $content = $_POST['content'];
        $rating = $_POST['rating'];
        $display_order = $_POST['display_order'];
        // 🔹 Traitement de l’image (si elle existe)
        $imageFile = !empty($_FILES['image']['name']) ? $_FILES['image'] : null;
        // $imagePath = $imageFile ? $testimonialModel->uploadImage($imageFile) : null;
        
        $created_by = $_SESSION["id_user"] ?? null; // Récupérer l'ID de l'administrateur connecté

        // 🔹 Création du témoignage en BDD
        if ($testimonialModel->createTestimonial($name, $position, $company, $content, $imageFile, $rating, $display_order, $created_by)) {
            header("Location: testimonials.php"); // Redirection après ajout
            exit;
        } else {
            echo "<p style='color: red;'>Erreur lors de l'ajout du témoignage.</p>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ajouter un témoignage | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/testimonials.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-testimonial-content">
                    <div class="testimonial-form-container">
                        <h1><i class="fas fa-plus-circle"></i> Ajouter un témoignage</h1>
                        <p>Remplissez le formulaire pour ajouter un nouveau témoignage.</p>

                        <form method="POST" action="add.php" enctype="multipart/form-data" class="testimonial-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Nom complet</label>
                                    <input type="text" id="name" name="name" required placeholder="Ex: The King Jonas">
                                </div>

                                <div class="form-group">
                                    <label for="position">Poste</label>
                                    <input type="text" id="position" name="position" required placeholder="Ex: Développeur Web et Mobile">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="company">Entreprise</label>
                                    <input type="text" id="company" name="company" required placeholder="Ex: Kings' Empire Tech">
                                </div>

                                <div class="form-group">
                                    <label for="rating">Note</label>
                                    <input type="number" id="rating" name="rating" min="1" max="5" value="5" required>
                                    <div class="rating-container" id="ratingStars">
                                        <span class="rating-star" data-value="1">★</span>
                                        <span class="rating-star" data-value="2">★</span>
                                        <span class="rating-star" data-value="3">★</span>
                                        <span class="rating-star" data-value="4">★</span>
                                        <span class="rating-star" data-value="5">★</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="content">Témoignage</label>
                                <textarea id="content" name="content" required placeholder="Décrivez le témoignage..."></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ordre d'affichage</label>
                                    <select name="display_order" required>
                                        <option value="1">Élevé (apparaîtra en premier)</option>
                                        <option value="2">Moyen</option>
                                        <option value="3">Bas (apparaîtra en dernier)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Photo de profil</label>
                                    <div class="file-upload">
                                        <label class="file-upload-label" for="image">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--primary-blue);"></i>
                                            <p>Cliquez pour télécharger une image</p>
                                            <small>Formats acceptés: JPG, JPEG, PNG (max 2MB)</small>
                                        </label>
                                        <input type="file" id="image" name="image" accept="image/*" class="file-upload-input">
                                        <div class="file-upload-preview" id="imagePreview"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='testimonials.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-check"></i> Ajouter le témoignage
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>

        <script>
            // Gestion de l'aperçu de l'image
            document.getElementById('image').addEventListener('change', function(e) {
                const preview = document.getElementById('imagePreview');
                const file = e.target.files[0];
                
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu de l'image">`;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(file);
                }
            });

            // Gestion des étoiles de notation
            const stars = document.querySelectorAll('.rating-star');
            const ratingInput = document.getElementById('rating');

            // Mettre à jour les étoiles en fonction de la valeur de l'input
            function updateStars() {
                let value = parseInt(ratingInput.value) || 1; // Si vide ou NaN, met 1
                
                // Forcer la valeur entre 1 et 5
                if (value < 1) value = 1;
                if (value > 5) value = 5;
                
                // Mettre à jour l'input avec la valeur corrigée
                ratingInput.value = value;
                
                // Mettre à jour l'affichage des étoiles
                stars.forEach((star, index) => {
                    if (index < value) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            }

            // Écouteur pour le clic sur les étoiles
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = star.getAttribute('data-value');
                    ratingInput.value = value;
                    updateStars();
                });
            });

            // Écouteur pour les changements dans l'input
            ratingInput.addEventListener('input', function() {
                // Permettre la saisie temporaire (ne pas corriger immédiatement)
                const value = parseInt(this.value);
                
                // Si la valeur est un nombre valide, mettre à jour les étoiles
                if (!isNaN(value)) {
                    stars.forEach((star, index) => {
                        if (index < value) {
                            star.classList.add('selected');
                        } else {
                            star.classList.remove('selected');
                        }
                    });
                }
            });

            // Écouteur quand l'input perd le focus (quand on clique ailleurs)
            ratingInput.addEventListener('blur', function() {
                // Corriger la valeur finale quand on quitte le champ
                updateStars();
            });

            // Écouteur pour empêcher la saisie de valeurs non entières
            ratingInput.addEventListener('keydown', function(e) {
                // Autoriser: backspace, delete, tab, escape, enter, flèches
                if ([46, 8, 9, 27, 13, 37, 38, 39, 40].includes(e.keyCode) || 
                    // Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode == 65 && e.ctrlKey === true) || 
                    (e.keyCode == 67 && e.ctrlKey === true) ||
                    (e.keyCode == 86 && e.ctrlKey === true) ||
                    (e.keyCode == 88 && e.ctrlKey === true)) {
                    return;
                }
                
                // Empêcher tout ce qui n'est pas un chiffre
                if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });

            // Initialiser les étoiles selon la valeur par défaut
            updateStars();
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Script pour la confirmation de deconnexion -->
        <script>
            document.getElementById("logoutButton").addEventListener("click", function() {
                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Vous allez être déconnecté.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, me déconnecter",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Déconnexion en cours...",
                            text: "Redirection vers la page de connexion.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "../logout.php";
                        });
                    }
                });
            });
        </script>
    </body>
</html>