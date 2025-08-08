<?php
    //Page de login Admin

    require_once "../includes/functions.php";
    require_once "../includes/db_connect.php";
    require_once "../includes/session.php"; // Gestion des sessions

    verifierExpirationSession(); // Vérifier si la session a expiré

    // Génération du token CSRF
    $csrf_token = generateToken();
    $_SESSION["csrf_token"] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion</title>
        <link rel="stylesheet" href="assets/css/login.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>
    <body>
        <div class="login-container">
            <h2>Connexion</h2>

            <?php if (isset($_SESSION["error"])): ?>
                <p class="error-message">
                    <?php echo htmlspecialchars($_SESSION["error"], ENT_QUOTES, 'UTF-8'); ?>
                    <?php unset($_SESSION["error"]); ?>
                </p>
            <?php endif; ?>

            <form action="../includes/auth.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email">

                <label for="password">Mot de passe :</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <i id="togglePassword" class="fa fa-eye"></i>
                </div>
                
                <button type="submit" class="login-button">Se connecter</button>
                <button type="button" class="cancel-button" onclick="window.location.href='../../frontend/index.php'">Annuler</button>

            </form>
        </div>

        <script src="assets/js/login.js"></script>
    </body>
</html>