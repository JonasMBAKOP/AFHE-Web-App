<?php
    //Modifier une activité

    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'edit_activity';

    // Vérifier que l'ID de l'activité est bien passé en paramètre
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Erreur : Aucune activité sélectionnée.");
    }

    $model = new ActivityModel();
    $id         = (int)($_GET['id'] ?? 0);
    $activity   = $model->getActivityById($id);
    $categories = $model->getCategories();
    $images     = $model->getActivityImages($id);

    if (!$activity) {
        die("Erreur : Activité introuvable.");
    }


    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1) Collecte des champs
        $data = [
            'title'             => trim($_POST['title']),
            'description'       => trim($_POST['description']),
            'short_description' => trim($_POST['short_description']),
            'category_id'       => (int)$_POST['category_id'],
            'featured'          => isset($_POST['featured']) ? 1 : 0,
        ];

        // 2) Unicité
        if ($data['title'] === '') {
            $error = "Le titre est obligatoire.";
        }
        elseif ($model->activityExists($data['title'], $data['category_id']) && $data['title'] !== $activity['title']) {
            $error = "Une activité du même nom existe déjà dans cette catégorie.";
        }
        else{
            // 3) Fichiers et suppressions
            $mainImageFile = isset($_FILES['main_image']) ? $_FILES['main_image'] : null;
            $secondaryImageFiles = isset($_FILES['secondary_images']) ? $_FILES['secondary_images'] : null;
            $deleteSecondaryIds   = $_POST['remove_images']     ?? [];  // tableau d’IDs cochés

            // 4) Appel de la méthode
            $ok = $model->updateActivity($id, $data, $mainImageFile, $secondaryImageFiles, $deleteSecondaryIds);
            if ($ok) {
                redirect("list.php");
            }
            $error = "Erreur lors de la mise à jour de l’activité.";
        }
    }

    // sélection courante de catégorie
    $selCat = $_POST['category_id'] ?? $activity['category_id'];
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier une Activité | AFHE Admin</title>
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

                <div class="edit-activity-content">
                    <div class="activity-form-container">
                        <h1><i class="fas fa-edit"></i> Modifier l'activité « <span style="color:#FF69B4;"><?= htmlspecialchars($activity['title']) ?></span> »</h1>
                        
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= htmlspecialchars($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="edit.php?id=<?= $activity['id_activity'] ?>" enctype="multipart/form-data" class="activity-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Titre *</label>
                                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($activity['title']) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Catégorie *</label>
                                    <select id="category_id" name="category_id" required>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= $activity['category_id'] === $c['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="short_description">Description courte *</label>
                                <textarea id="short_description" name="short_description" rows="4" required><?= htmlspecialchars($activity['short_description']) ?></textarea>
                                <small>Maximum 255 caractères</small>
                            </div>

                            <div class="form-group">
                                <label for="description">Description complète *</label>
                                <textarea id="description" name="description" rows="10" required><?= htmlspecialchars($activity['description']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Image principale</label>
                                    <div class="file-upload">
                                        <label for="main_image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Cliquez pour changer l'image</span>
                                            <small>Laisser vide pour conserver l'actuelle</small>
                                        </label>
                                        <input type="file" id="main_image" name="main_image" accept="image/*" class="file-upload-input">
                                    </div>
                                    <?php if ($activity['main_image']): ?>
                                        <div class="current-image-container">
                                            <p>Image actuelle :</p>
                                            <img src="../../<?= $activity['main_image'] ?>" class="current-image" alt="Image actuelle">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Images secondaires</label>
                                    <div class="file-upload">
                                        <label for="secondary_images" class="file-upload-label">
                                            <i class="fas fa-images"></i>
                                            <span>Ajouter des images</span>
                                            <small>Formats acceptés: JPG, PNG (max 2MB)</small>
                                        </label>
                                        <input type="file" id="secondary_images" name="secondary_images[]" accept="image/*" multiple class="file-upload-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Images secondaires existantes -->
                            <?php if (!empty($images)): ?>
                                <div class="existing-images">
                                    <h3><i class="fas fa-images"></i> Images secondaires existantes</h3>
                                    <div class="secondary-images-grid">
                                        <?php foreach ($images as $img): ?>
                                            <div class="secondary-image-item">
                                                <img src="../../<?= $img['image_path'] ?>" class="secondary-image" alt="Image secondaire">
                                                <div class="image-actions">
                                                    <input type="checkbox" id="remove_<?= $img['id'] ?>" name="remove_images[]" value="<?= $img['id'] ?>" class="delete-checkbox">
                                                    <label for="remove_<?= $img['id'] ?>" class="delete-label">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group featured-checkbox">
                                <input type="checkbox" id="featured" name="featured" value="1" <?= ($activity['featured'] === '1' ? 'checked' : '') ?>>
                                <label for="featured">Mettre cette activité à la une</label>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='list.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Enregistrer
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