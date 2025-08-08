<?php
    //Liste des catégories d'activités

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    require_once "../../models/UserModel.php";

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'categories_list';

    $model = new ActivityModel();

    // Pagination
    $limit = 10;                              
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page  = max($page, 1);
    $offset = ($page - 1) * $limit;
    $total        = $model->countCategories(/* onlyActive */ false);
    $totalPages   = (int)ceil($total / $limit);
    
    // Chargement des catégories avec pagination
    $stats = $model->getCategoryStats($limit, $offset, false);

    $userModel = new UserModel();
    $adminList = $userModel->getAdmins(); // Cette méthode doit retourner un tableau de admins/super_admin avec id et name.

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Catégories d'activités | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/categories.css">

    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="category-list-content">  
                    <!-- Header Section -->
                    <div class="category-header">
                        <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>

                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $total ?></h3>
                                <p>Catégories au total</p>
                            </div>
                        </div>
                        
                        <a href="create_category.php" class="add-category-btn">
                            <i class="fas fa-plus"></i>
                            Nouvelle Catégorie
                        </a>
                    </div>

                    <!-- Stats Summary -->
                    <!-- <div class="stats-summary"> -->
                        
                    <!-- </div> -->

                    <!-- Categories Table -->
                    <div class="category-table-container">
                        <table class="categories-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Activités</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats)): ?>
                                    <tr>
                                        <td colspan="6" class="no-results">
                                            <i class="fas fa-tags"></i>
                                            <h3>Aucune catégorie trouvée</h3>
                                            <p>Créez une nouvelle catégorie pour commencer</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stats as $row): ?>
                                        <tr class="<?= $row['active'] ? '' : 'inactive' ?>">
                                            <td>
                                                <a href="list.php?category_id=<?= $row['id'] ?>" class="category-name">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td>
                                                <span class="order-badge">
                                                    <?= htmlspecialchars(getDisplayOrderLabel($row['display_order'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $row['active'] ? 'active' : 'inactive' ?>">
                                                    <?= $row['active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="activity-count">
                                                    <?= htmlspecialchars($row['activity_count']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions-buttons">
                                                    <a href="list.php?category_id=<?= $row['id'] ?>" class="action-btn view" title="Voir les activités">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_category.php?id=<?= $row['id'] ?>" class="action-btn edit" title="Éditer">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['id'] ?>)" class="action-btn delete" title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?= buildPageUrl($page - 1) ?>" class="page-nav">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <a href="?<?= buildPageUrl($p) ?>" class="<?= ($p == $page) ? 'active' : '' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= buildPageUrl($page + 1) ?>" class="page-nav">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>
        <script src="../assets/js/categories.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(categoryId) {
                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "La suppression de cette catégorie supprimera toutes ses activités et leurs images. Cette action est irréversible !",
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
                            text: "Redirection vers la page d'affichage des catégories d'activité.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "deleteCategory.php?id=" + categoryId;
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