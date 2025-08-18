<?php
    include_once __DIR__ . '/../backend/includes/db_connect.php';
    require_once __DIR__ . "/../backend/models/TestimonialModel.php";

    $message = '';

    $tm = new TestimonialModel();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars($_POST['name']);
        $company = htmlspecialchars($_POST['company']);
        $content = htmlspecialchars($_POST['content']);
        $position = htmlspecialchars($_POST['position']);
        $rating = htmlspecialchars($_POST['rating']);
        $imageFile = !empty($_FILES['image']['name']) ? $_FILES['image'] : null;    // 🔹 Traitement de l’image (si elle existe)
        $active = 0;    // Pour que le témoignage ne s'affiche pas directement dans la section témoignage de la page d'accueil

        // 🔹 Ajout du témoignage en BD
        if ($tm->addTestimonial($name, $position, $company, $content, $imageFile, $rating, $active)) {
            $message = "Témoignage envoyé avec succès !";
            echo "<script>alert('votre Témoignage a bien été enregistré et va subir une vérification'); window.location.href='PrintSideTestimonials.php';</script>";
        } else {
            $message = "Erreur lors de l'envoi du témoignage.";
            echo "<script>alert('Erreur lors de l'enregistrement de votre temoignage');</script>";

        }


        
        // J'ai mis la suite en commentaire. Ce n'était pas totalement faux.
        // J'ai juste optimisé et ajusté avec une fonction "addTestimonial()" dans le fichier "TestimonialModel.php"
        // Ton princpal souci qui avait causé toutes ces erreurs était sur le chemin d'accès du require_once du début
        // Ton bout de code d'upload d'image donne bien, mais tu n'as pas géré le traitement du nom du fichier
        // Car le nom du fichier doit être "Témoignage {nomCompletDeCeluiQuiTémoigne}.{extension}"
        // Et tu n'as pas géré la nomenclature automatique et dynamique du fichier ni le nettoyage et la normalisation du nom du témoin
        // Donc j'ai conservé la fonction "uplaodImage()" et créé la fonction "addTestimonial()" pour gérer l'ajout d'un témoignage par un internaute lambda
        // J'ai ajusté aussi les valeurs des "name=" de tes inputs pour maintenir la cohérence avec le projet
        // Et j'ai suivi le mouvement dans le recueil des données du formulaire
        // C'est mieux d'utiliser "if ($_SERVER['REQUEST_METHOD'] === 'POST') {...}"
        // Tout le contenu de ce fichier a été mis dans le fichier "index.php" de la page d'accueil dans la section des témoignages
        // Juste en dessous des témoignages et s'affichera lors du clic sur le bouton "Ajouter votre témoignage"
        // Maintenant, je te demande de gérer la fonctionnalité d'affichage et de cache du formulaire
        // Genre le formulaire est inexistant (display: none;) tant qu'on ne clique pas sur "Ajouter votre témoignage"
        // Et lorsqu'il est affiché, il est au dessus de tout (z-index: 1000;). Géres aussi son CSS
        // Mets son css dans le fichier "shaneForm.css" et stocke le dans le dossier "frontend\assets\css"
        // Et fais en sorte que lorsqu'il envoie le formulaire, ça ouvre une boite de dialogue avec le message de réussite (ou d'échec)
        // ET lorsqu'il ferme la boîte de dialogue, ça ferme automatiquement le formulaire, et reviens à la page d'accueil



        
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Votre témoignage</title>
        <link rel="stylesheet" href="assets/css/shaneForm.css">
    </head>
    <body>
        <?php if (!empty($message)): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <form method="post" action="ClientSideTestimonials.php" enctype="multipart/form-data">
            <h1>Votre témoignage</h1>
            <label for="name">Nom complet</label>
            <input type="text" id="name" name="name" required placeholder="Ex: The King Jonas"><br><br>

            <label for="company">company</label>
            <input type="text" id="company" name="company" required placeholder="Ex: Kings' Empire Tech"><br><br>

            <label for="position">Poste</label>
            <input type="text" id="position" name="position" required placeholder="Ex: Développeur Web et Mobile"><br><br>
            
            <label for="rating">Note</label>
            <input type="number" id="rating" name="rating" min="1" max="5" value="5" required><br><br>
            
            <label for="content">Témoignage</label><br>
            <textarea id="content" name="content" rows="4" cols="50" required placeholder="Décrivez le témoignage..."></textarea><br><br>
            
            <label for="image">Photo (facultatif):</label><br>
            <input type="file" name="image" id="image" accept="image/*" onchange="previewImage()"><br><br>
            <img id="preview" src="#" alt="Prévisualisation de l'image" style="display:none; max-width: 200px;"><br><br>
            
            <input type="submit" name="Envoyer" value="Envoyer">
        </form>

        <script>
            function previewImage() {
                const fileInput = document.getElementById('image');
                const previewImage = document.getElementById('preview');
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewImage.src = '#';
                    previewImage.style.display = 'none';
                }
            }
        </script>
    </body>
</html>