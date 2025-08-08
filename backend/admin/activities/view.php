<?php
    //Affichage d'une seule activité

    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'view_activity';

    $model = new ActivityModel();

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("ID d'activité manquant.");
    }

    $activityId = $_GET['id'];
    $act = $model->getActivityById($activityId);
    if (!$act) {
        die("Activité introuvable.");
    }

    $images = $model->getActivityImages($activityId);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Activités | AFHE Admin</title>
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

                <div class="view-activity-content">
                    <div class="activity-view-card">
                        <!-- Header avec boutons d'action -->
                        <div class="activity-view-header">
                            <h1>
                                <i class="fas fa-tasks"></i> 
                                <?= htmlspecialchars($act['title']) ?>
                                <span class="activity-status">
                                    <?= $act['featured'] ? '<span class="featured-badge">À la une</span>' : '' ?>
                                </span>
                            </h1>
                            
                            <div class="activity-actions">
                                <a href="list.php" class="btn-back">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                                <a href="edit.php?id=<?= $act['id_activity'] ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?= $act['id_activity'] ?>)" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </div>
                        </div>

                        <!-- Section principale -->
                        <div class="activity-view-main">
                            <!-- Image principale -->
                            <div class="main-image-section">
                                <h2><i class="fas fa-image"></i> Image principale</h2>
                                <?php if ($act['main_image']): ?>
                                    <div class="main-image-container">
                                        <img src="../../<?= htmlspecialchars($act['main_image']) ?>" class="main-image" alt="Image principale">
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <p>Aucune image principale</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Métadonnées -->
                            <div class="activity-meta">
                                <div class="meta-item">
                                    <span class="meta-label"><i class="fas fa-tag"></i> Catégorie:</span>
                                    <span class="meta-value category-badge" style="background-color: <?= htmlspecialchars($act['category_color'] ?? '#6C757D') ?>">
                                        <?= htmlspecialchars($act['category_name'] ?? '—') ?>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-label"><i class="fas fa-user"></i> Créée par:</span>
                                    <span class="meta-value">
                                        <?php
                                            $query = "SELECT username FROM users WHERE id_user = :id_user";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->execute(['id_user' => $act['created_by']]);
                                            $username = $stmt->fetchColumn();
                                            echo htmlspecialchars($username);
                                        ?>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-label"><i class="fas fa-calendar-alt"></i> Date de création:</span>
                                    <span class="meta-value">
                                        <?= htmlspecialchars(formatDate($act['created_at'])) ?>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-label"><i class="fas fa-star"></i> À la une:</span>
                                    <span class="meta-value featured-status featured-<?= $act['featured'] ? 'yes' : 'no' ?>">
                                        <?= $act['featured'] ? 'Oui' : 'Non' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Descriptions -->
                            <div class="activity-descriptions">
                                <?php if ($act['short_description']): ?>
                                    <div class="description-section">
                                        <h3><i class="fas fa-align-left"></i> Description courte</h3>
                                        <div class="description-content">
                                            <?= nl2br(htmlspecialchars($act['short_description'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($act['description']): ?>
                                    <div class="description-section">
                                        <h3><i class="fas fa-align-justify"></i> Description complète</h3>
                                        <div class="description-content">
                                            <?= nl2br(htmlspecialchars($act['description'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Images secondaires -->
                            <div class="secondary-images-section">
                                <h2><i class="fas fa-images"></i> Images secondaires</h2>
                                <?php if (!empty($images)): ?>
                                    <div class="gallery">
                                        <?php foreach ($images as $img): ?>
                                            <div class="gallery-item">
                                                <img src="../../<?= htmlspecialchars($img['image_path']) ?>" class="gallery-image" alt="<?= htmlspecialchars($img['caption'] ?? ''); ?>">
                                                <?php if (!empty($img['caption'])): ?>
                                                    <div class="gallery-caption"><?= htmlspecialchars($img['caption']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-images">
                                        <i class="fas fa-image"></i>
                                        <p>Aucune image secondaire disponible</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>
        <script src="../assets/js/activities.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(activityId) {
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
                            text: "Redirection vers la page d'affichage des activités.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + activityId;
                        });
                    }
                });
            }
        </script>

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