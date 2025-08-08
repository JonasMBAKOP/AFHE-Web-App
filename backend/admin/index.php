<?php
    //Tableau de bord

    require_once "../includes/auth_guard.php";     //Inclure la gestion des accès
    require_once "../includes/db_connect.php";
    require_once "../models/ActivityModel.php";
    require_once "../models/UserModel.php";
    require_once "../models/TestimonialModel.php";
    require_once "../models/ProjectModel.php";
    require_once "../models/ContactModel.php";
    require_once "../models/DonationModel.php";
    require_once "../models/ReportModel.php";

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'dashboard';

    $am = new ActivityModel();
    $totalActivities = $am->countAllActivities();

    $pm = new ProjectModel();
    $totalProjects = $pm->countAllProjects();
    
    $tm = new TestimonialModel();
    $totalTestimonials = $tm->countAllTestimonials();

    $dm = new DonationModel();
    $totalDonations = $dm->countAllDonations();

    $cm = new ContactModel();
    $totalContacts = $cm->countAllContacts();

    $um = new UserModel();
    $totalUsers = $um->countAllUsers();
    
    $vm = new ReportModel();
    $totalVisits = $vm->countAllVisits();
    $totalVisitsCount = $vm->countTotalVisitCount();
    $totalUniqueVisitors = $vm->countTotalUniqueVisitors();

    // Nécessaires pour la sidebar du dasboard
    // Définir les éléments du menu avec leurs sous-menus
    $menu_items = [
        [
            'id' => 'dashboard',
            'title' => 'Tableau de bord',
            'icon' => 'fas fa-tachometer-alt',
            'url' => 'index.php',
            'submenu' => []
        ],
        [
            'id' => 'activities',
            'title' => 'Activités',
            'icon' => 'fas fa-calendar-alt',
            'url' => '#',
            'submenu' => [
                ['id' => 'activities_list', 'title' => 'Liste des Activités', 'url' => 'activities/list.php'],
                ['id' => 'create_activity', 'title' => 'Créer une activité', 'url' => 'activities/add.php'],
                ['id' => 'categories_list', 'title' => 'Liste des catégories', 'url' => 'activities/listCategories.php'],
                ['id' => 'create_category', 'title' => 'Créer une catégorie', 'url' => 'activities/create_category.php']
            ]
        ],
        [
            'id' => 'projects',
            'title' => 'Projets',
            'icon' => 'fas fa-project-diagram',
            'url' => '#',
            'submenu' => [
                ['id' => 'projects_list', 'title' => 'Liste des Projets', 'url' => 'projects/list.php'],
                ['id' => 'create_project', 'title' => 'Créer un Projet', 'url' => 'projects/add.php']
            ]
        ],
        [
            'id' => 'testimonials',
            'title' => 'Témoignages',
            'icon' => 'fas fa-comments',
            'url' => '#',
            'submenu' => [
                ['id' => 'testimonials_list', 'title' => 'Liste des Témoignages', 'url' => 'testimonials/testimonials.php'],
                ['id' => 'create_testimonial', 'title' => 'Créer un Témoignage', 'url' => 'testimonials/add.php']
            ]
        ],
        [
            'id' => 'admin_contacts',
            'title' => 'Messages reçus',
            'icon' => 'fas fa-envelope',
            'url' => 'contacts/admin_contacts.php',
            'submenu' => []
        ],
        [
            'id' => 'donations',
            'title' => 'Dons',
            'icon' => 'fas fa-donate',
            'url' => '#',
            'submenu' => [
                ['id' => 'donations_list', 'title' => 'Liste des Dons', 'url' => 'donations/list.php'],
                ['id' => 'create_donation', 'title' => 'Créer un Don', 'url' => 'donations/add.php']
            ]
        ],
        [
            'id' => 'visits',
            'title' => 'Rapport de visites',
            'icon' => 'fas fa-chart-line',
            'url' => 'reports/visits.php',
            'submenu' => []
        ]
    ];

    if (estSuperAdmin()) {
        $menu_items[] = [
            'id' => 'administrators',
            'title' => 'Administrateurs',
            'icon' => 'fas fa-users-cog',
            'url' => '#',
            'submenu' => [
                ['id' => 'administrators_list', 'title' => 'Liste des Administrateurs', 'url' => 'users/list.php'],
                ['id'=> 'create_administrator', 'title' => 'Créer un Administrateur', 'url' => 'users/add.php']
            ]
        ];
    }

    // Fonction pour vérifier si un élément est actif
    function isActiveMenuItem($menu_id, $current_page) {
        return $menu_id === $current_page;
    }

    // Fonction pour vérifier si un sous-menu contient la page active
    function hasActiveSubmenu($submenu, $current_page) {
        foreach ($submenu as $item) {
            $filename = basename($item['id']);
            if ($filename === $current_page) {
                return true;
            }
        }
        return false;
    }

    // Fonction pour vérifier si un lien de sous-menu est actif
    function isActiveSubmenuLink($submenu_id, $current_page) {
        $filename = basename($submenu_id);
        return $filename === $current_page . '.php' || $filename === $current_page;
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tableau de Bord – AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="assets/css/templates.css">
        <link rel="stylesheet" href="assets/css/admin_styles.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php //require_once "templates/sidebar.php"; // Inclure la barre latérale ?>

        
            <!-- Barre Latérale -->
            <div class="sidebar" id="sidebar">
                <!-- Sidebar Header -->
                <div class="sidebar-header">
                    <div class="sidebar-logo">
                        <a href="index.php">
                            <img src="assets/images/logo.png" alt="Logo AFHE">
                        </a>
                    </div>
                    <div class="sidebar-title">
                        <h3>AFHE</h3>
                        <p>Admin Panel</p>
                    </div>
                    <button class="hamburger-btn" id="hamburgerBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <!-- Sidebar Menu -->
                <nav class="sidebar-menu">
                    <?php foreach ($menu_items as $item): ?>
                        <?php 
                            $is_active = isActiveMenuItem($item['id'], $current_page);
                            $has_active_submenu = hasActiveSubmenu($item['submenu'], $current_page);
                            $should_expand = $has_active_submenu;
                        ?>
                        
                        <div class="menu-item">
                            <?php if (empty($item['submenu'])): ?>
                                <!-- Menu item without submenu -->
                                <a href="<?= $item['url'] ?>" class="menu-link <?= $is_active ? 'active' : '' ?>">
                                    <i class="menu-icon <?= $item['icon'] ?>"></i>
                                    <span class="menu-text"><?= $item['title'] ?></span>
                                </a>
                            <?php else: ?>
                                <!-- Menu item with submenu -->
                                <a href="<?= $item['url'] ?>" class="menu-link <?= $is_active || $has_active_submenu ? 'active' : '' ?>" 
                                data-submenu="<?= $item['id'] ?>">
                                    <i class="menu-icon <?= $item['icon'] ?>"></i>
                                    <span class="menu-text"><?= $item['title'] ?></span>
                                    <i class="menu-arrow fas fa-chevron-down <?= $should_expand ? 'rotated' : '' ?>"></i>
                                </a>
                                
                                <!-- Submenu -->
                                <div class="submenu <?= $should_expand ? 'open' : '' ?>" id="submenu-<?= $item['id'] ?>">
                                    <?php foreach ($item['submenu'] as $submenu_item): ?>
                                        <?php $is_submenu_active = isActiveSubmenuLink($submenu_item['id'], $current_page); ?>
                                        <a href="<?= $submenu_item['url'] ?>" class="submenu-link <?= $is_submenu_active ? 'active' : '' ?>">
                                            <i class="fas fa-circle" style="font-size: 6px; margin-right: 10px;"></i>
                                            <?= $submenu_item['title'] ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Logout Button -->
                    <div class="menu-item">
                        <a href="#" id="logoutButton" class="menu-link">
                            <i class="menu-icon fas fa-sign-out-alt"></i>
                            <span class="menu-text">Déconnexion</span>
                        </a>
                    </div>
                </nav>
            </div>


            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "templates/header.php"; // Inclure l'entête du back office ?>

                <div class="dashboard-content">
                    <!-- <h1>Bienvenue sur le tableau de bord ! </h1> -->

                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalActivities ?></h3>
                                <p>Activités</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalProjects ?></h3>
                                <p>Projets</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalTestimonials ?></h3>
                                <p>Témoignages</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalDonations ?></h3>
                                <p>Dons</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalContacts ?></h3>
                                <p>Messages</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $totalUsers ?></h3>
                                <p>Administrateurs</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="dashboard-sections">
                        <!-- <div class="section">
                            <h2><i class="fas fa-clock"></i> Activités Récentes</h2>
                            <div class="activity-list">
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong>Nouvelle activité créée</strong></p>
                                        <small>Formation en développement web - Il y a 2 heures</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong>Projet mis à jour</strong></p>
                                        <small>Système de gestion scolaire - Il y a 5 heures</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong>Nouveau témoignage</strong></p>
                                        <small>Témoignage de satisfaction - Il y a 1 jour</small>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        
                        <div class="section">
                            <h2><i class="fas fa-chart-line"></i> Statistiques des Visites</h2>
                            <div class="stats-grid">
                                <!-- <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?= $totalVisits ?></h3>
                                        <p>Visites</p>
                                    </div>
                                </div> -->

                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?= $totalVisitsCount ?></h3>
                                        <p>Nombre total de Visites</p>
                                    </div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?= $totalUniqueVisitors ?></h3>
                                        <p>Visiteurs Uniques</p>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="chart-container">
                                 <form method="get">
                                    <label>
                                        Début : <input type="date" name="start" value="<?= htmlspecialchars($_GET['start'] ?? '') ?>">
                                    </label>
                                    <label>
                                        Fin   : <input type="date" name="end"   value="<?= htmlspecialchars($_GET['end'] ?? '') ?>">
                                    </label>
                                    <button type="submit">Afficher</button>
                                </form>

                                <?php
                                    // $qs = http_build_query([
                                    // 'start'=>$_GET['start'] ?? '',
                                    // 'end'=>$_GET['end'] ?? ''
                                    // ]);
                                    // if (!empty($_GET['start']) && !empty($_GET['end'])) {
                                    //     echo '<canvas id="visitsChart" width="600" height="300"></canvas>';
                                    // }
                                ?>

                            </div> -->
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                        <div class="actions-grid">
                            <a href="activities/add.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Créer une Activité</span>
                            </a>
                            <a href="projects/add.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Créer un Projet</span>
                            </a>
                            <a href="testimonials/add.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Créer un Témoignage</span>
                            </a>
                            <a href="contacts/admin_contacts.php" class="action-btn">
                                <i class="fas fa-envelope"></i>
                                <span>Voir les Messages</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <?php require_once "templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="assets/js/admin_script.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Configure la période souhaitée (tu peux aussi l’inscrire en PHP)
            const params = new URLSearchParams({
                start: '2025-08-01', // Date de début
                end:   '2025-08-08'  // Date de fin
            });

            // On cible le canvas
            const ctx = document.getElementById('visitsChart').getContext('2d');

            // On fetch l’endpoint JSON
            fetch(`visits-chart.php?${params}`)
                .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
                })
                .then(json => {
                // Création du chart
                new Chart(ctx, {
                    type: 'line',
                    data: {
                    labels: json.labels,
                    datasets: [{
                        label: 'Visites',
                        data: json.data,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                    },
                    options: {
                    scales: {
                        x: { 
                        title: { display: true, text: 'Date' }
                        },
                        y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Nombre de visites' }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                    }
                });
                })
                .catch(err => {
                console.error(err);
                ctx.font = '16px sans-serif';
                ctx.fillText('Échec de chargement des données', 10, 50);
                });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            window.location.href = "logout.php";
                        });
                    }
                });
            });
        </script>
    </body>
</html>