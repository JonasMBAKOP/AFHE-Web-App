<?php
    //Script de suppression d'un administrateur

    define("SUPER_ADMIN_ONLY", true);
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base
    require_once "../../models/UserModel.php"; 

    verifierExpirationSession(); // Vérifier si la session a expiré

    $userModel = new UserModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID utilisateur manquant.");
    }

    // Vérifier si l'utilisateur existe
    $user = $userModel->getUserById($_GET["id"]);
    if (!$user) {
        die("Erreur : Utilisateur introuvable.");
    }

    // Supprimer l'utilisateur
    $userModel->deleteUser($_GET["id"]);

    redirect ("list.php");

?>


