<?php
    //Liste des activités
    
    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    require_once "../../models/UserModel.php";

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'activities_list';

    $model = new ActivityModel();
    $categories = $model->getCategories();
    
    // Récupération des paramètres de filtre et tri
    $filters = [];
    if (!empty($_GET['category_id'])) {
        $filters['category_id'] = (int)$_GET['category_id'];
    }
    if (!empty($_GET['created_by'])) {
        $filters['created_by'] = (int)$_GET['created_by'];
    }

    $sortField  = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'title';
    $sortOrder  = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

    // Pagination
    $limit  = isset($_GET['limit'])  ? (int)$_GET['limit']  : 10;
    $page   = isset($_GET['page'])   ? (int)$_GET['page']   : 1;
    $page   = max($page, 1);
    $offset = ($page - 1) * $limit;

    // Récupère data
    $total      = $model->countActivities($filters);
    $activities = $model->getActivities($filters, $sortField, $sortOrder, $limit, $offset);
    $totalPages = (int)ceil($total / $limit);

    $userModel = new UserModel();
    $adminList = $userModel->getAdmins(); // Cette méthode doit retourner un tableau de admins/super_admin avec id et name.

    // Fonction pour générer l'URL paginée avec les filtres actuels
    function getPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return 'list.php?' . http_build_query($params);
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des Activités | AFHE Admin</title>
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

                <div class="activity-list-content">  
                    <!-- Header Section -->
                    <div class="activity-header">
                        <h1><i class="fas fa-tasks"></i> Gestion des Activités</h1>
                        <a href="add.php" class="add-activity-btn">
                            <i class="fas fa-plus"></i>
                            Nouvelle Activité
                        </a>
                    </div>

                    <!-- Filtres -->
                    <form method="GET" class="filter-form">
                        <div class="filter-group">
                            <label>Catégorie:</label>
                            <select name="category_id">
                                <option value="">Toutes</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($filters['category_id'] ?? null) === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Créée par:</label>
                            <select name="created_by">
                                <option value="">Tous</option>
                                <?php foreach ($adminList as $admin): ?>
                                    <option value="<?= $admin['id_user'] ?>" <?= (isset($_GET['created_by']) && $_GET['created_by'] == $admin['id_user']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($admin['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Tri par:</label>
                            <select name="sort_field">
                                <option value="title"      <?= $sortField==='title'      ? 'selected':'' ?>>Titre</option>
                                <option value="created_at" <?= $sortField==='created_at' ? 'selected':'' ?>>Date création</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Ordre:</label>
                            <select name="sort_order">
                                <option value="ASC"  <?= strtoupper($sortOrder)==='ASC'  ? 'selected':'' ?>>Croissant</option>
                                <option value="DESC" <?= strtoupper($sortOrder)==='DESC' ? 'selected':'' ?>>Décroissant</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Par page:</label>
                            <input type="number" name="limit" min="1" value="<?= $limit ?>">
                        </div>

                        <button type="submit" class="filter-btn">Appliquer</button>
                    </form>

                    <!-- Tableau des activités -->
                    <div class="activity-table-container">
                        <div class="table-header">
                            <h2><i class="fas fa-table"></i> Liste des Activités <span class="total-count">(<?= $total ?>)</span></h2>
                        </div>

                        <table class="activities-table">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Description</th>
                                    <th>À la une</th>
                                    <th>Créée par</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activities)): ?>
                                    <tr>
                                        <td colspan="8" class="no-results" style="text-align: center; padding: 40px;">
                                            <i class="fas fa-tasks"></i>
                                            <h3>Aucune activité trouvée</h3>
                                            <p>Modifiez vos critères de recherche ou ajoutez une nouvelle activité</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($activities as $act): ?>
                                        <tr>
                                            <td>
                                                <span class="category-badge" style="background-color: <?= htmlspecialchars($act['category_color'] ?? '#6C757D') ?>">
                                                    <?= htmlspecialchars($act['category_name'] ?? '–') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($act['main_image']): ?>
                                                    <img src="../../<?= htmlspecialchars($act['main_image']) ?>" class="activity-image" alt="<?= htmlspecialchars($act['title']) ?>">
                                                <?php else: ?>
                                                    <div class="image-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($act['title']) ?></td>
                                            <td><?= htmlspecialchars($act['short_description']) ?></td>
                                            <td>
                                                <span class="featured-badge featured-<?= $act['featured'] ? 'yes' : 'no' ?>">
                                                    <?= $act['featured'] ? 'Oui' : 'Non' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="created-by">
                                                    <?php
                                                        $query = "SELECT username FROM users WHERE id_user = :id_user";
                                                        $stmt = $pdo->prepare($query);
                                                        $stmt->execute(['id_user' => $act['created_by']]);
                                                        $username = $stmt->fetchColumn();
                                                        echo (htmlspecialchars($username));
                                                    ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars(formatDate($act['created_at'])) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?= $act['id_activity'] ?>" class="action-btn view" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?= $act['id_activity'] ?>" class="action-btn edit" title="Éditer">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $act['id_activity'] ?>)" class="action-btn delete" title="Supprimer">
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
                                <a href="<?= getPaginationUrl(1) ?>" class="pagination-link" title="Première page">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="<?= getPaginationUrl($page - 1) ?>" class="pagination-link" title="Page précédente">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                                // Afficher jusqu'à 5 pages autour de la page courante
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);

                                for ($i = $start; $i <= $end; $i++): ?>
                                    <a href="<?= getPaginationUrl($i) ?>" class="pagination-link <?= $i == $page ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= getPaginationUrl($page + 1) ?>" class="pagination-link" title="Page suivante">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="<?= getPaginationUrl($totalPages) ?>" class="pagination-link" title="Dernière page">
                                    <i class="fas fa-angle-double-right"></i>
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