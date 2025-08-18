<?php
    include_once __DIR__ . '/../backend/includes/db_connect.php';
    require_once __DIR__ . "/../backend/models/TestimonialModel.php";
    
    function shanegetTestimonials( $sortField = 'created_at', $sortOrder = 'DESC', $limit = null, $offset = 0) {
            // Définir les champs autorisés pour le tri
            $allowedSortFields = ['name', 'rating', 'created_at'];
            $allowedSortOrder  = ['ASC', 'DESC'];

            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }
            if (!in_array($sortOrder, $allowedSortOrder)) {
                $sortOrder = 'DESC';
            }
            
            // Construction de la requête de base
            $query = "SELECT * FROM testimonials WHERE active=1";
            $params = [];

            // Application des filtres
            if (!empty($filters['created_by'])) {
                $query .= " AND created_by = :created_by";
                $params[':created_by'] = $filters['created_by'];
            }
            if (!empty($filters['display_order'])) {
                $query .= " AND display_order = :display_order";
                $params[':display_order'] = $filters['display_order'];
            }
            if (!empty($filters['rating'])) {
                $query .= " AND rating = :rating";
                $params[':rating'] = $filters['rating'];
            }
            if (isset($filters['active']) && $filters['active'] !== '') {
                $query .= " AND active = :active";
                $params[':active'] = $filters['active'];
            }

            // Ajout du tri
            $query .= " ORDER BY $sortField $sortOrder";
            
            // Ajout de la pagination
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit']  = $limit;
            $params[':offset'] = $offset;
            
            // Exécution de la requête en utilisant la méthode executeQuery() de BaseModel
            $stmt = $this->executeQuery($query, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <table>
        <th><td>PERSONNE</td><td>poste d'entreprise</td><td>note</td><td>statut</td><td>statut</td><td>créé le</td><td>Actions</td></th>
        <tr></tr>
    </table>
</body>
</html>