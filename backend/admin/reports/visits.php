<?php
    // site_stats_report.php
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php";
    require_once "../../models/ReportModel.php";

    verifierExpirationSession();

    $current_page = 'visits';

    $reportModel = new ReportModel();
    
    // Récupérer les paramètres de filtre via GET
    $pageFilter = $_GET['page_name'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $minVisits = isset($_GET['min_visits']) && is_numeric($_GET['min_visits']) ? (int)$_GET['min_visits'] : 0;
    $minUniqueVisitors = isset($_GET['min_unique_visitors']) && is_numeric($_GET['min_unique_visitors']) ? (int)$_GET['min_unique_visitors'] : 0;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page_num']) && is_numeric($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

    // Récupérer les paramètres de tri avec GET
    $sortBy = $_GET['sortBy'] ?? 'visit_date';
    $order = $_GET['order'] ?? 'DESC';
    
    // Récupérer les statistiques avec les filtres et le tri appliqués
    $stats = $reportModel->getStats($pageFilter, $startDate, $endDate, $minVisits, $minUniqueVisitors, $sortBy, $order, $limit, $page);

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rapport de Visites | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/reports.css">
        <link rel="stylesheet" href="../assets/css/users.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="visits-content">
                    <div class="stats-container">
                        <div class="stats-header">
                            <h1><i class="fas fa-chart-line"></i> Statistiques de visites</h1>

                            <button type="button" class="filter-toggle" id="filterToggle">
                                <i class="fas fa-sliders-h"></i> Options d'affichage
                            </button>
                        </div>

                        <div class="filter-section">
                            <!-- Formulaire de filtre et de tri -->
                            <form class="filter-form" method="GET" action="visits.php" id="filterForm">
                                <!-- Pour filtrer -->
                                <div class="filter-options" id="filterOptions">
                                    <div class="filter-grid">
                                        <div class="filter-group">
                                            <label for="page">Page :</label>
                                            <select name="page_name" id="page">
                                                <option value="">Toutes</option>
                                                <option value="Accueil" <?= $pageFilter === "Accueil" ? "selected" : "" ?>>Accueil</option>
                                                <option value="Activités et Projets" <?= $pageFilter === "Activités et Projets" ? "selected" : "" ?>>Activités et Projets</option>
                                                <option value="Contact" <?= $pageFilter === "Contact" ? "selected" : "" ?>>Contact</option>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label for="start_date">Du :</label>
                                            <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate) ?>">
                                        </div>
                                        
                                        <div class="filter-group">
                                            <label for="end_date">Au :</label>
                                            <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate) ?>">
                                        </div>

                                        <div class="filter-group">
                                            <label for="min_visits">Visites Min. :</label>
                                            <input type="number" name="min_visits" id="min_visits" value="<?= htmlspecialchars($minVisits) ?>">
                                        </div>

                                        <div class="filter-group">
                                            <label for="min_unique_visitors">Visiteurs uniques Min. :</label>
                                            <input type="number" name="min_unique_visitors" id="min_unique_visitors" value="<?= htmlspecialchars($minUniqueVisitors) ?>">
                                        </div>

                                        <!-- Pour trier -->
                                        <div class="filter-group">
                                            <label>Trier par :</label>
                                            <select name="sortBy">
                                                <option value="visit_date" <?= $sortBy === 'visit_date' ? 'selected' : '' ?>>Date</option>
                                                <option value="visit_count" <?= $sortBy === 'visit_count' ? 'selected' : '' ?>>Nombre de visites</option>
                                                <option value="page_name" <?= $sortBy === 'page_name' ? 'selected' : '' ?>>Nom de page</option>
                                                <option value="unique_visitors" <?= $sortBy === 'unique_visitors' ? 'selected' : '' ?>>Visiteurs uniques</option>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>Ordre :</label>
                                            <select name="order">
                                                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Décroissant</option>
                                                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Croissant</option>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label for="limit">Nombre de résultats par page :</label>
                                            <select name="limit" id="limit">
                                                <option value="">Sélectionner une option</option>
                                                <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                                            </select>
                                        </div>
                                    </div>
                                
                                    <div class="filter-actions">
                                        <button type="submit" class="btn-primary">
                                            <i class="fas fa-filter"></i> Appliquer les filtres
                                        </button>
                                        <button type="button" class="btn-secondary" onclick="window.location.href='visits.php'">
                                            <i class="fas fa-undo"></i> Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tableau des statistiques -->
                        <div class="table-responsive">
                            <table class="stats-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Page</th>
                                        <th>Nombre de visites</th>
                                        <th>Visiteurs uniques</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stats)): ?>
                                        <?php foreach ($stats as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['visit_date']) ?></td>
                                                <td><?= htmlspecialchars($row['page_name']) ?></td>
                                                <td class="visit-count"><?= htmlspecialchars($row['visit_count']) ?></td>
                                                <td class="unique-visitors"><?= htmlspecialchars($row['unique_visitors']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align:center;">Aucune donnée trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Système de pagination -->
                        <?php
                            $totalPages = $reportModel->getTotalPages($limit);
                            // $hasNext = $reportModel->hasNextPage($page, $limit);
                        ?>

                        <div class="pagination">
                            <?php if ($totalPages > 1) : ?> <!-- Afficher la pagination seulement si plusieurs pages existent -->

                                <?php if ($page == 1) : ?> <!-- Cas de la première page -->
                                    <span>Page <?= $page ?></span>
                                    <?php if ((!empty($stats)) && ($page < $totalPages)) : ?> <!-- Si ce n'est pas la dernière page, afficher "Page suivante" -->
                                        <a href="visits.php?page_name=<?= htmlspecialchars($pageFilter) ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>&min_visits=<?= htmlspecialchars($minVisits) ?>&min_unique_visitors=<?= htmlspecialchars($minUniqueVisitors) ?>&sortBy=<?= htmlspecialchars($sortBy) ?>&order=<?= htmlspecialchars($order) ?>&limit=<?= $limit ?>&page_num=<?= $page + 1 ?>">Page suivante <i class="fas fa-chevron-right"></i></a>
                                    <?php endif; ?>
                                    <?php else : ?> <!-- Cas à partir de la deuxième page -->
                                        <a href="visits.php?page_name=<?= htmlspecialchars($pageFilter) ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>&min_visits=<?= htmlspecialchars($minVisits) ?>&min_unique_visitors=<?= htmlspecialchars($minUniqueVisitors) ?>&sortBy=<?= htmlspecialchars($sortBy) ?>&order=<?= htmlspecialchars($order) ?>&limit=<?= $limit ?>&page_num=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i> Page précédente</a>
                                        <span>Page <?= $page ?></span>

                                        <?php if ((!empty($stats)) && ($page < $totalPages)) : ?> <!-- Afficher "Page suivante" si on n'est pas à la dernière page -->
                                            <a href="visits.php?page_name=<?= htmlspecialchars($pageFilter) ?>&start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>&min_visits=<?= htmlspecialchars($minVisits) ?>&min_unique_visitors=<?= htmlspecialchars($minUniqueVisitors) ?>&sortBy=<?= htmlspecialchars($sortBy) ?>&order=<?= htmlspecialchars($order) ?>&limit=<?= $limit ?>&page_num=<?= $page + 1 ?>">Page suivante <i class="fas fa-chevron-right"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>                    
                    </div>
                </div>

                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>

        
        <script src="../assets/js/admin_script.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterToggle = document.getElementById('filterToggle');
                const filterOptions = document.getElementById('filterOptions');
                const filterForm = document.getElementById('filterForm');
                const resetFilters = document.getElementById('resetFilters');
                
                // Gestion du toggle des options
                filterToggle.addEventListener('click', function() {
                    filterOptions.classList.toggle('expanded');
                    
                    // Changement d'icône
                    const icon = this.querySelector('i');
                    if (filterOptions.classList.contains('expanded')) {
                        icon.classList.replace('fa-sliders-h', 'fa-chevron-up');
                    } else {
                        icon.classList.replace('fa-chevron-up', 'fa-sliders-h');
                    }
                });
                
                // Fermer les options après soumission
                filterForm.addEventListener('submit', function() {
                    filterOptions.classList.remove('expanded');
                    filterToggle.querySelector('i').classList.replace('fa-chevron-up', 'fa-sliders-h');
                });
                
                // Réinitialisation des filtres
                resetFilters.addEventListener('click', function() {
                    window.location.href = 'visits.php';
                });
                
                // Ouvrir automatiquement si des filtres sont actifs
                const hasActiveFilters = <?= !empty($pageFilter) || !empty($startDate) || !empty($endDate) || 
                                        $minVisits > 0 || $minUniqueVisitors > 0 ? 'true' : 'false' ?>;
                
                if (hasActiveFilters) {
                    filterOptions.classList.add('expanded');
                    filterToggle.querySelector('i').classList.replace('fa-sliders-h', 'fa-chevron-up');
                }
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