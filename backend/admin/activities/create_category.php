<?php
    // Créer une catégorie d’activité
    
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ActivityModel.php";
    
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'create_category';

    $model = new ActivityModel();
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer et valider les champs
        $data = [
            'name'          => trim($_POST['name']),
            'description'   => trim($_POST['description']),
            'display_order' => (int)($_POST['display_order'] ?? 1),
            'active'        => isset($_POST['active']) && $_POST['active'] == '1' ? 1 : 0
        ];

        if ($data['name'] === '') {
            $error = "Le nom de la catégorie est obligatoire.";
        }
        elseif ($model->categoryExists($data['name'])) {    //Verif Unicité
           $error = "Cette catégorie existe déjà !! Veuillez changer le nom de votre catégorie !!";
        }
        else {
            $newId = $model->createCategory($data);
            if ($newId !== false) {
?>
                <script>
                    alert ("Nouvelle catégorie d'activité créée avec succès !");
                </script>
<?php
            } else {
                $error = "Impossible de créer la catégorie.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Créer une Catégorie d’Activité | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/categories.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-category-content">
                    <div class="add-category-form">
                        <h1><i class="fas fa-plus-circle"></i> Nouvelle Catégorie d'Activité</h1>
                        
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= htmlspecialchars($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group">
                                <label for="name">Nom de la catégorie *</label>
                                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="display_order">Ordre d'affichage *</label>
                                    <select id="display_order" name="display_order">
                                        <option value="1" <?= (($_POST['display_order'] ?? '1') === '1') ? 'selected' : '' ?>>Élevé</option>
                                        <option value="2" <?= (($_POST['display_order'] ?? '1') === '2') ? 'selected' : '' ?>>Moyen</option>
                                        <option value="3" <?= (($_POST['display_order'] ?? '1') === '3') ? 'selected' : '' ?>>Bas</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="active">Statut *</label>
                                    <select id="active" name="active">
                                        <option value="1" <?= (($_POST['active'] ?? '1') === '1') ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= (($_POST['active'] ?? '1') === '0') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='listCategories.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Créer la catégorie
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

            // Gestion des messages d'erreur avec SweetAlert
            <?php if ($error): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '<?= addslashes($error) ?>',
                    confirmButtonColor: '#1E90FF'
                });
            <?php endif; ?>

            // Gestion des succès avec SweetAlert
            <?php if (isset($newId) && $newId !== false): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Nouvelle catégorie créée avec succès !',
                    confirmButtonColor: '#1E90FF'
                }).then(() => {
                    window.location.href = 'listCategories.php';
                });
            <?php endif; ?>
        </script>
    </body>
</html>