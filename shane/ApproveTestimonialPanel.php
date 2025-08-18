<?php
    include_once __DIR__ . '/../backend/includes/db_connect.php';
    require_once __DIR__ . "/../backend/models/TestimonialModel.php";
    $req='SELECT * FROM testimonials WHERE active=0';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve the testimonial</title>
</head>
<body>
    <table>
        <th><td>PERSONNE</td><td>poste d'entreprise</td><td>note</td><td>statut</td><td>statut</td><td>créé le</td><td>Actions</td></th>
        <tr></tr>
    </table>
</body>
</html>