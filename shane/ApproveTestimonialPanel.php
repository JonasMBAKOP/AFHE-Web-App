<?php
    include_once __DIR__ . '/../backend/includes/db_connect.php';
    require_once __DIR__ . "/../backend/models/TestimonialModel.php";
    
    // Crée une classe anonyme pour utiliser la méthode executeQuery
    $shane = new class extends BaseModel {
        public function shanegetTestimonials($sortField = 'created_at', $sortOrder = 'DESC', $limit = 100, $offset = 0) {
            $allowedSortFields = ['name', 'rating', 'created_at'];
            $allowedSortOrder  = ['ASC', 'DESC'];
            if (!in_array($sortField, $allowedSortFields)) $sortField = 'created_at';
            if (!in_array($sortOrder, $allowedSortOrder)) $sortOrder = 'DESC';
            $query = "SELECT * FROM testimonials WHERE active=1 ORDER BY $sortField $sortOrder LIMIT :limit OFFSET :offset";
            $params = [':limit' => $limit, ':offset' => $offset];
            $stmt = $this->executeQuery($query, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    };
    $testimonials = $shane->shanegetTestimonials();

    if(isset($_POST['valider'])) {
        
        echo "<script>alert('Témoignage validé avec succès');</script>";
    }
    if(isset($_POST['supprimer'])) {
        
        echo "<script>alert('Témoignage supprimé correctement');</script>";
    }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve the testimonial</title>
</head>
<body>
    <form action="ApproveTestimonialPanel.php" method="post" enctype="multipart/form-data">
        <table>
        <thead>
            <tr>
            <th>Nom</th><th>Poste</th><th>Entreprise</th><th>Témoignage</th><th>Note</th><th>Date de création</th><th>Actions</th>
            </tr>

        </thead>
        <tbody>
            <?php
            foreach ($testimonials as $testimonial) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($testimonial['name']) . '</td>';
                echo '<td>' . htmlspecialchars($testimonial['position']) . '</td>';
                echo '<td>' . htmlspecialchars($testimonial['company']) . '</td>';
                echo '<td>' . htmlspecialchars($testimonial['content']) . '</td>';
                echo '<td>' . htmlspecialchars($testimonial['rating']) . '</td>';
                echo '<td>' . htmlspecialchars($testimonial['created_at']) . '</td>';
                echo '<td></td>';
                echo '</tr>';
            }
            ?>
            <button id="valider"  name="valider">Valider</button>
            <button id="supprimer"  name="supprimer">Valider</button>
        </tbody>

    </table>
    </form>
</body>
</html>