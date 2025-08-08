<?php

    $userId = getUserId(); // Récupérer l'ID de l'utilisateur connecté
    $userFullName = getUserFullName(); // Récupérer le nom complet de l'utilisateur
    $userName = getUsername(); // Récupérer le nom d'utilisateur

    // Définir les titres des pages
    $page_titles = [
        'dashboard' => 'Tableau de Bord',
        'activities_list' => 'Liste des Activités',
        'create_activity' => 'Créer une Activité',
        'edit_activity' => 'Modifier une Activité',
        'view_activity' => "Détails de l'Activité",
        'categories_list' => 'Liste des Catégories',
        'create_category' => 'Créer une Catégorie',
        'edit_category' => 'Modifier une Catégorie',
        'projects_list' => 'Liste des Projets',
        'create_project' => 'Créer un Projet',
        'edit_project' => 'Modifier un Projet',
        'view_project' => 'Détails du Projet',
        'testimonials_list' => 'Liste des Témoignages',
        'create_testimonial' => 'Créer un Témoignage',
        'update_testimonial' => 'Modifier un Témoignage',
        'view_testimonial' => 'Détails du Témoignage',
        'admin_contacts' => 'Messages Reçus',
        'donations_list' => 'Liste des Dons',
        'create_donation' => 'Créer un Don',
        'update_donation' => 'Modifier un Don',
        'visits' => 'Rapport de Visites',
        'administrators_list' => 'Liste des Administrateurs',
        'create_administrator' => 'Créer un Administrateur',
        'edit_administrator' => 'Modifier un Administrateur'
    ];

    // Obtenir le titre de la page actuelle
    $current_page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Administration';

    // Fonction pour générer les initiales à partir du nom
    function generateInitials($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return $initials;
    }
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', $userFullName)));

    // Générer les initiales pour l'avatar
    $user_initials = generateInitials($userFullName);
?>

<header class="header">
    <div class="page-title">
        <i class="fas fa-chevron-right" style="font-size: 20px; margin-right: 10px; color: var(--primary-pink);"></i>
        <?= $current_page_title ?>
    </div>
    
    <div class="user-info">
        <span class="user-name"><?= htmlspecialchars($userName) ?></span>
        <div class="user-avatar">
            <?php if (!empty($admin_photo) && file_exists($admin_photo)): ?>
                <img src="<?= htmlspecialchars($admin_photo) ?>" alt="Photo de profil">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </div>
    </div>
</header>