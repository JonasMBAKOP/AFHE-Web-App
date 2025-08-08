<?php
    require_once 'BaseModel.php';
    require_once __DIR__ . '/../includes/functions.php'; // Fonctions utiles

    class TestimonialModel extends BaseModel {

        // 🔹 Créer un témoignage avec gestion de l’image
        public function createTestimonial($name, $position, $company, $content, $imageFile, $rating, $display_order, $created_by) {
            $image_path = $this->uploadImage($imageFile, $name);

            $query = "INSERT INTO testimonials (name, position, company, content, image_path, rating, display_order, created_by) 
                    VALUES (:name, :position, :company, :content, :image_path, :rating, :display_order, :created_by)";
            $params = compact('name', 'position', 'company', 'content', 'image_path', 'rating', 'display_order', 'created_by');
            return $this->executeQuery($query, $params);
        }

        // 🔹 Modifier un témoignage avec gestion de l’image
        // public function updateTestimonial($id, $name, $position, $company, $content, $imageFile, $rating, $display_order, $active) {
        //     // 🔹 Vérifier si une nouvelle image est envoyée
        //     $currentTestimonial = $this->getTestimonialById($id);
        //     $image_path = !empty($imageFile['name']) ? $this->uploadImage($imageFile, $name) : $currentTestimonial['image_path'];

        //     $query = "UPDATE testimonials
        //             SET name = :name, position = :position, company = :company, content = :content, image_path = :image_path, 
        //                 rating = :rating, display_order = :display_order, active = :active
        //             WHERE id = :id";
        //     $params = compact('id', 'name', 'position', 'company', 'content', 'image_path', 'rating', 'display_order', 'active');
        //     return $this->executeQuery($query, $params);
        // }

        public function updateTestimonial($id, $name, $position, $company, $content, $imageFile, $rating, $display_order, $active) {
            // 🔹 Récupérer le témoignage actuel
            $currentTestimonial = $this->getTestimonialById($id);
            if (!$currentTestimonial) {
                return false;
            }

            // 🔹 Vérifier si le nom a changé
            $nameChanged = ($name !== $currentTestimonial['name']);

            // 🔹 Gérer l’image et le chemin d’accès
            $newImagePath = $currentTestimonial['image_path']; // Par défaut, conserver l’image actuelle

            if ($imageFile) { // 🔹 Cas où l’image est modifiée
                // 🔹 Supprimer l’ancienne image
                $this->deleteImage($newImagePath);

                // 🔹 Enregistrer la nouvelle image avec le nouveau nom
                $newImagePath = $this->uploadImage($imageFile, $name);
            } elseif ($nameChanged && !empty($currentTestimonial['image_path'])) { // 🔹 Cas où le nom est modifié mais pas l’image
                // 🔹 Construire le nouveau chemin de l’image avec le nom mis à jour
                // $uploadDir = 'uploads/testimonials/'; // Dossier d’upload
                $nameClean = normalizeText($name); // 🔹 Normaliser le nom pour bien gérer les caractères spéciaux
                $imageExt = pathinfo($currentTestimonial['image_path'], PATHINFO_EXTENSION);
                $newImagePath = "uploads/testimonials/Temoignage {$nameClean}." . $imageExt;

                // 🔹 Renommer le fichier image avec le nouveau nom
                rename($currentTestimonial['image_path'], $newImagePath);
            }

            // 🔹 Mettre à jour la base de données
            $query = "UPDATE testimonials
                      SET name = :name, position = :position, company = :company, content = :content, 
                          image_path = :image_path, rating = :rating, display_order = :display_order, active = :active
                      WHERE id = :id";

            $params = [
                'name'           => $name,
                'position'       => $position,
                'company'        => $company,
                'content'        => $content,
                'image_path'     => $newImagePath,
                'rating'         => $rating,
                'display_order'  => $display_order,
                'id'             => $id,
                'active'         => $active
            ];
            return $this->executeQuery($query, $params);
        }

        // 🔹 Supprimer un témoignage + son image
        public function deleteTestimonial($id) {
            $testimonial = $this->getTestimonialById($id);
            if (!empty($testimonial['image_path'])) {
                $this->deleteImage($testimonial['image_path']);
            }

            $query = "DELETE FROM testimonials WHERE id = :id";
            return $this->executeQuery($query, [':id' => $id]);
        }

        // 🔹 Afficher tous les témoignages
        // public function getAllTestimonials() {
        //     $query = "SELECT * FROM testimonials ORDER BY display_order ASC";
        //     return $this->executeQuery($query)->fetchAll(PDO::FETCH_ASSOC);
        // }

        public function getTestimonials($filters = [], $sortField = 'created_at', $sortOrder = 'DESC', $limit = null, $offset = 0) {
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
            $query = "SELECT * FROM testimonials WHERE 1=1";
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

        public function countTestimonials($filters = []) {
            $where = [];
            $params = [];
            
            if (!empty($filters['created_by'])) {
                $where[] = "created_by = :created_by";
                $params[':created_by'] = $filters['created_by'];
            }
            
            // Ajoutez les autres filtres de la même manière...
            
            $sql = "SELECT COUNT(*) FROM testimonials";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        }

        // 🔹 Compte le nombre total de témoignages
        public function countAllTestimonials(): int{
            $sql = "SELECT COUNT(*)
                    FROM testimonials";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }

        // 🔹 Récupérer un témoignage par ID
        public function getTestimonialById($id) {
            $query = "SELECT * FROM testimonials WHERE id = :id";
            return $this->executeQuery($query, [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        }

        // 🔹 Fonction pour gérer l’upload d’image
        private function uploadImage($imageFile, $name) {
            if (!$imageFile) {
                return null; // 🔹 Retourne null si aucune image n’est envoyée
            }

            $uploadDir = __DIR__ . '/../uploads/testimonials/'; // Dossier d’upload
            $nameClean = normalizeText($name); // 🔹 Normaliser le nom pour bien gérer les caractères spéciaux
            
            $imageExt = pathinfo($imageFile['name'], PATHINFO_EXTENSION); // 🔹 Récupérer l'extension du fichier
            $imageName = "Temoignage {$nameClean}." . $imageExt; // 🔹 Construire le nom du fichier
            $imagePath = $uploadDir . $imageName; // 🔹 Chemin complet pour l'upload

            if (move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
                return "uploads/testimonials/" . $imageName; // Chemin relatif à stocker en BDD
            } else {
                return null; // Si l'upload échoue
            }
        }

        // 🔹 Fonction pour supprimer une image
        private function deleteImage($imagePath) {
            $filePath = __DIR__ . '/../uploads/testimonials/' . basename($imagePath);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
?>