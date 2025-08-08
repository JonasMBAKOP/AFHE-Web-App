<?php
    //Script de suppression d'un projet

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ProjectModel.php"; // Modèle projetverifierExpirationSession(); // Vérifier si la session a expiré

    verifierExpirationSession(); // Vérifier si la session a expiré

    $projectModel = new ProjectModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID projet manquant.");
    }

    // Vérifier si le projet existe
    $project = $projectModel->getProjectById($_GET["id"]);
    if (!$project) {
        die("Erreur : Projet introuvable.");
    }

    // Supprimer le projet
    $projectModel->deleteProject($_GET["id"]);

    redirect ("list.php");
?>