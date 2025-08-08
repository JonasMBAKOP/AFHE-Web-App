<?php
    //Ajouter un Don

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/DonationModel.php"; // Modèle utilisateur
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'create_donation';

    $donationModel = new DonationModel();
    $message = "";

    // Vérifier si le formulaire a été soumis
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $donor_name = $_POST["donor_name"] ?? null;
        $donor_email = $_POST["donor_email"] ?? null;
        $donor_phone = $_POST["donor_phone"] ?? null;
        $amount = $_POST["amount"] ?? null;
        $currency = $_POST["currency"] ?? "USD"; // Devise par défaut
        $payment_method = $_POST["payment_method"] ?? null;
        $status = $_POST["status"] ?? "pending"; // Par défaut, le statut est "en attente"
        $transaction_id = $_POST["transaction_id"] ?? null;
        $is_anonymous = isset($_POST["is_anonymous"]) ? 1 : 0;
        $message = $_POST["message"] ?? null;
        $created_by = $_SESSION["id_user"] ?? null; // Récupérer l'ID de l'administrateur connecté

        // Vérifier que toutes les valeurs essentielles sont définies
        if ($donor_name && $amount && $payment_method && $created_by) {
            $donationModel->addDonation($donor_name, $donor_phone, $donor_email, $status, $transaction_id, $amount, $payment_method, $currency, $is_anonymous, $message, $created_by);
            header("Location: list.php?success=1"); // Redirection après ajout
            exit();
        } else {
            $message = "Veuillez remplir tous les champs obligatoires.";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ajouter un Don | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <link rel="stylesheet" href="../assets/css/templates.css">
        <link rel="stylesheet" href="../assets/css/admin_styles.css">
        <link rel="stylesheet" href="../assets/css/donations.css">
    </head>
    <body>
        <div class="admin-container">    
            <?php require_once "../templates/sidebar.php"; // Inclure la barre latérale ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php require_once "../templates/header.php"; // Inclure l'entête du back office ?>

                <div class="add-donation-content">
                    <div class="donation-form-card">
                        <div class="form-header">
                            <div class="form-icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <h1>Nouveau Don</h1>
                            <p>Renseignez les informations du donateur et du paiement</p>
                            
                            <?php if ($message): ?>
                            <div class="form-alert error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= htmlspecialchars($message) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                         <form method="POST" action="add.php" class="elegant-form">
                            <div class="form-columns">
                                <div class="form-column">
                                    <div class="form-section">
                                        <h3><i class="fas fa-user-circle"></i> Informations du Donateur</h3>
                                        
                                        <div class="form-group floating">
                                            <input type="text" id="donor_name" name="donor_name" placeholder=" " required>
                                            <label for="donor_name">Nom complet</label>
                                            <div class="form-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="email" id="donor_email" name="donor_email" placeholder=" " required>
                                            <label for="donor_email">Adresse email</label>
                                            <div class="form-icon">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="tel" id="donor_phone" name="donor_phone" placeholder=" " required>
                                            <label for="donor_phone">Téléphone</label>
                                            <div class="form-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                        </div>

                                        <div class="form-group checkbox-group">
                                            <input type="checkbox" id="is_anonymous" name="is_anonymous">
                                            <label for="is_anonymous" class="custom-checkbox">
                                                Don anonyme
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-column">
                                    <div class="form-section">
                                        <h3><i class="fas fa-money-bill-wave"></i> Détails du Paiement</h3>
                                        
                                        <div class="amount-group">
                                            <div class="form-group floating currency-select">
                                                <select id="currency" name="currency" required>
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="XAF">XAF</option>
                                                </select>
                                                <label>Devise</label>
                                            </div>

                                            <div class="form-group floating">
                                                <input type="number" id="amount" name="amount" step="0.1" placeholder=" " min="1" required>
                                                <label for="amount">Montant</label>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <select id="payment_method" name="payment_method" required>
                                                <option value="" disabled selected></option>
                                                <option value="PayPal">PayPal</option>
                                                <option value="Carte Bancaire">Carte Bancaire</option>
                                                <option value="Mobile Money">Mobile Money</option>
                                                <option value="Orange Money">Orange Money</option>
                                                <option value="Autre">Autre</option>
                                            </select>
                                            <label for="payment_method">Méthode de paiement</label>
                                            <div class="form-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="text" id="transaction_id" name="transaction_id" placeholder=" " required>
                                            <label for="transaction_id">ID Transaction</label>
                                            <div class="form-icon">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <select id="status" name="status" required>
                                                <option value="pending">En cours de traitement</option>
                                                <option value="completed">Effectué</option>
                                                <option value="failed">Échoué</option>
                                            </select>
                                            <label for="status">Statut</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section message-section">
                                <h3><i class="fas fa-comment-dots"></i> Message du donateur</h3>
                                <div class="form-group">
                                    <textarea id="message" name="message" rows="6" placeholder="Message du donateur..." required></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="window.location.href='list.php'">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer le don
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <?php require_once "../templates/footer.php"; // Inclure le footer du back office ?>
            </div>
        </div>


        
        <script>
            // Animation des labels flottants
            document.addEventListener('DOMContentLoaded', function() {
                // Animation des labels
                const floatLabels = () => {
                    document.querySelectorAll('.form-group.floating input, .form-group.floating select').forEach(el => {
                        el.addEventListener('focus', () => {
                            el.parentNode.classList.add('focused');
                            el.parentNode.querySelector('.form-icon').classList.add('active');
                        });
                        
                        el.addEventListener('blur', () => {
                            if (!el.value) {
                                el.parentNode.classList.remove('focused');
                            }
                            el.parentNode.querySelector('.form-icon').classList.remove('active');
                        });
                        
                        if (el.value) el.parentNode.classList.add('focused');
                    });
                };

                floatLabels();
            });
        </script>

        <script src="../assets/js/admin_script.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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