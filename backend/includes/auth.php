<?php
    //Gestion de l'authentification

    require_once "db_connect.php"; // Connexion à la base
    require_once "session.php"; // Gestion des sessions
    require_once "functions.php";     //Inclure les fonctions utilitaires
    require_once "../models/UserModel.php"; // Inclure le modèle utilisateur

    if (estConnecte()) {
        redirect("../admin/index.php"); //Redirige vers la page dashboard.php si l'utilisateur est déjà connecté
    }

    // Définir le nombre maximum de tentatives
    define("MAX_LOGIN_ATTEMPTS", 3);
    define("LOCKOUT_TIME", 60); // 1 minutes de blocage après 5 essais

    // Vérifier si l'utilisateur est temporairement bloqué
    if (isset($_SESSION["login_attempts"]) && $_SESSION["login_attempts"] >= MAX_LOGIN_ATTEMPTS) {
        $remaining_lockout = LOCKOUT_TIME - (time() - $_SESSION["last_attempt_time"]);
        if ($remaining_lockout > 0) {
            die("Session temporairement bloquée. Réessayez dans {$remaining_lockout} secondes.");
        } else {
            unset($_SESSION["login_attempts"]);
            unset($_SESSION["last_attempt_time"]);
        }
    }

    // Vérifier si une requête POST est envoyée
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        //Vérification du token CSRF avant toute autre validation
        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            die("Erreur : Requête non valide.");
        }

        // Récupérer les données envoyées
        $email = sanitizeInput($_POST["email"] ?? "");
        $password = sanitizeInput($_POST["password"] ?? "");

        // Vérifier les champs vides
        if (empty($email) || empty($password)) {
            die("Erreur : Email et mot de passe obligatoires.");
        }

        // Vérifier la validité de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Erreur : Format d'email invalide.");
        }

        // Requête sécurisée pour récupérer l'utilisateur
        // $query = "SELECT id_user, username, password, email, full_name, role, profile_image, active
        //           FROM users
        //           WHERE email = :email";
        // $stmt = $pdo->prepare($query);
        // $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        // $stmt->execute();
        // $user = $stmt->fetch();
        $userModel = new UserModel($pdo); // Instancier le modèle utilisateur
        $user = $userModel->getUserByEmail($email); // Utiliser le modèle pour récupérer l'utilisateur

        // Vérification des identifiants et du statut actif
        if ($user && password_verify($password, $user["password"])) {
            if ($user["active"] != 1) {
                die("Erreur : Votre compte est inactif !! Veuillez contacter l'administrateur !!");
            }

            // Réinitialiser les tentatives de connexion après un succès
            unset($_SESSION["login_attempts"]);
            unset($_SESSION["last_attempt_time"]);

            // Création d'une session sécurisée
            $_SESSION["id_user"] = $user["id_user"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["profile_image"] = $user["profile_image"];
            $_SESSION["session_token"] = hash('sha256', $_SERVER['REMOTE_ADDR'] . uniqid('', true));

            redirect("../admin/index.php"); // Redirection après connexion réussie
        } else {
            // Enregistrer l'échec de connexion
            $_SESSION["login_attempts"] = ($_SESSION["login_attempts"] ?? 0) + 1;
            $_SESSION["last_attempt_time"] = time();

            // Vérifier si l’utilisateur atteint la limite de tentatives
            if ($_SESSION["login_attempts"] >= MAX_LOGIN_ATTEMPTS) {
                die("Erreur : Trop de tentatives échouées. Session temporairement bloquée pour " . (LOCKOUT_TIME / 60) . " minutes.");
            }

            die("Erreur : Email ou mot de passe incorrect.");
        }
    }

?>