<?php
    //Script de suppression d'une catégorie d'activité

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $model = new ActivityModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID Activité manquant.");
    }

    // Vérifier si la catégorie existe
    $act = $model->getCategoryById($_GET["id"]);
    if (!$act) {
        die("Erreur : Activité introuvable.");
    }

    // On compte le nbre d'activités pour cet ID
    $count = $model->countActivitiesInCategory($_GET["id"]);

    // Supprimer l'activité
    $model->deleteCategory($_GET["id"]);

    redirect ("listCategories.php");
?>