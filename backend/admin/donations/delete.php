<?php
    //Supprimer un Don

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base de données
    require_once "../../models/DonationModel.php"; // Modèle utilisateur
    verifierExpirationSession(); // Vérifier si la session a expiré

    $donationModel = new DonationModel();

    // Vérifier si l'ID du don est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID de don manquant.");
    }

    // Vérifier si le don existe
    $donation = $donationModel->getDonationById($_GET["id"]);
    if (!$donation) {
        die("Erreur : Don introuvable.");
    }

    // Supprimer le don
    $donationModel->deleteDonation($_GET["id"]);

    redirect ("list.php");
?>
