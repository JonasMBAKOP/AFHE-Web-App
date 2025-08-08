<?php
    // Afficher les détails d'un projet spécifique

    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ProjectModel.php";

    verifierExpirationSession();

    $current_page = 'view_project';

    $projectModel = new ProjectModel();

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("ID de projet manquant.");
    }

    $projectId = $_GET['id'];
    $project = $projectModel->getProjectById($projectId);
    if (!$project) {
        die("Projet introuvable.");
    }

    $projectImages = $projectModel->getProjectImages($projectId);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails du Projet | AFHE Admin</title>
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

                <div class="view-project-content">
                    <div class="project-view-card">
                        <div class="project-view-header">
                            <?php if (!empty($project['main_image'])) : ?>
                                <img src="../../<?= htmlspecialchars(bust_cache($project['main_image'])) ?>" class="project-main-image" alt="<?= htmlspecialchars($project['title']) ?>">
                            <?php else : ?>
                                <div class="project-main-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="project-info">
                                <h1 class="project-title"><?= htmlspecialchars($project['title']) ?></h1>
                                
                                <div class="project-meta">
                                    <div class="project-meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?= htmlspecialchars(getStatus($project['status'])) ?></span>
                                    </div>
                                    
                                    <div class="project-meta-item">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= htmlspecialchars(getPriority($project['priority'])) ?></span>
                                    </div>
                                    
                                    <div class="project-meta-item">
                                        <i class="fas fa-user"></i>
                                        <span>
                                            <?php
                                                $query = "SELECT username FROM users WHERE id_user = :id_user";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->execute(['id_user' => $project['created_by']]);
                                                $username = $stmt->fetchColumn();
                                                echo htmlspecialchars($username);
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="project-meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= htmlspecialchars(formatDate($project['created_at'])) ?></span>
                                    </div>
                                    
                                    <div class="project-meta-item">
                                        <i class="fas fa-power-off"></i>
                                        <span><?= ($project['active'] == 1) ? 'Actif' : 'Inactif' ?></span>
                                    </div>
                                </div>
                                
                                <div class="project-description">
                                    <h3><i class="fas fa-align-left"></i> Description courte</h3>
                                    <p><?= htmlspecialchars($project['short_description']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="project-section">
                            <h3><i class="fas fa-info-circle"></i> Description complète</h3>
                            <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                        </div>
                        
                        <div class="project-section">
                            <h3><i class="fas fa-images"></i> Images secondaires</h3>
                            <?php if (!empty($projectImages)): ?>
                                <div class="gallery">
                                    <?php foreach($projectImages as $img): ?>
                                        <div class="gallery-item">
                                            <img src="../../<?= htmlspecialchars(bust_cache($img['image_path'])) ?>" class="gallery-image" alt="Image secondaire du projet">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #6c757d;">Aucune image secondaire disponible</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="project-actions">
                            <a href="list.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <a href="edit.php?id=<?= $project["id_project"] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Modifier le projet
                            </a>
                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $project["id_project"] ?>)" class="btn-delete">
                                <i class="fas fa-trash-alt"></i> Supprimer le projet
                            </a>
                        </div>
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

        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(projectId) {
                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Cette action est irréversible !",
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
                            text: "Redirection vers la page d'affichage des utilisateurs.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + projectId;
                        });
                    }
                });
            }
        </script>
    </body>
</html>