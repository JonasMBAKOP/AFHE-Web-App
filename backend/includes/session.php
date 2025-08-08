<?php
    //Gestion des sessions

    // require_once "init.php";
    require_once "functions.php"; // Inclure les fonctions utilitaires

    define("SESSION_TIMEOUT", 600); // 10 minutes (600 secondes)

    // Sécurisation des sessions
    ini_set('session.cookie_httponly', 1); // Empêche JavaScript d’accéder aux cookies
    ini_set('session.cookie_secure', 1); // Active HTTPS (utile en production)
    ini_set('session.use_only_cookies', 1); // Empêche l'utilisation de sessions via URL (sessions sécurisées)

    session_name("AFHE_SESSION_" . session_id()); // Définit un nom de session unique par utilisateur pour les sessions multiples

    //Sécurisation renforcée des sessions avec SameSite
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'  //Protection contre les attaques CSRF
    ]);

    session_start(); // Démarrer la session
    session_regenerate_id(true); // Renouvelle l’ID de session à chaque action

    // Gérer l'expiration de la session après 10 minutes d'inactivité
    function verifierExpirationSession() {
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            $_SESSION['LAST_ACTIVITY'] = time();
        }    
        $temps_restant = SESSION_TIMEOUT - (time() - $_SESSION['LAST_ACTIVITY']);

        if ($temps_restant <= 120) { // Avertir l'utilisateur 2 minutes avant expiration (120 secondes)
            $_SESSION['EXPIRE_WARNING'] = "Votre session expire dans $temps_restant secondes.";
            echo $_SESSION['EXPIRE_WARNING'];
        }
        if ($temps_restant <= 0) {
            deconnecter(); // Déconnexion après expiration
        }

        $_SESSION['LAST_ACTIVITY'] = time(); // Met à jour l'activité utilisateur
    }

    // Sécurité supplémentaire : Vérification du token de session
    if (!isset($_SESSION["session_token"])) {
        $_SESSION["session_token"] = hash('sha256', $_SERVER['REMOTE_ADDR'] . uniqid('', true));
    }
    // if ($_SESSION["session_token"] !== hash('sha256', $_SERVER['REMOTE_ADDR'] . uniqid('', true))) {
    //     deconnecter(); // Déconnexion forcée si le token ne correspond pas
    // }

    //Protection CSRF : Génération d'un token CSRF
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = generateToken();
    }
?>