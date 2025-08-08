<?php
    /*Configuration de la base données*/

    define("DB_HOST", "localhost");
    define("DB_NAME", "afhe_db");
    define("DB_USER", "root"); 
    define("DB_PASSWORD", "");

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die('Erreur de connexion (' . $conn->connect_errno . ') ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    //Reconnecter à la base de données en cas de perte de connexion
    function reconnectDB() {
        global $pdo;
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true
            ]);
        } catch (PDOException $e) {
            error_log("Échec de la reconnexion à MySQL : " . $e->getMessage());
            die("Erreur critique, veuillez réessayer plus tard.");
        }
    }
    
?>