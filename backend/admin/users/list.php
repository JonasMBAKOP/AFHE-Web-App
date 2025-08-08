<?php
    //Liste des administrateurs

    define("SUPER_ADMIN_ONLY", true);
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/UserModel.php"; // Modèle utilisateur
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'administrators_list';
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des Administrateurs | AFHE Admin</title>
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

                <div class="admin-list-content">  
                    <!-- Header Section -->
                    <div class="users-header">
                        <h1><i class="fas fa-users-cog"></i> Gestion des Administrateurs</h1>
                        <a href="add.php" class="add-user-btn">
                            <i class="fas fa-user-plus"></i>
                            Nouvel Administrateur
                        </a>
                    </div>

                    <!-- Users Table -->
                    <div class="users-table-container">
                        <div class="table-header">                            
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Rechercher un administrateur..." id="searchInput">
                            </div>
                        </div>

                        <?php
                            $userModel = new UserModel($pdo); // Instancier le modèle utilisateur
                            $users = $userModel->getAllUsers(); // Récupérer tous les utilisateurs

                            if (!$users) {
                                echo "Aucun utilisateur trouvé.";
                                exit;
                            }
                            else{
                                // border="1" 
                                echo '<table class="users-table">';
                                echo "<tr>
                                        <th>Nom complet</th>
                                        <th>Nom d'utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>";

                                foreach ($users as $user) {
                                    echo "<tr>";
                                    echo "<td>
                                            <div class='user-details'>
                                                <h4>" . htmlspecialchars($user["full_name"]) . "</h4>
                                            </div>
                                    </td>";
                                    echo "<td>" . htmlspecialchars($user["username"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($user["email"]) . "</td>";
                                    echo '<td><span class="role-badge">' . htmlspecialchars($user["role"]) . "</span></td>";
                                    $status = $user["active"] ? "<span style='color:green;'>Actif</span>" : "<span style='color:red;'>Inactif</span>";
                                    echo '<td><span class="status-badge ' . ($user['active'] ? 'status-active' : 'status-inactive') . '">' . $status . "</span></td>";

                                    // Ajout des icônes "Éditer" et "Supprimer"
                                    echo "<td><div class='actions-buttons'>";
                                    echo "<a href='edit.php?id=" . $user["id_user"] . "' title='Éditer' class='btn btn-edit'><i class='fas fa-edit' font-size:18px;'></i>Modifier</a> ";
                                    echo "<a href='javascript:void(0);' onclick='confirmDelete(" . $user["id_user"] . ")' title='Supprimer' class='btn btn-delete'><i class='fas fa-trash-alt' font-size:18px;'></i>Supprimer</a>";
                                    echo "</div></td>";

                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                        ?>
                    </div>
                    <br><br><br>
                </div>

                
                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>

            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>
        <script src="../assets/js/users.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(userId) {
                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Cette action est irréversible !",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Suppression en cours...",
                            text: "Redirection vers la page d'affichage des utilisateurs.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "delete.php?id=" + userId;
                        });
                    }
                });
            }
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