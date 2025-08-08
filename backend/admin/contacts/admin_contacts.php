<?php
    //Page pour afficher la liste des contacts et gérer les actions (suppression, etc.)
    
    require_once "../../includes/auth_guard.php"; // Vérification des droits d'accès
    require_once "../../includes/db_connect.php"; // Connexion à la base
    require_once "../../models/ContactModel.php"; // Modèle contact

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'admin_contacts';

    $cm = new ContactModel();
    
    // Récupération des paramètres GET
    $sortBy        = $_GET['sortBy']  ?? 'date_sent';
    $order         = $_GET['order']   ?? 'DESC';
    $subjectFilter = $_GET['subject'] ?? 'all';
    $page          = max(1, intval($_GET['page'] ?? 1));

    // Pagination
    $perPage     = 3;
    $offset      = ($page - 1) * $perPage;
    $totalItems  = $cm->countContacts($subjectFilter);
    $totalPages  = (int)ceil($totalItems / $perPage);

    $contacts = $cm->getContacts($sortBy, $order, $subjectFilter, $perPage, $offset);   // Récupération des données paginées

    $subjects = $cm->getDistinctSubjects();     // Pour le filtre par sujet
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Messages Reçus | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/contacts.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="contact-list-content">
                    <div class="contacts-header">
                        <div class="header-content">
                            <h1><i class="fas fa-envelope-open-text"></i> Messages Reçus</h1>
                            <p>Gestion des messages envoyés via le formulaire de contact</p>
                        </div>
                        <div class="stats-badge">
                            <span class="count"><?= $totalItems ?></span>
                            <span class="label">Messages</span>
                        </div>
                    </div>

                    <!-- Filtres & Tri -->
                    <div class="contacts-filters-card">
                        <form method="GET" class="contacts-filters">
                            <div class="filter-group">
                                <div class="input-with-icon">
                                    <i class="fas fa-filter"></i>
                                    <select name="subject" class="styled-select">
                                        <option value="all">Tous les objets</option>
                                        <?php foreach ($subjects as $sub): ?>
                                            <option value="<?= htmlspecialchars($sub, ENT_QUOTES) ?>"
                                                <?= $subjectFilter===$sub ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sub, ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="filter-group">
                                <div class="input-with-icon">
                                    <i class="fas fa-sort"></i>
                                    <select name="sortBy" class="styled-select">
                                        <option value="date_sent" <?= $sortBy==='date_sent' ? 'selected' : '' ?>>Date d'envoi</option>
                                        <option value="name" <?= $sortBy==='name' ? 'selected' : '' ?>>Nom</option>
                                        <option value="email" <?= $sortBy==='email' ? 'selected' : '' ?>>Email</option>
                                    </select>
                                </div>

                                <div class="input-with-icon">
                                    <i class="fas fa-sort-amount-down"></i>
                                    <select name="order" class="styled-select">
                                        <option value="DESC" <?= $order==='DESC' ? 'selected' : '' ?>>Décroissant</option>
                                        <option value="ASC" <?= $order==='ASC' ? 'selected' : '' ?>>Croissant</option>
                                    </select>
                                </div>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="btn btn-filter">
                                    <i class="fas fa-check"></i> Appliquer
                                </button>
                                <a href="admin_contacts.php" class="btn btn-clear">
                                    <i class="fas fa-undo"></i> Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des messages -->
                    <div class="messages-container">
                        <?php if (empty($contacts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>Aucun message trouvé</h3>
                                <p>Aucun message ne correspond à vos critères de recherche</p>
                            </div>
                        <?php else: ?>
                            <div class="messages-list">
                                <?php foreach ($contacts as $c): ?>
                                    <div class="message-card">
                                        <div class="message-header">
                                            <div class="sender-info">
                                                <div class="sender-name"><?= htmlspecialchars($c['name'], ENT_QUOTES) ?></div>
                                                <div class="sender-email"><?= htmlspecialchars($c['email'], ENT_QUOTES) ?></div>
                                            </div>
                                            <div class="message-meta">
                                                <span class="message-date"><?= htmlspecialchars(formatDate($c['date_sent']), ENT_QUOTES) ?></span>
                                                <span class="message-subject"><?= htmlspecialchars($c['subject'], ENT_QUOTES) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="message-content">
                                            <p><?= htmlspecialchars($c['message'], ENT_QUOTES) ?></p>
                                        </div>
                                        
                                        <div class="message-footer">
                                            <div class="sender-phone">
                                                <i class="fas fa-phone"></i> <?= htmlspecialchars($c['phone'], ENT_QUOTES) ?>
                                            </div>
                                            <div class="message-actions">
                                                <button onclick="confirmDelete(<?= $c['id_contact'] ?>)" class="btn-action delete">
                                                    <i class="fas fa-trash-alt"></i> Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-container">
                            <nav class="pagination">
                                <?php
                                    $params = $_GET;
                                    // Bouton Précédent
                                    if ($page > 1):
                                        $params['page'] = $page - 1;
                                        $url = 'admin_contacts.php?'.http_build_query($params);
                                ?>
                                    <a href="<?= $url ?>" class="page-nav prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php 
                                    // Affichage des pages
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    
                                    if ($start > 1): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif;
                                    
                                    for ($p = $start; $p <= $end; $p++):
                                        $params['page'] = $p;
                                        $url = 'admin_contacts.php?'.http_build_query($params);
                                ?>
                                    <a href="<?= $url ?>" class="<?= $p === $page ? 'current' : '' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php endfor;
                                    
                                    if ($end < $totalPages): ?>
                                    <span class="ellipsis">...</span>
                                <?php endif; ?>

                                <?php 
                                    // Bouton Suivant
                                    if ($page < $totalPages):
                                        $params['page'] = $page + 1;
                                        $url = 'admin_contacts.php?'.http_build_query($params);
                                ?>
                                    <a href="<?= $url ?>" class="page-nav next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>

        
        <script src="../assets/js/admin_script.js"></script>

        <script>
            // Gestion de l'affichage des messages
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle l'expansion des messages
                document.querySelectorAll('.message-header').forEach(header => {
                    header.addEventListener('click', function() {
                        this.parentElement.classList.toggle('expanded');
                    });
                });
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(contactId) {
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
                            text: "Redirection vers la page d'affichage des messages.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + contactId;
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