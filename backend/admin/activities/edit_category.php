<?php
    //Modifier une catégorie d'activité

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'edit_category';

    $model = new ActivityModel();

    // Vérifier que l'ID de la catégorie est bien passé en paramètre
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Erreur : Aucune catégorie sélectionnée.");
    }

    $id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $cat   = $model->getCategoryById($id);

    if (!$cat) {
        die("Erreur : Catégorie introuvable.");
    }

    $ok = false;
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer et valider les champs
        $data = [
            'name'          => trim($_POST['name']),
            'description'   => trim($_POST['description']),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'active'        => $_POST['active']
        ];

        if ($data['name'] === '') {
            $error = "Le nom de la catégorie est obligatoire.";
        } 
        // unicité hors cas où on n'a pas changé le nom
        elseif ($data['name'] !== $cat['name'] && $model->categoryExists($data['name'])) {
            $error = "Une autre catégorie porte déjà ce nom.";
        }
        else {
            $ok = $model->updateCategory($id, $data);
            if ($ok) {
                redirect("listCategories.php");
            }
            $error = "Erreur lors de la mise à jour.";
        }

    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier une Catégorie d’Activité | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/categories.css">
        <link rel="stylesheet" href="../assets/css/activities.css">
        <style>
            form { max-width: 600px; margin: auto; }
            label { display: block; margin-top: 10px; }
            input, textarea, select { width: 100%; padding: 8px; }
        </style>
    </head>
    <body>
        <!-- Barre Latérale -->
        <!-- <div class="sidebar">
        </div> -->

        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="edit-category-content">
                    <?php if ($error): ?>
                        <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Échec',
                                text: <?= json_encode($error) ?>,
                                confirmButtonText: 'OK'
                            });
                        </script>
                        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <div class="add-category-form">
                        <h1><i class="fas fa-edit"></i> Modifier la catégorie « <span style="color:#FF69B4;"><?= htmlspecialchars($cat['name']) ?></span> »</h1>

                        <form method="POST" action="edit_category.php?id=<?= $cat['id'] ?>">
                            <div class="form-group">
                                <label for="name">Nom de la catégorie *</label>
                                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($cat['name']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="display_order">Ordre d'affichage *</label>
                                    <select id="display_order" name="display_order">
                                        <option value="1" <?= $cat['display_order'] === 1 ? 'selected' : '' ?>>Élevé</option>
                                        <option value="2" <?= $cat['display_order'] === 2 ? 'selected' : '' ?>>Moyen</option>
                                        <option value="3" <?= $cat['display_order'] === 3 ? 'selected' : '' ?>>Bas</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="active">Statut *</label>
                                    <select id="active" name="active">
                                        <option value="1" <?= $cat['active'] === 1 ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= $cat['active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='listCategories.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        <script src="../assets/js/admin_script.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Script pour la confirmation de deconnexion -->
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