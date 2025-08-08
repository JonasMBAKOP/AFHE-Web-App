<?php
    //Script de déconnexion Admin

    // require_once "../includes/auth_guard.php";
    require_once "../includes/session.php"; // Inclure la gestion des sessions
    require_once "../includes/functions.php"; // Inclure les fonctions utilitaires

    // Vérifier si l'utilisateur est bien connecté
    if (!estConnecte()) {
        redirect("login.php"); //Redirige vers la page de connexion login.php si l'utilisateur n'est pas connecté
    }

    // Déconnexion de l'utilisateur
    $_SESSION = []; // Vide les données de session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit complètement la session
    setcookie(session_name(), '', time() - 600, '/'); // Supprime le cookie de session
    redirect("login.php"); // Redirection vers login.php