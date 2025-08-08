<?php
    //Liste des projets

    // Désactive la mise en cache HTTP
    session_cache_limiter('nocache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ProjectModel.php"; // Modèle projet
    require_once "../../models/UserModel.php";

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'projects_list';

    $projectModel = new ProjectModel(); // Instancier le modèle projet
    
    // Récupération des paramètres de filtres, de tri et de pagination en GET
    $status     = isset($_GET['status']) ? $_GET['status'] : '';
    $priority   = isset($_GET['priority']) ? $_GET['priority'] : '';
    $created_by = isset($_GET['created_by']) ? $_GET['created_by'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $active     = isset($_GET['active']) ? $_GET['active'] : '';

    $sortField  = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
    $sortOrder  = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    $page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit      = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset     = ($page - 1) * $limit;

    $filters = [
        'status'           => $status,
        'priority'         => $priority,
        'created_by'       => $created_by,
        'start_date'      => $start_date,
        'end_date'        => $end_date,
        'active'           => $active,
    ];

    $totalProjects = $projectModel->countProjects($filters);
    $totalPages = ceil($totalProjects / $limit);

    // Récupérer tous les projets
    $projects = $projectModel->getProjects($filters, $sortField, $sortOrder, $limit, $offset);

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
        <title>Gestion des Projets | AFHE Admin</title>
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

                <div class="project-list-content">  
                    <!-- Header Section -->
                    <div class="project-header">
                        <h1><i class="fas fa-project-diagram"></i> Gestion des Projets</h1>
                        <a href="add.php" class="add-project-btn">
                            <i class="fas fa-plus"></i>
                            Nouveau Projet
                        </a>
                    </div>

                    <!-- Filtres -->
                    <form method="GET" class="filter-form">
                        <p>
                            <b>Filtrer par:</b>
                            <label>Status:
                                <select name="status">
                                    <option value="">Tous</option>
                                    <option value="upcoming" <?= ($status == 'upcoming') ? 'selected' : '' ?>>À venir</option>
                                    <option value="ongoing" <?= ($status == 'ongoing') ? 'selected' : '' ?>>En cours</option>
                                    <option value="completed" <?= ($status == 'completed') ? 'selected' : '' ?>>Terminé</option>
                                </select>
                            </label>
                            
                            <label>Priorité:
                                <select name="priority">
                                    <option value="">Toutes</option>
                                    <option value="1" <?= ($priority == '1') ? 'selected' : '' ?>>Élevée</option>
                                    <option value="2" <?= ($priority == '2') ? 'selected' : '' ?>>Moyenne</option>
                                    <option value="3" <?= ($priority == '3') ? 'selected' : '' ?>>Basse</option>
                                </select>
                            </label>

                            <label>Créé par:
                                <select name="created_by">
                                    <option value="">Tous</option>
                                    <?php foreach ($adminList as $admin): ?>
                                        <option value="<?= $admin['id_user'] ?>" <?= (isset($_GET['created_by']) && $_GET['created_by'] == $admin['id_user']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($admin['username']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            
                            <label>Du:
                                <input type="date" name="start_date" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
                            </label>
                            <label>Au:
                                <input type="date" name="end_date" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
                            </label>

                            <label>Actif:
                                <select name="active">
                                    <option value="">Tous</option>
                                    <option value="1" <?= ($active === '1') ? 'selected' : '' ?>>Oui</option>
                                    <option value="0" <?= ($active === '0') ? 'selected' : '' ?>>Non</option>
                                </select>
                            </label>
                        </p>

                        <div style="display: flex; gap: 15px; align-items: center;">
                            <label>Tri par:
                                <select name="sort">
                                    <option value="title" <?= ($sortField == 'title') ? 'selected' : '' ?>>Titre</option>
                                    <option value="created_at" <?= ($sortField == 'created_at') ? 'selected' : '' ?>>Date de création</option>
                                </select>
                            </label>
                            
                            <label>Ordre:
                                <select name="order">
                                    <option value="ASC" <?= (strtoupper($sortOrder) == 'ASC') ? 'selected' : '' ?>>Croissant</option>
                                    <option value="DESC" <?= (strtoupper($sortOrder) == 'DESC') ? 'selected' : '' ?>>Décroissant</option>
                                </select>
                            </label>

                            <label>Par page:
                                <input type="number" name="limit" value="<?= htmlspecialchars($limit) ?>" min="1">
                            </label>
                            
                            <button type="submit">Appliquer</button>
                        </div>
                    </form>

                    <!-- Projects Table -->
                    <div class="project-table-container">
                        <div class="table-header">
                            <h2><i class="fas fa-table"></i> Liste des Projets</h2>
                        </div>

                        <table class="projects-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Description courte</th>
                                    <th>Status</th>
                                    <th>Priorité</th>
                                    <th>Date de création</th>
                                    <th>Actif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($project['main_image'])): ?>
                                                    <img src="../../<?= htmlspecialchars(bust_cache($project['main_image'])) ?>" class="project-image" alt="<?= htmlspecialchars($project['title']) ?>">
                                                <?php else: ?>
                                                    <div class="project-image-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($project['title']) ?></td>
                                            <td><?= htmlspecialchars($project['short_description']) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= htmlspecialchars($project['status']) ?>">
                                                    <?= htmlspecialchars($project['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="priority-badge priority-<?= getPriorityClass($project['priority']) ?>">
                                                    <?= htmlspecialchars(getPriority($project['priority'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(formatDate($project['created_at'])) ?>
                                                <?php
                                                    $query = "SELECT username FROM users WHERE id_user = :id_user";
                                                    $stmt = $pdo->prepare($query);
                                                    $stmt->execute(['id_user' => $project['created_by']]);
                                                    $username = $stmt->fetchColumn();
                                                ?>
                                                <div class="project-created-by">Par : <?= htmlspecialchars($username) ?></div>
                                            </td>
                                            <td>
                                                <span class="active-status active-<?= ($project['active'] == 1) ? 'true' : 'false' ?>"></span>
                                                <?= ($project['active'] == 1) ? 'Oui' : 'Non' ?>
                                            </td>
                                            <td>
                                                <div class="actions-buttons">
                                                    <a href="view.php?id=<?= $project["id_project"] ?>" class="action-btn view" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?= $project["id_project"] ?>" class="action-btn edit" title="Éditer">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $project["id_project"] ?>)" class="action-btn delete" title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 30px;">
                                            <i class="fas fa-project-diagram" style="font-size: 48px; color: var(--primary-blue); margin-bottom: 15px;"></i>
                                            <h3 style="color: var(--text-color); margin: 0 0 10px 0;">Aucun projet trouvé</h3>
                                            <p style="color: #6C757D; margin: 0;">Modifiez vos critères de recherche ou ajoutez un nouveau projet.</p>
                                        </td>
                                    </tr>
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
                            text: "Redirection vers la page d'affichage des projets.",
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
