<?php
    //vérification de session active

    require_once "session.php";     //Inclure la gestion des sessions
    require_once "functions.php";     //Inclure les fonctions utilitaires

    // Empêcher l'accès direct au fichier via URL
    if (basename($_SERVER["SCRIPT_FILENAME"]) === "auth_guard.php") {
        die("Accès interdit.");
    }

    // Vérifier si l'utilisateur est bien connecté
    if (!estConnecte()) {
        redirect("../login.php"); //Redirige vers la page de connexion login.php si l'utilisateur n'est pas connecté
    }

    // Vérifier si l'accès nécessite un super admin
    if (defined("SUPER_ADMIN_ONLY") && SUPER_ADMIN_ONLY && !estSuperAdmin()) {
        die("Accès réservé aux super admins.");
    }

?>