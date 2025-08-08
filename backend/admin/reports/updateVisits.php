<?php
    session_start(); // Pour utiliser la session
    require_once __DIR__ . '/../../includes/db_connect.php'; // Connexion à la base de données
    require_once __DIR__ . '/../../models/ReportModel.php'; // Modèle de rapport

    // On définit le nom de la page. Cela peut être dynamique selon la page où ce script est appelé.
    $pageName = isset($_GET['page']) ? $_GET['page'] : 'Accueil';

    // Gestion du visiteur unique : On vérifie dans la session si ce visiteur a déjà été comptabilisé pour cette page aujourd'hui
    $sessionKey = 'visited_' . md5($pageName . date('d-m-Y')); // clé unique par page et par jour

    if (!isset($_SESSION[$sessionKey])) {
        $unique_increment = 1;   // Nouveau visiteur unique pour la journée
        $_SESSION[$sessionKey] = true;
    } else {
        $unique_increment = 0;   // Ce visiteur a déjà été comptabilisé pour cette page aujourd'hui.
    }

    // Instanciation du modèle et enregistrement de la visite
    $reportModel = new ReportModel();
    $reportModel->recordVisit($pageName, $unique_increment);
?>