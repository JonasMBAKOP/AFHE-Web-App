<?php
    //Script de suppression d'un message de contacts

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base
    require_once "../../models/ContactModel.php"; 

    verifierExpirationSession(); // Vérifier si la session a expiré

    $contactModel = new ContactModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID message manquant.");
    }

    // Vérifier si l'utilisateur existe
    $contact = $contactModel->getContactById($_GET["id"]);
    if (!$contact) {
        die("Erreur : Message introuvable.");
    }

    // Supprimer l'utilisateur
    $contactModel->deleteContact($_GET["id"]);

    redirect ("admin_contacts.php");