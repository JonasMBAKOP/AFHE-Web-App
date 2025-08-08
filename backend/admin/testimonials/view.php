<?php
    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/TestimonialModel.php";

    verifierExpirationSession();

    $current_page = 'view_testimonial';

    $testimonialModel = new TestimonialModel();

    $id = $_GET['id'] ?? null;
    $testimonial = $id ? $testimonialModel->getTestimonialById($id) : null;

    if (!$testimonial) {
        die("Témoignage introuvable.");
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Témoignages | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/testimonials.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="view-testimonial-content">
                    <div class="testimonial-view-card">
                        <div class="testimonial-view-header">
                            <?php if (!empty($testimonial['image_path'])) : ?>
                                <img src="../../<?= htmlspecialchars($testimonial['image_path']) ?>" class="testimonial-avatar-large" alt="<?= htmlspecialchars($testimonial['name']) ?>">
                            <?php else : ?>
                                <div class="testimonial-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="testimonial-info">
                                <h1 class="testimonial-name"><?= htmlspecialchars($testimonial['name']) ?></h1>
                                <div class="testimonial-position"><?= htmlspecialchars($testimonial['position']) ?></div>
                                <div class="testimonial-company"><?= htmlspecialchars($testimonial['company']) ?></div>
                                <div class="testimonial-rating"><?= str_repeat('★', (int) $testimonial['rating']) ?></div>
                            </div>
                        </div>

                        <div class="testimonial-content">
                            <?= nl2br(htmlspecialchars($testimonial['content'])) ?>
                        </div>

                        <div class="testimonial-meta">
                            <div class="testimonial-meta-item">
                                <i class="fas fa-sort-amount-down" style="color: var(--primary-blue);"></i>
                                <span>Priorité: <?= getDisplayOrderLabel($testimonial['display_order']) ?></span>
                            </div>
                            <div class="testimonial-meta-item">
                                <i class="fas fa-calendar-alt" style="color: var(--primary-blue);"></i>
                                <span>Créé le: <?= htmlspecialchars(formatDate($testimonial['created_at'])) ?></span>
                            </div>

                            <div class="testimonial-meta-item">
                                <?php
                                    $query = "SELECT username FROM users WHERE id_user = :id_user";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->execute(['id_user' => $testimonial['created_by']]);
                                    $username = $stmt->fetchColumn();
                                ?>
                                <i class="fas fa-user-edit" style="color: var(--primary-blue);"></i>
                                <span>Créé par: <?= htmlspecialchars($username) ?></span>
                            </div>
                            
                            <div class="testimonial-meta-item">
                                <i class="fas fa-circle" style="color: <?= $testimonial['active'] ? '#28a745' : '#dc3545'; ?>"></i>
                                <span>Statut: <?= $testimonial['active'] ? 'Actif' : 'Inactif' ?></span>
                            </div>
                        </div>

                        <div class="testimonial-actions">
                            <a href="testimonials.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <div style="display: flex; gap: 1rem;">
                                <a href="update.php?id=<?= $testimonial['id'] ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?= $testimonial['id'] ?>)" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </div>
                        </div>
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

        <!-- Script pour la confirmation de suppression -->
        <script>
            function confirmDelete(testimonialId) {
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
                            window.location.href = "delete.php?id=" + testimonialId;
                        });
                    }
                });
            }
        </script>
    </body>
</html>