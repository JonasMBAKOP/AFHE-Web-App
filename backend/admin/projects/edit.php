<?php
    //Modifier un projet

    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ProjectModel.php"; // Modèle projet

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'edit_project';

    // Vérifier que l'ID du projet est bien passé en paramètre
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Erreur : Aucun projet sélectionné.");
    }

    $id = (int) $_GET['id'];
    $projectModel = new ProjectModel();
    $project = $projectModel->getProjectById($id);

    if (!$project) {
        die("Erreur : Projet introuvable.");
    }

    // Récupérer les images secondaires existantes du projet
    // (si nécessaire pour l'affichage ou la gestion des images)
    $projectImages = $projectModel->getProjectImages($id);

    // $error = '';

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'short_description' => $_POST['short_description'],
            'status'            => $_POST['status'],
            'priority'          => $_POST['priority'],
            'active'            => $_POST['active']
        ];
            
        // Récupération des fichiers envoyés
        $mainImageFile = isset($_FILES['main_image']) ? $_FILES['main_image'] : null;
        $secondaryImageFiles = isset($_FILES['secondary_images']) ? $_FILES['secondary_images'] : null;

        // IDs des images secondaires à supprimer (via les cases à cocher)
        $deleteSecondaryIds = isset($_POST['delete_secondary']) ? $_POST['delete_secondary'] : [];

        if ($data['title'] === '') {
            $error = "Le nom du projet est obligatoire.";
        }
        else {
            // Mise à jour du projet via le modèle
            $result = $projectModel->updateProject($id, $data, $mainImageFile, $secondaryImageFiles, $deleteSecondaryIds);
            if ($result) {
                // Rediriger vers la page list.php pour visualiser le projet modifié
                header("Location: list.php?id=" . $id);
                exit;
            } else {
                echo "<script>alert('Erreur lors de la mise à jour.'); window.history.back();</script>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier un Projet | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/projects.css">
    </head>

    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="edit-project-content">
                    <div class="update-project-card">
                        <div class="update-project-header">
                            <i class="fas fa-edit" style="color: var(--primary-pink); font-size: 1.5rem;"></i>
                            <h2>Modifier le projet « <span style="color:#FF69B4;"><?= htmlspecialchars($project['title']) ?></span> »</h2>
                        </div>
                        
                        <form method="POST" action="edit.php?id=<?= $project['id_project'] ?>" enctype="multipart/form-data" class="project-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Titre du projet</label>
                                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($project['title']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select id="status" name="status" required>
                                        <option value="upcoming" <?= $project['status'] === 'upcoming' ? 'selected' : '' ?>>À venir</option>
                                        <option value="ongoing" <?= $project['status'] === 'ongoing' ? 'selected' : '' ?>>En cours</option>
                                        <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Terminé</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="short_description">Description courte</label>
                                <textarea id="short_description" name="short_description" rows="3" required><?= htmlspecialchars($project['short_description']) ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description complète</label>
                                <textarea id="description" name="description" rows="10" required><?= htmlspecialchars($project['description']) ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="priority">Priorité</label>
                                    <select id="priority" name="priority" required>
                                        <option value="1" <?= $project['priority'] === '1' ? 'selected' : '' ?>>Élevée</option>
                                        <option value="2" <?= $project['priority'] === '2' ? 'selected' : '' ?>>Moyenne</option>
                                        <option value="3" <?= $project['priority'] === '3' ? 'selected' : '' ?>>Basse</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="active">Statut</label>
                                    <select id="active" name="active" required>
                                        <option value="1" <?= $project['active'] == 1 ? 'selected' : '' ?>>Actif</option>
                                        <option value="0" <?= $project['active'] == 0 ? 'selected' : '' ?>>Inactif</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Image principale</label>
                                <div class="current-image-container">
                                    <?php if (!empty($project['main_image'])): ?>
                                        <p>Image actuelle :</p>
                                        <img src="../../<?= htmlspecialchars(bust_cache($project['main_image'])) ?>" class="current-image" alt="Image principale actuelle">
                                        <div class="image-actions">
                                            <div class="file-upload">
                                                <label class="file-upload-label" style="padding: 0.5rem 1rem;">
                                                    <i class="fas fa-sync-alt"></i> Remplacer
                                                    <input type="file" class="file-upload-input" name="main_image" accept="image/*">
                                                </label>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-upload">
                                            <label class="file-upload-label">
                                                <i class="fas fa-cloud-upload-alt"></i> Ajouter une image principale
                                                <input type="file" class="file-upload-input" name="main_image" accept="image/*">
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    <div class="file-upload-preview" id="mainImagePreview"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Images secondaires</label>
                                <?php if (!empty($projectImages)): ?>
                                    <p>Images secondaires actuelles :</p>
                                    <div class="secondary-images-grid">
                                        <?php foreach ($projectImages as $img): ?>
                                            <div class="secondary-image-item">
                                                <img src="../../<?= htmlspecialchars(bust_cache($img['image_path'])) ?>" class="secondary-image" alt="Image secondaire">
                                                <div style="padding: 0.5rem;">
                                                    <label class="delete-label">
                                                        <input type="checkbox" class="delete-checkbox" name="delete_secondary[]" value="<?= $img['id'] ?>">
                                                        <i class="fas fa-trash-alt"></i> Supprimer
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 1.5rem;">
                                    <div class="file-upload">
                                        <label class="file-upload-label">
                                            <i class="fas fa-plus-circle"></i> Ajouter des images secondaires
                                            <input type="file" class="file-upload-input" name="secondary_images[]" accept="image/*" multiple>
                                        </label>
                                    </div>
                                    <div class="file-upload-preview" id="secondaryImagesPreview"></div>
                                </div>
                            </div>
                            
                            <div class="update-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='list.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-update">
                                    <i class="fas fa-save"></i> Mettre à jour
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
        <script src="../assets/js/projects.js"></script>

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
