<?php
    //Script de suppression d'un témoignage

    require_once "../../includes/auth_guard.php";
    require_once "../../includes/db_connect.php"; // Connexion à la base
    require_once "../../models/TestimonialModel.php"; 

    verifierExpirationSession(); // Vérifier si la session a expiré

    $testimonialModel = new TestimonialModel();

    // Vérifier si un ID est bien passé en paramètre
    if (!isset($_GET["id"])) {
        die("Erreur : ID témoignage manquant.");
    }

    // Vérifier si le témoignage existe
    $testimonial = $testimonialModel->getTestimonialById($_GET["id"]);
    if (!$testimonial) {
        die("Erreur : Témoignage introuvable.");
    }

    // Supprimer le témoignage
    $testimonialModel->deleteTestimonial($_GET["id"]);

    redirect ("testimonials.php");

?>


