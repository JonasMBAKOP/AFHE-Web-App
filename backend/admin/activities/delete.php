<?php
    //Script de suppression d'une activité

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $model = new ActivityModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID Activité manquant.");
    }

    // Vérifier si l'activité existe
    $act = $model->getActivityById($_GET["id"]);
    if (!$act) {
        die("Erreur : Activité introuvable.");
    }

    // Supprimer l'activité
    $model->deleteActivity($_GET["id"]);

    redirect ("list.php");
?>