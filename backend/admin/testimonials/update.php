<?php
    // site_stats_report.php
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php";
    require_once "../../models/TestimonialModel.php";

    verifierExpirationSession();

    $current_page = 'update_testimonial';
    
    $testimonialModel = new TestimonialModel();

    $id = $_GET['id'] ?? null;
    $testimonial = $id ? $testimonialModel->getTestimonialById($id) : null;

    if (!$testimonial) {
        die("<p style='color: red;'>⚠ Témoignage introuvable.</p>");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $position = $_POST['position'];
        $company = $_POST['company'];
        $content = $_POST['content'];
        $rating = $_POST['rating'];
        $display_order = $_POST['display_order'];
        $active = isset($_POST['active']) ? intval($_POST['active']) : 0; // Gestion du statut actif/inactif

        // Gestion de l'image (si une nouvelle est envoyée)
        $imageFile = (!empty($_FILES['image']['name'])) ? $_FILES['image'] : null;
        // $imagePath = $imageFile ? $testimonialModel->uploadImage($imageFile, $name) : $testimonial['image_path'];

        // Mise à jour du témoignage
        if ($testimonialModel->updateTestimonial($id, $name, $position, $company, $content, $imageFile, $rating, $display_order, $active)) {
            header("Location: testimonials.php?success=1");
            exit;
        } else {
            echo "<p style='color: red;'>Erreur lors de la mise à jour.</p>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier un Témoignage | AFHE Admin</title>
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

                <div class="update-testimonial-content">
                    <div class="update-testimonial-form">
                        <h1><i class="fas fa-edit"></i> Modifier le témoignage de « <span style="color:#FF69B4;"><?= htmlspecialchars($testimonial['name']) ?></span> »</h1>
                        
                        <form method="POST" action="update.php?id=<?= $testimonial['id'] ?>" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Nom complet *</label>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($testimonial['name']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="position">Poste *</label>
                                    <input type="text" id="position" name="position" value="<?= htmlspecialchars($testimonial['position']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="company">Entreprise *</label>
                                    <input type="text" id="company" name="company" value="<?= htmlspecialchars($testimonial['company']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="rating">Note *</label>
                                    <input type="number" id="rating" name="rating" min="1" max="5" value="<?= htmlspecialchars($testimonial['rating']) ?>" required>
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
                                <label for="content">Témoignage *</label>
                                <textarea id="content" name="content" required><?= htmlspecialchars($testimonial['content']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="active" class="status-select">
                                        <option value="1" <?= $testimonial['active'] ? 'selected' : '' ?>>Actif</option>
                                        <option value="0" <?= !$testimonial['active'] ? 'selected' : '' ?>>Inactif</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Ordre d'affichage *</label>
                                    <select name="display_order" required>
                                        <option value="1" <?= ($testimonial['display_order'] == 1) ? 'selected' : '' ?>>Élevé</option>
                                        <option value="2" <?= ($testimonial['display_order'] == 2) ? 'selected' : '' ?>>Moyen</option>
                                        <option value="3" <?= ($testimonial['display_order'] == 3) ? 'selected' : '' ?>>Bas</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Image actuelle</label>
                                    <div class="current-image-container">
                                        <?php if (!empty($testimonial['image_path'])) : ?>
                                            <img src="../../<?= htmlspecialchars($testimonial['image_path']) ?>" class="current-image" alt="Image actuelle">
                                        <?php else : ?>
                                            <div class="current-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user" style="color: #ccc; font-size: 2rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nouvelle image</label>
                                    <div class="file-upload">
                                        <label class="file-upload-label" for="image">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--primary-blue);"></i>
                                            <p>Cliquez pour changer l'image</p>
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
                                    <i class="fas fa-save"></i> Enregistrer les modifications
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
                        preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu de la nouvelle image">`;
                        preview.style.display = 'block';
                        
                        // Décocher "Conserver l'image actuelle" si nouvelle image sélectionnée
                        document.querySelector('input[name="keep_image"]').checked = false;
                    }
                    
                    reader.readAsDataURL(file);
                }
            });

            // Gestion des étoiles de notation
            const stars = document.querySelectorAll('.rating-star');
            const ratingInput = document.getElementById('rating');

            function updateStars() {
                let value = parseInt(ratingInput.value) || 1;
                if (value < 1) value = 1;
                if (value > 5) value = 5;
                ratingInput.value = value;
                
                stars.forEach((star, index) => {
                    if (index < value) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            }

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = star.getAttribute('data-value');
                    ratingInput.value = value;
                    updateStars();
                });
            });

            ratingInput.addEventListener('input', function() {
                const value = parseInt(this.value);
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

            ratingInput.addEventListener('blur', updateStars);

            // Initialiser les étoiles
            updateStars();

            // Gestion du toggle switch
            const toggleSwitch = document.querySelector('.toggle-switch input');
            const toggleLabel = document.querySelector('.toggle-label:last-child');

            toggleSwitch.addEventListener('change', function() {
                toggleLabel.textContent = this.checked ? 'Actif' : 'Inactif';
            });
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