<?php
    //Pour gérer les activités
    
    require_once 'BaseModel.php';
    require_once __DIR__ . '/../includes/functions.php'; // Fonctions utiles

    class ActivityModel extends BaseModel {

        public function __construct()
        {
            parent::__construct(); // initialise $this->db
            // Autres initialisations spécifiques si besoin
        }

        // Récupère toutes les catégories (pour un dropdown)
        public function getCategories() {
            $stmt = $this->executeQuery(
                "SELECT id, name
                 FROM activity_categories
                 ORDER BY display_order ASC"
            );
            return $stmt->fetchAll();
        }


        // Récupère une catégorie par son ID
        public function getCategoryById(int $id) {
            $stmt = $this->executeQuery(
                "SELECT * FROM activity_categories WHERE id = :id",
                [':id' => $id]
            );
            $row = $stmt->fetch();
            return $row ?: null;
        }


        // Compte toutes les catégories (actives ou non)
        public function countCategories($onlyActive = false) {
            $sql = "SELECT COUNT(*) AS cnt FROM activity_categories c";
            if ($onlyActive) {
                $sql .= " WHERE c.active = 1";
            }
            $stmt = $this->executeQuery($sql);
            $row  = $stmt->fetch();
            return (int)$row['cnt'];
        }


        // Récupérer toutes les catégories actives (ou non), avec le nombre d’activités associées.
        public function getCategoryStats($limit, $offset, $onlyActiveCategories = false) {
            $sql = "SELECT c.id, c.name, c.description, c.display_order, c.active, COUNT(a.id_activity) AS activity_count
                    FROM activity_categories c
                    LEFT JOIN activities a
                    ON a.category_id = c.id
            ";
            if ($onlyActiveCategories) {
                $sql .= " WHERE c.active = 1";
            }
            $sql .= " GROUP BY c.id, c.name, c.description, c.display_order, c.active 
                      ORDER BY c.name ASC
                      LIMIT :limit OFFSET :offset
                    ";
            $params = [
                ':limit'  => (int)$limit,
                ':offset' => (int)$offset
            ];
            $stmt = $this->executeQuery($sql, $params);
            return $stmt->fetchAll();
        }


        // Crée une catégorie + son dossier
        public function createCategory(array $data): int|false
        {
            // Slug pour le dossier
            $slug = normalizeText($data['name']);

            // Insertion en BDD
            $this->executeQuery(
                "INSERT INTO activity_categories
                (name, description, display_order, active)
                VALUES
                (:name, :description, :display_order, :active)",
                [
                    ':name'          => $data['name'],
                    ':description'   => $data['description'],
                    ':display_order' => $data['display_order'],
                    ':active'        => $data['active']
                ]
            );
            $newId = (int)$this->pdo->lastInsertId();

            // Création du dossier physique uploads/activities/<slug>/
            $root      = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
            $relFolder = "uploads/activities/{$slug}";
            $absFolder = $root . str_replace('/', DIRECTORY_SEPARATOR, $relFolder);

            if (!is_dir($absFolder)) {
                mkdir($absFolder, 0755, true);
            }

            return $newId;
        }


        // Créer une activité (record + dossier + images)
        public function createActivity(
            array $data,
            array $mainImageFile,
            ?array $secondaryImageFiles
        ): int|false {
            // Vérifier la catégorie
            $category = $this->getCategoryById($data['category_id']);
            if (!$category) {
                return false;
            }

            // Slugs et chemins
            $catSlug    = normalizeText($category['name']);
            $actSlug    = normalizeText($data['title']);
            $root       = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
            $relFolder  = "uploads/activities/{$catSlug}/{$actSlug}";
            $absFolder  = $root . str_replace('/', DIRECTORY_SEPARATOR, $relFolder);

            if (!is_dir($absFolder)) {
                mkdir($absFolder, 0755, true);
            }

            // Upload de l'image principale
            $mainPath = null;
            if (isset($mainImageFile['error']) && $mainImageFile['error'] === UPLOAD_ERR_OK) {
                $mainPath = $this->uploadImageFile(
                    $mainImageFile,
                    $absFolder,
                    "{$actSlug} - main"
                );
            }

            // Insert en BDD de l’activité
            $this->executeQuery(
                "INSERT INTO activities
                (title, description, short_description,
                category_id, featured, main_image, created_by)
                VALUES
                (:title, :description, :short_description,
                :category_id, :featured, :main_image, :created_by)",
                [
                    ':title'             => $data['title'],
                    ':description'       => $data['description'],
                    ':short_description' => $data['short_description'],
                    ':category_id'       => $data['category_id'],
                    ':featured'          => $data['featured'],
                    ':main_image'        => $mainPath,
                    ':created_by'        => $data['created_by']
                ]
            );
            $activityId = (int)$this->pdo->lastInsertId();

            // Upload des images secondaires
            $order = 0;
            if ($secondaryImageFiles && isset($secondaryImageFiles['name'])) {
                $count = count($secondaryImageFiles['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($secondaryImageFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $order++;
                        $file = [
                            'name'     => $secondaryImageFiles['name'][$i],
                            'type'     => $secondaryImageFiles['type'][$i],
                            'tmp_name' => $secondaryImageFiles['tmp_name'][$i],
                            'error'    => $secondaryImageFiles['error'][$i],
                            'size'     => $secondaryImageFiles['size'][$i],
                        ];
                        $secPath = $this->uploadImageFile(
                            $file,
                            $absFolder,
                            "{$actSlug} - secondary_{$order}"
                        );
                        $this->executeQuery(
                            "INSERT INTO activity_images
                            (activity_id, image_path, caption, display_order)
                            VALUES
                            (:aid, :path, '', :ord)",
                            [
                                ':aid'  => $activityId,
                                ':path' => $secPath,
                                ':ord'  => $order
                            ]
                        );
                    }
                }
            }

            return $activityId;
        }


        // Petit helper pour uploader un fichier et renvoyer le chemin relatif
        public function uploadImageFile(array $imageFile, string $absFolder, string $prefix): string
        {
            $ext       = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $filename  = "{$prefix}.{$ext}";
            $destAbs   = $absFolder . DIRECTORY_SEPARATOR . $filename;

            // Si le dossier a disparu, on le recrée
            if (!is_dir($absFolder)) {
                mkdir($absFolder, 0755, true);
            }

            move_uploaded_file($imageFile['tmp_name'], $destAbs);

            // Calcul du chemin relatif pour la BDD
            $root = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
            $rel  = str_replace($root, '', $destAbs);
            return str_replace('\\', '/', $rel);
        }


        // Vérifier si une catégorie existe déjà (même nom)
        public function categoryExists(string $name): bool
        {
            $stmt = $this->executeQuery(
                "SELECT COUNT(*) AS cnt
                FROM activity_categories
                WHERE name = :name",
                [':name' => $name]
            );
            $row = $stmt->fetch();
            return ((int)$row['cnt'] > 0);
        }


        // Vérifie si une activité existe déjà dans une même catégorie.
        public function activityExists(string $title, int $categoryId): bool
        {
            $stmt = $this->executeQuery(
                "SELECT COUNT(*) AS cnt
                FROM activities
                WHERE title = :title
                    AND category_id = :cid",
                [
                    ':title' => $title,
                    ':cid'   => $categoryId
                ]
            );
            $row = $stmt->fetch();
            return ((int)$row['cnt'] > 0);
        }


        // Récupère N activités avec filtres, tri, pagination.
        public function getActivities($filters = [], $sortField = 'title', $sortOrder = 'ASC', $limit = 10, $offset = 0) {
            $sql    = "SELECT a.*, c.name AS category_name
                        FROM activities a
                    LEFT JOIN activity_categories c 
                        ON a.category_id = c.id
                        WHERE 1";
            $params = [];

            // Filtre catégorie
            if (!empty($filters['category_id'])) {
                $sql .= " AND a.category_id = :cat";
                $params[':cat'] = $filters['category_id'];
            }
            // Filtre créé par
            if (!empty($filters['created_by'])) {
                $sql .= " AND a.created_by = :creator";
                $params[':creator'] = $filters['created_by'];
            }

            // Valide le tri
            $allowed = ['title','created_at'];
            if (!in_array($sortField, $allowed)) {
                $sortField = 'title';
            }
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

            $sql .= " ORDER BY a.{$sortField} {$sortOrder}";
            $sql .= " LIMIT :limit OFFSET :offset";

            // Toujours binder limit/offset en int
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $k => &$v) {
                $stmt->bindParam($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        }


        // Compte le nombre d'activités selon les mêmes filtres
        public function countActivities($filters = []) {
            $sql    = "SELECT COUNT(*) AS cnt
                        FROM activities a
                        WHERE 1";
            $params = [];

            if (!empty($filters['category_id'])) {
                $sql .= " AND a.category_id = :cat";
                $params[':cat'] = $filters['category_id'];
            }
            if (!empty($filters['created_by'])) {
                $sql .= " AND a.created_by = :creator";
                $params[':creator'] = $filters['created_by'];
            }

            $stmt = $this->executeQuery($sql, $params);
            $row  = $stmt->fetch();
            return (int)$row['cnt'];
        }


        // Compte le nombre total d'activités
        public function countAllActivities(): int{
            $sql = "SELECT COUNT(*)
                    FROM activities";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }


        /**
         * Récupère une activité par son ID (avec le nom de la catégorie).
         * @param int $id
         * @return array|null
         */
        public function getActivityById(int $id): ?array
        {
            $stmt = $this->executeQuery(
                "SELECT a.*, c.name AS category_name
                FROM activities a
            LEFT JOIN activity_categories c ON a.category_id = c.id
                WHERE a.id_activity = :id",
                [':id' => $id]
            );
            return $stmt->fetch() ?: null;
        }


         /**
         * Récupère la liste des créateurs (distinct created_by) pour les filtres.
         * @return array  Tableau d’IDs [ ['created_by'=>1], ['created_by'=>2], … ]
         */
        public function getCreators(): array
        {
            $stmt = $this->executeQuery(
                "SELECT DISTINCT created_by
                FROM activities
                WHERE created_by IS NOT NULL
                ORDER BY created_by ASC"
            );
            return $stmt->fetchAll();
        }


        /**
         * Récupère les activités “à la une”.
         */
        public function getFeaturedActivities(int $limit = 5) {
            $stmt = $this->executeQuery(
                "SELECT a.*, c.name AS category_name
                FROM activities a
                LEFT JOIN activity_categories c
                    ON a.category_id = c.id
                WHERE a.featured = 1
            ORDER BY a.created_at DESC
                LIMIT :lim",
                [':lim' => $limit]
            );
            return $stmt->fetchAll();
        }


        /**
         * Récupère les images secondaires d'une activité.
         * @param int $activityId
         * @return array
         */
        public function getActivityImages(int $activityId) {
            $stmt = $this->executeQuery(
                "SELECT * 
                FROM activity_images
                WHERE activity_id = :aid
            ORDER BY display_order ASC",
                [':aid' => $activityId]
            );
            return $stmt->fetchAll();
        }


        // Supprime une activité, ses images (principale et secondaires) et le dossier associé
        public function deleteActivity($id) {
            // Récupère l'activité via son ID
            $act = $this->getActivityById($id);
            if (!$act) {
                return false;
            }

            // Détermine le dossier d l'activité. 
            $relativeFolder = dirname($act['main_image']);
            $folderPath = "../../" . $relativeFolder; // Chemin complet du dossier

            // Supprime le dossier et son contenu (si le dossier existe)
            if (is_dir($folderPath)) {
                deleteDirectory($folderPath);
            }

            // Supprime d'abord les enregistrements des images secondaires dans la table activity_images
            $queryImages = "DELETE FROM activity_images WHERE activity_id = :activity_id";
            $this->executeQuery($queryImages, [':activity_id' => $id]);

            // Enfin, supprime l'activité de la table activities
            $queryActivity = "DELETE FROM activities WHERE id_activity = :id";
            return $this->executeQuery($queryActivity, [':id' => $id]);
        }


        // Compte le nbre d'activités d'une catégorie
        public function countActivitiesInCategory($categoryId) {
            $stmt = $this->executeQuery(
                "SELECT COUNT(*) AS cnt
                FROM activities
                WHERE category_id = :cid",
                [':cid' => $categoryId]
            );
            $row = $stmt->fetch();
            return (int)$row['cnt'];
        }


        // Supprime un catégorie et tout ce qu'elle contient
        public function deleteCategory(int $categoryId) {
            // 1) On charge la catégorie pour générer le slug
            $cat = $this->getCategoryById($categoryId);
            if (!$cat) {
                return false;
            }
            $catSlug = normalizeText($cat['name']);

            // 2) On récupère toutes les activités liées
            $stmt = $this->executeQuery(
                "SELECT id_activity
                FROM activities
                WHERE category_id = :cid",
                [':cid' => $categoryId]
            );
            $activityIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            // 3) On supprime chacune via deleteActivity()
            foreach ($activityIds as $aid) {
                $this->deleteActivity((int)$aid);
            }

            // 4) On supprime le dossier de la catégorie
            $root      = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
            $catFolder = $root 
                    . 'uploads' . DIRECTORY_SEPARATOR 
                    . 'activities' . DIRECTORY_SEPARATOR 
                    . $catSlug;
            deleteDirectory($catFolder);

            // 5) On supprime la catégorie en base
            $this->executeQuery(
                "DELETE FROM activity_categories WHERE id = :id",
                [':id' => $categoryId]
            );

            return true;
        }


        // Mettre à jour une catégorie d'activité
        public function updateCategory($id, array $data) {
            // 1) Charger l’ancienne catégorie
            $oldCat = $this->getCategoryById($id);
            if (!$oldCat) {
                return false;
            }

            // 2) Calculer slugs
            $oldSlug = normalizeText($oldCat['name']);
            $newSlug = normalizeText($data['name']);

            // 3) Si slug changé, renommer dossier physique
            if ($oldSlug !== $newSlug) {
                $root      = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
                $baseDir   = $root . 'uploads' . DIRECTORY_SEPARATOR . 'activities' . DIRECTORY_SEPARATOR;
                $oldDir    = $baseDir . $oldSlug;
                $newDir    = $baseDir . $newSlug;

                if (is_dir($oldDir)) {
                    rename($oldDir, $newDir);
                }
                // Mettre à jour en BD les chemins relatifs
                $oldSegment = "uploads/activities/{$oldSlug}/";
                $newSegment = "uploads/activities/{$newSlug}/";

                // 4a) Main images
                $sql1 = "UPDATE activities
                         SET main_image = REPLACE(main_image, :oldSeg, :newSeg)
                         WHERE category_id = :cid
                ";
                $this->executeQuery($sql1, [
                    ':oldSeg' => $oldSegment,
                    ':newSeg' => $newSegment,
                    ':cid'    => $id
                ]);

                // 4b) Secondary images
                $sql2 = "UPDATE activity_images i
                         JOIN activities a
                         ON i.activity_id = a.id_activity
                         AND a.category_id = :cid
                         SET i.image_path = REPLACE(i.image_path, :oldSeg, :newSeg)
                ";
                $this->executeQuery($sql2, [
                    ':oldSeg' => $oldSegment,
                    ':newSeg' => $newSegment,
                    ':cid'    => $id
                ]);
            }

            // 5) Mettre à jour la catégorie en base
            $sql3 = "UPDATE activity_categories
                     SET name          = :name,
                         description   = :desc,
                         display_order = :ord,
                         active        = :act
                     WHERE id = :id
            ";
            $this->executeQuery($sql3, [
                ':name' => $data['name'],
                ':desc' => $data['description'],
                ':ord'  => $data['display_order'],
                ':act'  => $data['active'],
                ':id'   => $id
            ]);

            return true;
        }


        // Mettre à jour une activité
        /**
         * Met à jour une activité, renomme dossier & images si le titre change,
         * gère le main image, la suppression et l'ajout d'images secondaires.
         *
         * @param int   $id                      ID de l’activité
         * @param array $data                    ['title','description','short_description','category_id','featured','created_by']
         * @param array $mainImageFile           $_FILES['main_image']
         * @param array $secondaryImageFiles     $_FILES['secondary_images']
         * @param array $deleteSecondaryIds      IDs des activity_images à supprimer
         * @return bool
         */
        public function updateActivity($id, $data, $mainImageFile, $secondaryImageFiles, $deleteSecondaryIds = []) {
            // 1) Charger l’existant
            $old   = $this->getActivityById($id);
            $oldCat= $this->getCategoryById($old['category_id']);
            $newCat= $this->getCategoryById($data['category_id']);
            if (!$old || !$oldCat || !$newCat) {
                return false;
                // throw new \Exception("Point X a échoué : détail…");
            }

            // 2) Slugs et dossiers
            $oldCatSlug = normalizeText($oldCat['name']);
            $newCatSlug = normalizeText($newCat['name']);
            $oldActSlug = normalizeText($old['title']);
            $newActSlug = normalizeText($data['title']);

            $root       = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
            $base = $root 
                        . 'uploads' . DIRECTORY_SEPARATOR 
                        . 'activities' . DIRECTORY_SEPARATOR;
            $oldDir  = "{$base}{$oldCatSlug}/{$oldActSlug}";
            $newDir  = "{$base}{$newCatSlug}/{$newActSlug}";

            // 3) Créer le dossier cible et déplacer/renommer
            // if (!is_dir($newDir)) {
            //     mkdir($newDir, 0755, true);
            // }
            if ($oldDir !== $newDir && is_dir($oldDir) && !is_dir($newDir)) {
                rename($oldDir, $newDir);
            }

            // 4) Mettre à jour en BD tous les chemins
            $oldSeg = "uploads/activities/{$oldCatSlug}/{$oldActSlug}/";
            $newSeg = "uploads/activities/{$newCatSlug}/{$newActSlug}/";

            // a) main_image
            $this->executeQuery(
                "UPDATE activities
                    SET main_image = REPLACE(main_image, :oldSeg, :newSeg)
                    WHERE id_activity = :id",
                [':oldSeg'=>$oldSeg, ':newSeg'=>$newSeg, ':id'=>$id]
            );

            // b) secondaires
            $this->executeQuery(
                "UPDATE activity_images i
                JOIN activities a ON i.activity_id = a.id_activity
                SET i.image_path = REPLACE(i.image_path, :oldSeg, :newSeg)
                WHERE a.id_activity = :id",
                [':oldSeg'=>$oldSeg, ':newSeg'=>$newSeg, ':id'=>$id]
            );

            // 5) Gérer upload / renommage main_image
            $mainPath = $old['main_image'];
            // a) Si on a uploadé une nouvelle image principale
            if (/*$mainImageFile && */ isset($mainImageFile['error']) && $mainImageFile['error'] === UPLOAD_ERR_OK) {
                // supprimer l’ancienne physiquement
                @unlink($root . $old['main_image']);
                // uploader la nouvelle
                $mainPath = $this->uploadImageFile($mainImageFile, $newDir, "{$newActSlug} - main");
            }
            // b) Sinon si le slug a changé, renommer le fichier existant
            elseif ((!isset($mainFile['error']) || $mainFile['error'] !== UPLOAD_ERR_OK) && $oldSeg !== $newSeg && !empty($old['main_image'])) {
                // $oldAbs  = $root . $old['main_image'];
                $oldMainName = basename($old['main_image']);
                $ext     = pathinfo($oldMainName, PATHINFO_EXTENSION);
                $newMainName = "{$newActSlug} - main.{$ext}";
                $oldAbs      = $newDir . DIRECTORY_SEPARATOR . $oldMainName;
                $newAbs      = $newDir . DIRECTORY_SEPARATOR . $newMainName;
                // $newAbs  = "{$newDir}/{$newName}";
                if (is_file($oldAbs)) {
                    $moved = @rename($oldAbs, $newAbs);
                    if (!$moved) {
                        // fallback pour les environnements où rename() bloque
                        $moved = @copy($oldAbs, $newAbs) && @unlink($oldAbs);
                    }
                    if ($moved) {
                        $mainPath = str_replace('\\','/',
                            substr($newAbs, strlen($root))
                        );
                    }
                }
            }

            // 6) Supprimer les secondaires cochées
            if (!empty($deleteSecondaryIds)) {
                foreach ($deleteSecondaryIds as $imgId) {
                    $stmt = $this->executeQuery(
                        "SELECT image_path FROM activity_images WHERE id = :iid",
                        [':iid' => $imgId]
                    );
                    $row = $stmt->fetch();
                    if ($row) {
                        @unlink($root . $row['image_path']);
                        $this->executeQuery(
                            "DELETE FROM activity_images WHERE id = :iid",
                            [':iid' => $imgId]
                        );
                    }
                }
            }

            // 7) Renommer et ré-indexer les images secondaires restantes
            $remaining = $this->getActivityImages($id);
            $order      = 0;
            foreach ($remaining as $img) {
                $order++;
                $ext     = pathinfo($img['image_path'], PATHINFO_EXTENSION);
                $oldAbs  = $root . $img['image_path'];
                $newName = "{$newActSlug} - secondary_{$order}.{$ext}";
                $newAbs  = "{$newDir}/{$newName}";

                if (is_file($oldAbs)) {
                    rename($oldAbs, $newAbs);
                    $newRel = str_replace('\\','/',
                        substr($newAbs, strlen($root))
                    );
                    $this->executeQuery(
                        "UPDATE activity_images
                            SET image_path = :path,
                                display_order = :ord
                        WHERE id = :iid",
                        [
                            ':path' => $newRel,
                            ':ord'  => $order,
                            ':iid'  => $img['id']
                        ]
                    );
                }
            }

            // 8) Ajouter les nouvelles images secondaires
            if ($secondaryImageFiles && isset($secondaryImageFiles['name'])) {
                $count = count($remaining);
                for ($i = 0; $i < $countImages = count($secondaryImageFiles['name']); $i++) {
                    if ($secondaryImageFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $order++;
                        $file = [
                            'name'     => $secondaryImageFiles['name'][$i],
                            'type'     => $secondaryImageFiles['type'][$i],
                            'tmp_name' => $secondaryImageFiles['tmp_name'][$i],
                            'error'    => $secondaryImageFiles['error'][$i],
                            'size'     => $secondaryImageFiles['size'][$i],
                        ];
                        $newRel = $this->uploadImageFile(
                            $file,
                            $newDir,
                            "{$newActSlug} - secondary_{$order}"
                        );
                        $this->executeQuery(
                            "INSERT INTO activity_images
                            (activity_id, image_path, caption, display_order)
                            VALUES
                            (:aid, :path, '', :ord)",
                            [
                                ':aid'  => $id,
                                ':path' => $newRel,
                                ':ord'  => $order
                            ]
                        );
                    }
                }
            }

            // 9) Mise à jour finale de l’activité
            $this->executeQuery(
                "UPDATE activities SET
                title             = :title,
                description       = :description,
                short_description = :short_description,
                category_id       = :category_id,
                featured          = :featured,
                main_image        = :main_image
                WHERE id_activity = :id",
                [
                    ':title'             => $data['title'],
                    ':description'       => $data['description'],
                    ':short_description' => $data['short_description'],
                    ':category_id'       => $data['category_id'],
                    ':featured'          => $data['featured'],
                    ':main_image'        => $mainPath,
                    ':id'                => $id
                ]
            );

            return true;
        }

    }
?>