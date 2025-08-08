<?php
    //Connexion à la base de données

    require_once __DIR__ . '/../config/db_config.php';
    require_once __DIR__ . '/../includes/functions.php';
    
    if (!isset($pdo)){
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Désactive l'émulation des requêtes préparées pour éviter les injections SQL
                PDO::ATTR_PERSISTENT => true // Active la connexion persistante pour éviter les ouvertures répétées
            ]);

            // Activation du mode strict pour éviter les erreurs SQL silencieuses
            $pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");

        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage()); //stocke l'erreur dans les logs
            die("Connexion impossible. Veuillez réessayer plus tard.");
        }
    }

    // Vérification contre une éventuelle attaque DNS Spoofing
    $resolved_ip = gethostbyname(DB_HOST);
    if ($resolved_ip !== DB_HOST && $resolved_ip !== "127.0.0.1") {
        error_log("Possible attaque DNS ! Adresse IP incorrecte.");
        die("Erreur critique. Contactez l’administrateur.");
    }

    // Tentative de reconnexion si nécessaire
    if (!$pdo) {
        reconnectDB();  // Fonction définie dans db_config.php
    }
    
    //Vérification que la connexion est bien établie
    if (!$pdo) {
        error_log("Problème de connexion à la base de données.");
        die("Le service est temporairement indisponible.");
    }

?>