<?php
    //Ajouter un administrateur

    define("SUPER_ADMIN_ONLY", true);
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base
    require_once "../../models/UserModel.php"; // Modèle utilisateur

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'create_administrator';

    // Vérifier si le formulaire est soumis
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = sanitizeInput($_POST["username"] ?? "");
        $email = sanitizeInput($_POST["email"] ?? "");
        $full_name = sanitizeInput($_POST["full_name"] ?? "");
        $password = $_POST["password"] ?? "";
        $role = ($_POST["role"] === "super_admin") ? "super_admin" : "admin"; // Sélectionner admin ou super_admin

        // Vérifier les champs vides
        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            die("Erreur : Tous les champs sont obligatoires.");
        }

        // Vérifier la validité de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Erreur : Format d'email invalide.");
        }

        $userModel = new UserModel($pdo); // Instancier le modèle utilisateur
        if ($userModel->getUserByEmail($email)) {
            die("Erreur : Cet email est déjà enregistré.");
        }

        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insérer l'utilisateur
        $userModel->createUser($username, $email, $hashed_password, $full_name, $role); // Utiliser le modèle pour créer l'utilisateur
        
        header("Location: add.php?success=1&username=" . urlencode($username) . "&role=" . urlencode($role));
        exit;
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ajouter un Administrateur | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/users.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-admin-content">
                    <div class="admin-form-container">
                        <h1><i class="fas fa-user-plus"></i> Nouvel Administrateur</h1>
                        
                        <form class="admin-form" action="add.php" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name"><i class="fas fa-user"></i> Nom complet :</label>
                                    <input type="text" id="full_name" name="full_name" required placeholder="Prénom et Nom">
                                </div>
                                
                                <div class="form-group">
                                    <label for="username"><i class="fas fa-at"></i> Nom d'utilisateur :</label>
                                    <input type="text" id="username" name="username" required placeholder="john.doe">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email :</label>
                                    <input type="email" id="email" name="email" required placeholder="exemple@email.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password"><i class="fas fa-lock"></i> Mot de passe :</label>
                                    <div class="password-container">
                                        <input type="password" id="password" name="password" required>
                                        <i id="togglePassword" class="fa fa-eye"></i>
                                    </div>
                                    <p class="password-hint">Minimum 8 caractères</p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="role"><i class="fas fa-shield-alt"></i> Rôle :</label>
                                <select id="role" name="role">
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin" selected>Admin</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" id="submitBtn" class="btn-primary"><i class="fas fa-save"></i> Créer l'utilisateur</button>
                                <button type="button" class="btn-secondary" onclick="window.location.href='list.php'"><i class="fas fa-times"></i>Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>
        <script src="../assets/js/users.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const form = document.querySelector("form");
                const submitBtn = document.getElementById("submitBtn");

                // Confirmation avant soumission
                if (form && submitBtn) {
                    form.addEventListener("submit", function (e) {
                        e.preventDefault(); // Ne pas soumettre immédiatement

                        Swal.fire({
                            title: "Confirmer la création ?",
                            text: "Voulez-vous vraiment créer cet administrateur ?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "Oui, créer",
                            cancelButtonText: "Annuler",
                            confirmButtonColor: "#28a745",
                            cancelButtonColor: "#dc3545"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit(); // Soumettre le formulaire
                            }
                        });
                    });
                }

                // SweetAlert de succès après redirection
                const urlParams = new URLSearchParams(window.location.search);
                const success = urlParams.get('success');
                const username = urlParams.get('username');
                const role = urlParams.get('role');

                if (success === '1' && username && role) {
                    Swal.fire({
                        title: "Succès ✅",
                        text: `L'utilisateur "${decodeURIComponent(username)}" a été ajouté avec le rôle "${decodeURIComponent(role)}".`,
                        icon: "success",
                        confirmButtonColor: "#1E90FF"
                    }).then(() => {
                        // Nettoyer l'URL après l'affichage
                        const cleanUrl = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, cleanUrl);
                    });
                }
            });
        </script>

        <!-- Script pour la confirmation de déconnexion -->
        <script>
            document.getElementById("logoutButton").addEventListener("click", function() {
                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Vous allez être déconnecté.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, me déconnecter",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Déconnexion en cours...",
                            text: "Redirection vers la page de connexion.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "../logout.php";
                        });
                    }
                });
            });
        </script>
    </body>
</html>