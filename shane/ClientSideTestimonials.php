<?php
include_once 'backend/config/db_config.php';
include_once 'backend/includes/db_connect.php';

$message = '';

if (isset($_POST['Envoyer'])) {
    $name = htmlspecialchars($_POST['name']);
    $entreprise = htmlspecialchars($_POST['entreprise']);
    $testimonial = htmlspecialchars($_POST['testimonial']);
    $poste = htmlspecialchars($_POST['poste']);
    $rating = htmlspecialchars($_POST['rating']);
    $image_path = null;

    // Upload du fichier
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowedTypes)) {
            $uploadDir = 'backend/uploads/testimonials/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
            $uploadFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                $image_path = $uploadFile;
            } else {
                $message = "Erreur lors de l'upload du fichier.";
            }
        } else {
            $message = "Type de fichier non autorisé.";
        }
    }

    // Insertion en base de données
    if (empty($message)) {
        try {
            $query = "INSERT INTO testimonials (name, position, company, content, image_path, rating) 
                      VALUES (:name, :position, :company, :content, :image_path, :rating)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':position' => $poste,
                ':company' => $entreprise,
                ':content' => $testimonial,
                ':image_path' => $image_path,
                ':rating' => $rating
            ]);
            $message = "Témoignage envoyé avec succès !";
        } catch (Exception $e) {
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre témoignage</title>
</head>
<body>
    <?php if (!empty($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form method="post" action="ClientSideTestimonials.php" enctype="multipart/form-data">
        <h1>Votre témoignage</h1>
        <label for="name">Nom(s) et Prénom(s):</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="entreprise">Entreprise</label>
        <input type="text" id="entreprise" name="entreprise" required><br><br>
        <label for="poste">Poste</label>
        <input type="text" id="poste" name="poste" required><br><br>
        <label for="rating">Note:</label>
        <input type="number" id="rating" name="rating" min="1" max="5" required><br><br>
        <label for="testimonial">Témoignage:</label><br>
        <textarea id="testimonial" name="testimonial" rows="4" cols="50" required></textarea><br><br>
        <label for="photo">Photo (facultatif):</label><br>
        <input type="file" name="photo" id="photo" accept="image/*" onchange="previewImage()"><br><br>
        <img id="preview" src="#" alt="Prévisualisation de l'image" style="display:none; max-width: 200px;"><br><br>
        <input type="submit" name="Envoyer" value="Envoyer">
    </form>
    <script>
        function previewImage() {
            const fileInput = document.getElementById('photo');
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