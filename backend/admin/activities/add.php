<?php
    //Ajouter une activité

    require_once "../../includes/auth_guard.php";    
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'create_activity';

    $model = new ActivityModel();
    $categories  = $model->getCategories();
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1) Récupère les données
        $data = [
            'title'             => trim($_POST['title'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'category_id'       => (int)($_POST['category_id'] ?? 0),
            'featured'          => isset($_POST['featured']) ? 1 : 0,
            'created_by'        => $_SESSION['id_user'] ?? 0 // Récupérer l'ID de l'administrateur connecté
        ];

        // 2) Les fichiers
        $mainImage       = $_FILES['main_image']       ?? null;
        $secondaryImages = $_FILES['secondary_images'] ?? null;

        // 3) Validation
        if ($data['title'] === '' || $data['category_id'] === 0) {
            $error = "Le titre et la catégorie sont obligatoires.";
        }
        elseif ($model->activityExists($data['title'], $data['category_id'])) { // Vérif unicité dans la catégorie
           $error = "Cette activité existe déjà dans cette catégorie ! Veuillez changer de catégorie ou le titre de votre activité !!";
        }
        else {
            $newId = $model->createActivity($data, $mainImage, $secondaryImages);
            if ($newId !== false) {
?>
                <script>
                    alert ("Nouvelle activité créée avec succès !");
                </script>
<?php
            } else {
                $error = "Erreur lors de la création de l’activité.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Créer une Activité | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/activities.css">
    </head>

    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-activity-content">
                    <div class="activity-form-container">
                        <h1><i class="fas fa-plus-circle"></i> Nouvelle Activité</h1>
                        
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= htmlspecialchars($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" class="activity-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Titre *</label>
                                    <input type="text" id="title" name="title" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Catégorie *</label>
                                    <select id="category_id" name="category_id" required>
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="short_description">Description courte *</label>
                                <textarea id="short_description" name="short_description" rows="4" required><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                                <small>Maximum 255 caractères</small>
                            </div>

                            <div class="form-group">
                                <label for="description">Description complète *</label>
                                <textarea id="description" name="description" rows="10" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Image principale *</label>
                                    <div class="file-upload">
                                        <label for="main_image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Cliquez pour téléverser</span>
                                            <small>Formats acceptés: JPG, JPEG, PNG (max 2MB)</small>
                                        </label>
                                        <input type="file" id="main_image" name="main_image" accept="image/*" required class="file-upload-input">
                                        <div class="file-upload-preview" id="mainImagePreview"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Images secondaires</label>
                                    <div class="file-upload">
                                        <label for="secondary_images" class="file-upload-label">
                                            <i class="fas fa-images"></i>
                                            <span>Cliquez pour téléverser</span>
                                            <small>Formats acceptés: JPG, JPEG, PNG (max 2MB)</small>
                                        </label>
                                        <input type="file" id="secondary_images" name="secondary_images[]" accept="image/*" multiple class="file-upload-input">
                                        <div class="file-upload-preview" id="secondaryImagesPreview"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group featured-checkbox">
                                <input type="checkbox" id="featured" name="featured" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                                <label for="featured">Mettre cette activité à la une</label>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='list.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Créer l'activité
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
        <script src="../assets/js/activities.js"></script>

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