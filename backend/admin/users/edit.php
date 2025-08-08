<?php
    //Modifier un administrateur

    define("SUPER_ADMIN_ONLY", true);
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/UserModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'edit_administrator';

    $userModel = new UserModel();

    // Vérifier si l'ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID utilisateur manquant.");
    }

    $user = $userModel->getUserById($_GET["id"]);
    
    if (!$user) {
        die("Erreur : Utilisateur introuvable.");
    }

    // Traitement du formulaire si soumis
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = $_POST["id"];
        $username = htmlspecialchars($_POST["username"]);
        $email = htmlspecialchars($_POST["email"]);
        $full_name = htmlspecialchars($_POST["full_name"]);
        $role = $_POST["role"];
        $password = $_POST["password"]; // Nouveau Mot de passe (optionnel)
        $active = isset($_POST["active"]) ? intval($_POST["active"]) : 0;
        
        // Vérifier que tous les champs sont remplis
        if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
            echo "<p style='color:red;'>Erreur : Tous les champs doivent être remplis.</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p style='color:red;'>Erreur : Email invalide.</p>";
        } else {
            // Mettre à jour l'utilisateur
            $userModel->updateUser($id, $username, $email, $full_name, $role, $password, $active);    
            
            // Actualiser les données affichées après mise à jour
            $user = $userModel->getUserById($id);
        }
        redirect("list.php"); // Redirection après mise à jour
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier un Administrateur | AFHE Admin</title>
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

                <div class="edit-admin-content">
                    <div class="admin-form-edit">
                        <h1><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h1>
                    
                        <form class="form-grid" action="edit.php?id=<?php echo htmlspecialchars($_GET["id"]); ?>" method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user["id_user"]); ?>">
                            
                            <div class="form-field">
                                <label for="full_name">Nom complet</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?= htmlspecialchars($user["full_name"]) ?>" required>
                            </div>
                            
                            <div class="form-field">
                                <label for="username">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" 
                                       value="<?= htmlspecialchars($user["username"]) ?>" required>
                            </div>

                            <div class="form-field">
                                <label for="email">Adresse email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?= htmlspecialchars($user["email"]) ?>" required>
                            </div>

                            <div class="form-field">
                                <label for="password">Nouveau mot de passe</label>
                                <div class="password-container">
                                    <input type="password" id="password" name="password" 
                                           placeholder="Laissez vide pour ne pas changer">
                                    <i id="togglePassword" class="fa fa-eye"></i>
                                </div>
                                <p class="password-note">Ne remplir que pour modifier le mot de passe</p>
                            </div>
                            
                            <div class="form-field">
                                <label for="role">Rôle</label>
                                <select id="role" name="role">
                                    <option value="super_admin" <?= ($user["role"] === "super_admin") ? "selected" : "" ?>>Super Admin</option>
                                    <option value="admin" <?= ($user["role"] === "admin") ? "selected" : "" ?>>Admin</option>
                                </select>
                            </div>

                            <div class="form-field">
                                <label for="active">Statut</label>
                                <select id="active" name="active">
                                    <option value="1" <?= ($user["active"] == 1) ? "selected" : "" ?>>
                                        Actif
                                    </option>
                                    <option value="0" <?= ($user["active"] == 0) ? "selected" : "" ?>>
                                        Inactif
                                    </option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" id="submitBtn" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
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