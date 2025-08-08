<?php
    //Ajouter des projets

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/ProjectModel.php"; // Modèle projet

    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'create_project';

    $projectModel = new ProjectModel(); // Instancier le modèle projet
    $error = ''; // Initialiser la variable d'erreur

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire et stockage dans un tableau associatif
        $created_by = $_SESSION["id_user"] ?? null; // Récupérer l'ID de l'administrateur connecté
        $priority = isset($_POST['priority']) ? intval($_POST['priority']) : 1; // Gestion de la priorité
        $data = [
            'title'             => trim($_POST['title']),
            'description'       => trim($_POST['description']),
            'short_description' => trim($_POST['short_description']),
            'status'            => $_POST['status'],         // Par exemple: "active" ou "inactive"
            'priority'          => $priority,          // Priorité du projet (1, 2, 3)
            'created_by'        => $created_by,       // Typiquement récupéré depuis la session de l'admin
            'active'            => 1                    // Par défaut, le projet est actif
        ];
        
        // Récupération des images uploadées
        $mainImage      = $_FILES['main_image'];           // L'image principale
        $secondaryImages = $_FILES['secondary_images'];    // Un tableau de fichiers pour les images secondaires
        
        if ($data['title'] === '') {
            $error = "Le nom du projet est obligatoire.";
        }
        elseif ($projectModel->projectExists($data['title'])) {    //Verif Unicité
           $error = "Ce projet existe déjà !! Veuillez changer le nom de votre projet !!";
        }
        else {
            // Appeler la fonction createProject() avec les trois paramètres
            $projectId = $projectModel->createProject($data, $mainImage, $secondaryImages);

            if ($projectId) {
?>
                <script>
                    alert ("Nouveau projet créé avec succès !");
                </script>
<?php
            } else {
                $error = "Erreur lors de la création du projet.";
            }
        }

        
        
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ajouter un Projet | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/projects.css">
    </head>

    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-project-content">
                    <div class="project-form-container">
                        <h1><i class="fas fa-plus-circle"></i> Ajouter un nouveau projet</h1>
                        
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="add.php" enctype="multipart/form-data" class="project-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Titre du projet</label>
                                    <input type="text" id="title" name="title" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select id="status" name="status" required>
                                        <option value="upcoming">À venir</option>
                                        <option value="ongoing">En cours</option>
                                        <option value="completed">Terminé</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="short_description">Description courte</label>
                                <textarea id="short_description" name="short_description" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description complète</label>
                                <textarea id="description" name="description" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="priority">Priorité</label>
                                    <select id="priority" name="priority" required>
                                        <option value="1">Élevée</option>
                                        <option value="2">Moyenne</option>
                                        <option value="3">Basse</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Image principale</label>
                                    <div class="file-upload">
                                        <label class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <div>Cliquez pour sélectionner une image</div>
                                            <div style="font-size: 0.8rem; color: #6c757d;">Formats acceptés: JPG, JPEG, PNG (max 2MB)</div>
                                            <input type="file" class="file-upload-input" name="main_image" accept="image/*" required>
                                        </label>
                                        <div class="file-upload-preview" id="mainImagePreview"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Images secondaires</label>
                                <div class="file-upload">
                                    <label class="file-upload-label">
                                        <i class="fas fa-images" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                        <div>Cliquez pour sélectionner plusieurs images</div>
                                        <div class="multiple-files-info">
                                            Formats acceptés: JPG, JPEG, PNG (max 2MB)<br>
                                            (Vous pouvez sélectionner plusieurs fichiers)
                                        </div>
                                        <input type="file" class="file-upload-input" name="secondary_images[]" accept="image/*" multiple>
                                    </label>
                                    <div class="file-upload-preview" id="secondaryImagesPreview"></div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.location.href='list.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Créer le projet
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
        <script src="../assets/js/projects.js"></script>

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