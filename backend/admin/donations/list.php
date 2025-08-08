<?php
    //Liste des Dons

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/DonationModel.php"; // Modèle utilisateur
    require_once "../../models/UserModel.php";

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'donations_list';

    $userModel = new UserModel();
    $adminList = $userModel->getAdmins(); // Cette méthode doit retourner un tableau de admins/super_admin avec id et name.

    // Instancier le modèle
    $donationModel = new DonationModel();

    // Récupération des paramètres GET
    $sortBy = $_GET['sortBy'] ?? 'created_at';
    $order = $_GET['order'] ?? 'DESC';

    // Filtres
    $filters   = [
        'currency'       => $_GET['currency']       ?? 'all',
        'payment_method' => $_GET['payment_method'] ?? 'all',
        'status'         => $_GET['status']         ?? 'all',
        'is_anonymous'   => $_GET['is_anonymous']   ?? 'all',
        'created_by'     => $_GET['created_by']     ?? 'all'
    ];

    $currencyFilter     = $_GET['currency']      ?? 'all';
    $paymentFilter      = $_GET['payment_method']?? 'all';
    $statusFilter       = $_GET['status']        ?? 'all';
    $anonymousFilter    = $_GET['is_anonymous']  ?? 'all';
    $creatorFilter      = $_GET['created_by']    ?? 'all';

    $page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit      = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset     = ($page - 1) * $limit;

    // Préparation du tableau de filtres pour le modèle
    $filters = [
        'currency'       => $currencyFilter,
        'payment_method' => $paymentFilter,
        'status'         => $statusFilter,
        'is_anonymous'   => $anonymousFilter,
        'created_by'     => $creatorFilter
    ];
    
    $totalItems  = $donationModel->countDonations($filters);
    $totalPages  = (int) ceil($totalItems / $limit);

    // Récupération des données
    $donations      = $donationModel->getDonations($sortBy, $order, $filters, $limit, $offset);
    $currencies     = $donationModel->getDistinct('currency');
    $methods        = $donationModel->getDistinct('payment_method');
    $statuses       = $donationModel->getDistinct('status');

    // function modifyQueryString($params) {
    //     $currentParams = $_GET;
    //     $mergedParams = array_merge($currentParams, $params);
    //     return 'list.php?' . http_build_query($mergedParams);
    // }

    function buildPaginationUrl($page) {
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
        <title>Gestion des Dons | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/donations.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="donation-list-content">
                    <div class="donations-header">
                        <div class="header-left">
                            <h1><i class="fas fa-hand-holding-heart"></i> Gestion des Dons</h1>
                            <div class="header-stats">
                                <div class="stat-card total">
                                    <div class="stat-icon">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="value"><?= $totalItems ?></span>
                                        <span class="label">Dons total</span>
                                    </div>
                                </div>
                                <div class="stat-card pages">
                                    <div class="stat-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="value"><?= $page ?>/<?= $totalPages ?></span>
                                        <span class="label">Pages</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="header-actions">
                            <button class="btn btn-filter" id="toggleFilters">
                                <i class="fas fa-sliders-h"></i> Filtres
                            </button>
                        </div>
                    </div>
     
                    <div class="filters-panel" id="filtersPanel">
                        <!-- FILTRES & TRI -->
                        <form method="GET" action="list.php">
                            <!-- Filtres -->
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label>Devise :</label>
                                    <select name="currency">
                                        <option value="all">Toutes</option>
                                        <?php foreach($currencies as $c): ?>
                                            <option value="<?= $c ?>" <?= $currencyFilter===$c ? 'selected':'' ?>>
                                                <?= $c ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Méthode de paiement :</label>
                                    <select name="payment_method">
                                        <option value="all">Toutes</option>
                                        <?php foreach($methods as $m): ?>
                                            <option value="<?= $m ?>" <?= $paymentFilter===$m ? 'selected':'' ?>>
                                                <?= $m ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Statut :</label>
                                    <select name="status">
                                        <option value="all">Tous</option>
                                        <?php foreach($statuses as $s): ?>
                                            <option value="<?= $s ?>" <?= $statusFilter===$s ? 'selected':'' ?>>
                                                <?= ucfirst($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Anonyme :</label>
                                    <select name="is_anonymous">
                                        <option value="all">Tous</option>
                                        <option value="1" <?= $anonymousFilter==='1' ? 'selected':'' ?>>Oui</option>
                                        <option value="0" <?= $anonymousFilter==='0' ? 'selected':'' ?>>Non</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label>Créé par :</label>
                                    <select name="created_by">
                                        <option value="all">Tous</option>
                                        <?php foreach($adminList as $admin): ?>
                                            <option value="<?= $admin['id_user'] ?>" <?= (isset($_GET['created_by']) && $_GET['created_by'] == $admin['id_user']) ? 'selected':'' ?>>
                                                <?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tri -->
                                <div class="filter-group">
                                    <label>Trier par :</label>
                                    <div class="sort-options">
                                        <select name="sortBy">
                                            <option value="created_at" <?= $sortBy=='created_at' ? 'selected':'' ?>>
                                                Date
                                            </option>
                                            <option value="amount" <?= $sortBy=='amount' ? 'selected':'' ?>>
                                                Montant
                                            </option>
                                            <option value="donor_name" <?= $sortBy=='donor_name' ? 'selected':'' ?>>
                                                Donateur
                                            </option>
                                        </select>

                                        <select name="order">
                                            <option value="DESC" <?= $order=='DESC' ? 'selected':'' ?>>Décroissant</option>
                                            <option value="ASC"  <?= $order=='ASC'  ? 'selected':'' ?>>Croissant</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Appliquer les filtres
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='list.php'">
                                    <i class="fas fa-undo"></i> Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des dons -->
                    <div class="table-responsive">
                        <table class="donations-table">
                            <thead>
                                <tr>
                                    <th class="col-donor">Infos du Donateur</th>
                                    <th class="col-amount">Montant</th>
                                    <th class="col-method">Méthode de paiement</th>
                                    <th class="col-status">Statut</th>
                                    <th class="col-message">Message</th>
                                    <th class="col-date">Date de Création</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donations)): ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>Aucun don trouvé</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($donations as $d) : ?>
                                        <tr>
                                            <td class="col-donor">
                                                <div class="donor-info">
                                                    <div class="donor-name"><?= htmlspecialchars($d['donor_name']) ?></div>
                                                    <div class="donor-meta">
                                                        <span class="donor-email"><?= htmlspecialchars($d['donor_email']) ?></span>
                                                        <?php if (!empty($d['donor_phone'])): ?>
                                                            <span class="separator">•</span>
                                                            <span class="donor-phone"><?= htmlspecialchars($d['donor_phone']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if ($d['is_anonymous']): ?>
                                                    <span class="badge anonymous">Anonyme</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="col-amount">
                                                <div class="amount-value"><?= number_format($d['amount'], 0, ',', ' ') ?></div>
                                                <div class="currency"><?= htmlspecialchars($d['currency']) ?></div>
                                            </td>

                                            <td class="col-method">
                                                <div class="payment-method"><?= htmlspecialchars($d['payment_method']) ?></div>
                                                <?php if (!empty($d['transaction_id'])): ?>
                                                    <div class="transaction-id">#<?= htmlspecialchars($d['transaction_id']) ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="col-status">
                                                <span class="status-badge status-<?= strtolower($d['status']) ?>">
                                                    <?= ucfirst($d['status']) ?>
                                                </span>
                                            </td>

                                            <td class="col-message"><?= htmlspecialchars($d['message']) ?></td>
                                        
                                            <?php
                                                $query = "SELECT username FROM users WHERE id_user = :id_user";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->execute(['id_user' => $d['created_by']]);
                                                $username = $stmt->fetchColumn();
                                            ?>
                                            <td class="col-date">
                                                <div class="date"><?= htmlspecialchars(formatDate($d['created_at'])) ?></div>
                                                <div class="created-by">Par <?= htmlspecialchars($username) ?></div>
                                            </td>

                                            <td class="col-actions">
                                                <div class="action-buttons">
                                                    <a href="update.php?id=<?= $d['id_donation'] ?>" class="btn-action btn-edit" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="confirmDelete(<?= $d['id_donation'] ?>)" class="btn-action btn-delete" title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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
                        <div class="pagination-wrapper">
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="<?= buildPaginationUrl(1) ?>" class="page-btn first" title="Première page">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    <a href="<?= buildPaginationUrl($page - 1) ?>" class="page-btn prev" title="Page précédente">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                <?php endif; ?>

                                <div class="page-numbers">
                                    <?php 
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    
                                    if ($start > 1): ?>
                                        <span class="ellipsis">...</span>
                                    <?php endif;
                                    
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <a href="<?= buildPaginationUrl($i) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor;
                                    
                                    if ($end < $totalPages): ?>
                                        <span class="ellipsis">...</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($page < $totalPages): ?>
                                    <a href="<?= buildPaginationUrl($page + 1) ?>" class="page-btn next" title="Page suivante">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <a href="<?= buildPaginationUrl($totalPages) ?>" class="page-btn last" title="Dernière page">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>

        <script>
            // Toggle des filtres
            document.getElementById('toggleFilters').addEventListener('click', function() {
                const panel = document.getElementById('filtersPanel');
                panel.classList.toggle('expanded');
                
                const icon = this.querySelector('i');
                if (panel.classList.contains('expanded')) {
                    icon.classList.replace('fa-sliders-h', 'fa-times');
                    this.innerHTML = '<i class="fas fa-times"></i> Fermer les filtres';
                    // Scroll doux vers les filtres
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    icon.classList.replace('fa-times', 'fa-sliders-h');
                    this.innerHTML = '<i class="fas fa-sliders-h"></i> Filtres';
                }
            });

            // Fermer les filtres si on clique en dehors
            document.addEventListener('click', function(e) {
                const panel = document.getElementById('filtersPanel');
                const btn = document.getElementById('toggleFilters');
                
                if (panel.classList.contains('expanded') && 
                    !panel.contains(e.target) && 
                    e.target !== btn && 
                    !btn.contains(e.target)) {
                    panel.classList.remove('expanded');
                    const icon = btn.querySelector('i');
                    icon.classList.replace('fa-times', 'fa-sliders-h');
                    btn.innerHTML = '<i class="fas fa-sliders-h"></i> Filtres';
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(donationId) {
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
                            text: "Redirection vers la page d'affichage des dons.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + donationId;
                        });
                    }
                });
            }
        </script>

        <!-- Script pour la confirmation de déconnexion -->
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