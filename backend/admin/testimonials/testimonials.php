<?php
    // Pour gérer les témoignages
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php";
    require_once "../../models/TestimonialModel.php";
    require_once "../../models/UserModel.php";

    verifierExpirationSession();

    $current_page = 'testimonials_list';

    $userModel = new UserModel();
    $adminList = $userModel->getAdmins(); // Cette méthode doit retourner un tableau de admins/super_admin avec id et name.

    $testimonialModel = new TestimonialModel();

    // Récupérer les filtres et critères de tri depuis l'URL (méthode GET)
    $filters = [];
    if (isset($_GET['created_by']) && $_GET['created_by'] !== '') {
        $filters['created_by'] = $_GET['created_by'];
    }
    if (isset($_GET['display_order']) && $_GET['display_order'] !== '') {
        $filters['display_order'] = $_GET['display_order'];
    }
    if (isset($_GET['rating']) && $_GET['rating'] !== '') {
        $filters['rating'] = $_GET['rating'];
    }
    if (isset($_GET['active']) && $_GET['active'] !== '') {
        $filters['active'] = $_GET['active'] === '1' ? 1 : 0; // Convertir en entier
    }

    $sortField = $_GET['sort_field'] ?? 'created_at';
    $sortOrder = $_GET['sort_order'] ?? 'DESC';

    // Définir la pagination souhaitée
    $limit = 8;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Récupérer le nombre total de témoignages (avec les mêmes filtres)
    $totalTestimonials = $testimonialModel->countTestimonials($filters);
    $totalPages = ceil($totalTestimonials / $limit);

    // Vérifier que la page demandée est valide
    if ($page < 1 || ($page > $totalPages && $totalPages > 0)) {
        header("Location: testimonials.php?page=1");
        exit();
    }

    // Récupérer les témoignages filtrés et triés
    $testimonials = $testimonialModel->getTestimonials($filters, $sortField, $sortOrder, $limit, $offset);

    // Fonction pour générer l'URL paginée avec les filtres actuels
    function getPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return 'testimonials.php?' . http_build_query($params);
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Témoignages | AFHE Admin</title>
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

                <div class="testimonial-management">
                    <!-- En-tête -->
                    <div class="testimonial-header">
                        <h1 class="testimonial-title">
                            <i class="fas fa-quote-left"></i> Gestion des Témoignages
                        </h1>
                        <a href="add.php" class="add-testimonial-btn">
                            <i class="fas fa-plus"></i> Nouveau Témoignage
                        </a>
                    </div>

                    <!-- Filtres -->
                    <div class="filter-section">
                        <form method="GET" action="testimonials.php">
                            <div class="filter-grid">
                                <div class="filter-group">
                                    <label>Créé par</label>
                                    <select name="created_by" class="filter-select">
                                        <option value="">Tous les administrateurs</option>
                                        <?php foreach ($adminList as $admin): ?>
                                            <option value="<?= $admin['id_user'] ?>" <?= (isset($_GET['created_by']) && $_GET['created_by'] == $admin['id_user']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($admin['username']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label>Priorité d'affichage</label>
                                    <select name="display_order" class="filter-select">
                                        <option value="">Toutes les priorités</option>
                                        <option value="1" <?= (isset($_GET['display_order']) && $_GET['display_order'] == '1') ? 'selected' : '' ?>>Élevée</option>
                                        <option value="2" <?= (isset($_GET['display_order']) && $_GET['display_order'] == '2') ? 'selected' : '' ?>>Moyenne</option>
                                        <option value="3" <?= (isset($_GET['display_order']) && $_GET['display_order'] == '3') ? 'selected' : '' ?>>Basse</option>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label>Note</label>
                                    <select name="rating" class="filter-select">
                                        <option value="">Toutes les notes</option>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= (isset($_GET['rating']) && $_GET['rating'] == $i) ? 'selected' : '' ?>>
                                                <?= $i ?> <?= $i > 1 ? 'étoiles' : 'étoile' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Statut</label>
                                    <select name="active" class="filter-select">
                                        <option value="">Tous les statuts</option>
                                        <option value="1" <?= (isset($_GET['active']) && $_GET['active'] == '1') ? 'selected' : '' ?>>Actif</option>
                                        <option value="0" <?= (isset($_GET['active']) && $_GET['active'] == '0') ? 'selected' : '' ?>>Inactif</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Trier par</label>
                                    <select name="sort_field" class="filter-select">
                                        <option value="name" <?= ($sortField == 'name') ? 'selected' : '' ?>>Nom</option>
                                        <option value="rating" <?= ($sortField == 'rating') ? 'selected' : '' ?>>Note</option>
                                        <option value="created_at" <?= ($sortField == 'created_at') ? 'selected' : '' ?>>Date</option>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label>Ordre</label>
                                    <select name="sort_order" class="filter-select">
                                        <option value="ASC" <?= ($sortOrder == 'ASC') ? 'selected' : '' ?>>Croissant</option>
                                        <option value="DESC" <?= ($sortOrder == 'DESC') ? 'selected' : '' ?>>Décroissant</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="apply-filters">
                                    <i class="fas fa-filter"></i> Appliquer
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau -->
                    <div class="testimonial-table-container">
                        <div class="table-header">
                            <h2><i class="fas fa-table"></i> Liste des Témoignages</h2>
                        </div>
                        
                        <table class="testimonial-table">
                            <thead>
                                <tr>
                                    <th>Personne</th>
                                    <th>Poste & Entreprise</th>
                                    <th>Note</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($testimonials)): ?>
                                    <tr>
                                        <td colspan="7" style="padding: 2rem; text-align: center;">
                                            <i class="fas fa-quote-right" style="font-size: 3rem; color: var(--primary-blue); margin-bottom: 1rem;"></i>
                                            <h3 style="color: var(--text-color);">Aucun témoignage trouvé</h3>
                                            <p style="color: #6c757d;">Modifiez vos critères de recherche ou ajoutez un nouveau témoignage.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <?php if (!empty($testimonial['image_path'])): ?>
                                                        <img src="../../<?= htmlspecialchars($testimonial['image_path']) ?>" class="testimonial-avatar" alt="<?= htmlspecialchars($testimonial['name']) ?>">
                                                    <?php else: ?>
                                                        <div class="testimonial-avatar" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-user" style="color: #ccc;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="testimonial-name"><?= htmlspecialchars($testimonial['name']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($testimonial['position']) ?></div>
                                                <div class="testimonial-company"><?= htmlspecialchars($testimonial['company']) ?></div>
                                            </td>
                                            <td class="testimonial-rating"><?= str_repeat('★', (int)$testimonial['rating']) ?></td>
                                            <td><?= htmlspecialchars(getDisplayOrderLabel($testimonial['display_order'])) ?></td>
                                            <td>
                                                <span class="status-badge <?= $testimonial['active'] ? 'status-active' : 'status-inactive' ?>">
                                                    <?= $testimonial['active'] ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(formatDate($testimonial['created_at'])) ?>
                                                <?php
                                                    $query = "SELECT username FROM users WHERE id_user = :id_user";
                                                    $stmt = $pdo->prepare($query);
                                                    $stmt->execute(['id_user' => $testimonial['created_by']]);
                                                    $username = $stmt->fetchColumn();
                                                ?>
                                                <div class="testimonial-created-by">Par : <?= htmlspecialchars($username) ?></div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?= $testimonial['id'] ?>" class="action-btn view-btn" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="update.php?id=<?= $testimonial['id'] ?>" class="action-btn edit-btn" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $testimonial['id'] ?>)" class="action-btn delete-btn" title="Supprimer">
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
            function confirmDelete(testimonialId) {
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
                            text: "Redirection vers la page d'affichage des témoignages.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + testimonialId;
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