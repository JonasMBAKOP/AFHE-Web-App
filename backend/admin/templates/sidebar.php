<?php
// Définir les éléments du menu avec leurs sous-menus
$menu_items = [
    [
        'id' => 'dashboard',
        'title' => 'Tableau de bord',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '../index.php',
        'submenu' => []
    ],
    [
        'id' => 'activities',
        'title' => 'Activités',
        'icon' => 'fas fa-calendar-alt',
        'url' => '#',
        'submenu' => [
            ['id' => 'activities_list', 'title' => 'Liste des Activités', 'url' => '../activities/list.php'],
            ['id' => 'create_activity', 'title' => 'Créer une activité', 'url' => '../activities/add.php'],
            ['id' => 'categories_list', 'title' => 'Liste des catégories', 'url' => '../activities/listCategories.php'],
            ['id' => 'create_category', 'title' => 'Créer une catégorie', 'url' => '../activities/create_category.php']
        ]
    ],
    [
        'id' => 'projects',
        'title' => 'Projets',
        'icon' => 'fas fa-project-diagram',
        'url' => '#',
        'submenu' => [
            ['id' => 'projects_list', 'title' => 'Liste des Projets', 'url' => '../projects/list.php'],
            ['id' => 'create_project', 'title' => 'Créer un Projet', 'url' => '../projects/add.php']
        ]
    ],
    [
        'id' => 'testimonials',
        'title' => 'Témoignages',
        'icon' => 'fas fa-comments',
        'url' => '#',
        'submenu' => [
            ['id' => 'testimonials_list', 'title' => 'Liste des Témoignages', 'url' => '../testimonials/testimonials.php'],
            ['id' => 'create_testimonial', 'title' => 'Créer un Témoignage', 'url' => '../testimonials/add.php']
        ]
    ],
    [
        'id' => 'admin_contacts',
        'title' => 'Messages reçus',
        'icon' => 'fas fa-envelope',
        'url' => '../contacts/admin_contacts.php',
        'submenu' => []
    ],
    [
        'id' => 'donations',
        'title' => 'Dons',
        'icon' => 'fas fa-donate',
        'url' => '#',
        'submenu' => [
            ['id' => 'donations_list', 'title' => 'Liste des Dons', 'url' => '../donations/list.php'],
            ['id' => 'create_donation', 'title' => 'Créer un Don', 'url' => '../donations/add.php']
        ]
    ],
    [
        'id' => 'visits',
        'title' => 'Rapport de visites',
        'icon' => 'fas fa-chart-line',
        'url' => '../reports/visits.php',
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
            ['id' => 'administrators_list', 'title' => 'Liste des Administrateurs', 'url' => '../users/list.php'],
            ['id'=> 'create_administrator', 'title' => 'Créer un Administrateur', 'url' => '../users/add.php']
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

<div class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <a href="../index.php">
                <img src="../assets/images/logo.png" alt="Logo AFHE">
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