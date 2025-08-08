<?php
    //Modifier un Don

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/DonationModel.php"; // Modèle utilisateur
    verifierExpirationSession(); // Vérifier si la session a expiré

    $current_page = 'update_donation';

    // Instancier le modèle
    $donationModel = new DonationModel();
    $message = "";

    // Vérifier si un ID de don est passé
    if (!isset($_GET["id"])) {
        die("Erreur : ID du don manquant.");
    }

    $donationId = intval($_GET["id"]);
    $donation = $donationModel->getDonationById($donationId);

    // Vérifier que le don existe
    if (!$donation) {
        die("Erreur : Don introuvable.");
    }

    // Mise à jour du don si le formulaire est soumis
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $donor_name =  htmlspecialchars($_POST["donor_name"]);
        $donor_email = htmlspecialchars($_POST["donor_email"]);
        $donor_phone = htmlspecialchars($_POST["donor_phone"]);
        $amount = htmlspecialchars($_POST["amount"]);
        $currency = htmlspecialchars($_POST["currency"]);
        $payment_method = htmlspecialchars($_POST["payment_method"]);
        $status = htmlspecialchars($_POST["status"]);
        $transaction_id = htmlspecialchars($_POST["transaction_id"]);
        $is_anonymous = isset($_POST["is_anonymous"]) ? 1 : 0;
        $message = htmlspecialchars($_POST["message"]);

        if ($donationModel->updateDonation($donationId, $donor_name, $donor_email, $donor_phone, $amount, $status, $payment_method, $transaction_id, $currency, $is_anonymous, $message)) {
            redirect("list.php");
        } else {
            $message = "Échec de la mise à jour du don.";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier un Don | AFHE Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

                <div class="update-donation-content">
                    <div class="donation-form-card">
                        <div class="form-header">
                            <div class="form-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h1>Modifier le Don de « <span style="color:#FF69B4;"><?= $donation['donor_name'] ?></span> »</h1>
                            <p>Mettez à jour les informations de ce don</p>
                            
                            <?php if ($message): ?>
                            <div class="form-alert error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= htmlspecialchars($message) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="donation-status-badge status-<?= strtolower($donation['status']) ?>">
                                <?= ucfirst($donation['status']) ?>
                            </div>
                        </div>

                        <!-- Formulaire de modification -->
                        <form method="POST" action="update.php?id=<?= $donationId ?>" class="elegant-form">
                            <div class="form-columns">
                                <div class="form-column">
                                    <div class="form-section">
                                        <h3><i class="fas fa-user-circle"></i> Informations du Donateur</h3>
                                        
                                        <div class="form-group floating">
                                            <input type="text" id="donor_name" name="donor_name" 
                                                value="<?= htmlspecialchars($donation['donor_name']) ?>" 
                                                placeholder=" " required>
                                            <label for="donor_name">Nom complet</label>
                                            <div class="form-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="email" id="donor_email" name="donor_email" 
                                                value="<?= htmlspecialchars($donation['donor_email']) ?>" 
                                                placeholder=" ">
                                            <label for="donor_email">Adresse email</label>
                                            <div class="form-icon">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="tel" id="donor_phone" name="donor_phone" 
                                                value="<?= htmlspecialchars($donation['donor_phone']) ?>" 
                                                placeholder=" ">
                                            <label for="donor_phone">Téléphone</label>
                                            <div class="form-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                        </div>

                                        <div class="form-group checkbox-group">
                                            <input type="checkbox" id="is_anonymous" name="is_anonymous" 
                                                <?= $donation['is_anonymous'] ? "checked" : "" ?>>
                                            <label for="is_anonymous" class="custom-checkbox">
                                                <span class="checkmark"></span>
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
                                                    <option value="USD" <?= $donation['currency'] === "USD" ? "selected" : "" ?>>USD</option>
                                                    <option value="EUR" <?= $donation['currency'] === "EUR" ? "selected" : "" ?>>EUR</option>
                                                    <option value="XAF" <?= $donation['currency'] === "XAF" ? "selected" : "" ?>>XAF</option>
                                                </select>
                                                <label>Devise</label>
                                            </div>

                                            <div class="form-group floating">
                                                <input type="number" id="amount" name="amount" 
                                                    value="<?= htmlspecialchars($donation['amount']) ?>" 
                                                    step="0.01" placeholder=" " required>
                                                <label for="amount">Montant</label>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <select id="payment_method" name="payment_method" required>
                                                <option value="PayPal" <?= $donation['payment_method'] === "PayPal" ? "selected" : "" ?>>PayPal</option>
                                                <option value="Carte Bancaire" <?= $donation['payment_method'] === "Carte Bancaire" ? "selected" : "" ?>>Carte Bancaire</option>
                                                <option value="Mobile Money" <?= $donation['payment_method'] === "Mobile Money" ? "selected" : "" ?>>Mobile Money</option>
                                                <option value="Orange Money" <?= $donation['payment_method'] === "Orange Money" ? "selected" : "" ?>>Orange Money</option>
                                            </select>
                                            <label for="payment_method">Méthode de paiement</label>
                                            <div class="form-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <input type="text" id="transaction_id" name="transaction_id" 
                                                value="<?= htmlspecialchars($donation['transaction_id']) ?>" 
                                                placeholder=" ">
                                            <label for="transaction_id">ID Transaction</label>
                                            <div class="form-icon">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                        </div>

                                        <div class="form-group floating">
                                            <select id="status" name="status" required>
                                                <option value="pending" <?= $donation['status'] === "pending" ? "selected" : "" ?>>En attente</option>
                                                <option value="confirmed" <?= $donation['status'] === "confirmed" ? "selected" : "" ?>>Confirmé</option>
                                                <option value="failed" <?= $donation['status'] === "failed" ? "selected" : "" ?>>Échoué</option>
                                            </select>
                                            <label for="status">Statut</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section message-section">
                                <h3><i class="fas fa-comment-dots"></i> Message</h3>
                                <div class="form-group">
                                    <textarea id="message" name="message" rows="6" placeholder="Message du donateur..."><?= htmlspecialchars($donation['message']) ?></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="window.location.href='list.php'">
                                    <i class="fas fa-arrow-left"></i> Annuler
                                </button>
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $donationId ?>)">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </div>
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

        <!-- Script pour la confirmation de suppression -->
         <script>
            function confirmDelete(donationId) {
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
                            window.location.href = "delete.php?id=" + donationId;
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
                            window.location.href = "logout.php";
                        });
                    }
                });
            });
        </script>
    </body>
</html>