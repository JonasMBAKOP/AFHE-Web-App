<?php
    // Pour gérer les projets

    require_once 'BaseModel.php';
    require_once __DIR__ . '/../includes/functions.php'; // Fonctions utiles

    class ProjectModel extends BaseModel {

        // Créer un nouveau projet et Traiter l'upload de ses images
        public function createProject($data, $mainImageFile, $secondaryImageFiles) {
            // Création du dossier pour ce projet en se basant sur le titre
            $projectFolderName = normalizeText($data['title']);
            $projectFolderPath = __DIR__ . "/../uploads/projects/" . $projectFolderName . "/";
            
            if (!is_dir($projectFolderPath)) {
                if (!mkdir($projectFolderPath, 0777, true)) {
                    // Erreur lors de la création du dossier
                    return false;
                }
            }
            
            // Traiter et déplacer l'image principale
            $mainImagePath = "";
            if ($mainImageFile && isset($mainImageFile['error']) && $mainImageFile['error'] === UPLOAD_ERR_OK) {
                $mainImagePath = $this->uploadImage($mainImageFile, $projectFolderName, $projectFolderPath, "main");
            }

            // Insertion dans la table projects
            $query = "INSERT INTO projects (title, description, short_description, status, main_image, priority, created_by, created_at, active)
                    VALUES (:title, :description, :short_description, :status, :main_image, :priority, :created_by, NOW(), :active)";
            $params = [
                ':title'             => $data['title'],
                ':description'       => $data['description'],
                ':short_description' => $data['short_description'],
                ':status'            => $data['status'],
                ':main_image'        => $mainImagePath,
                ':priority'          => $data['priority'],
                ':created_by'        => $data['created_by'],
                ':active'            => $data['active']
            ];
            $this->executeQuery($query, $params);
            
            // Récupérer l'ID du projet créé
            $projectId = $this->pdo->lastInsertId();
            
            // Traitement des images secondaires
            if ($secondaryImageFiles && isset($secondaryImageFiles['name']) && count($secondaryImageFiles['name']) > 0) {
                $filesCount = count($secondaryImageFiles['name']);
                for ($i = 0; $i < $filesCount; $i++) {
                    // Vérifier chaque fichier
                    if ($secondaryImageFiles['error'][$i] === UPLOAD_ERR_OK) {
                        // Reconstituer le tableau du fichier pour cette image
                        $fileInfo = [
                            'name'     => $secondaryImageFiles['name'][$i],
                            'type'     => $secondaryImageFiles['type'][$i],
                            'tmp_name' => $secondaryImageFiles['tmp_name'][$i],
                            'error'    => $secondaryImageFiles['error'][$i],
                            'size'     => $secondaryImageFiles['size'][$i]
                        ];
                        $secondaryImagePath = $this->uploadImage($fileInfo, $projectFolderName, $projectFolderPath, "secondary_" . ($i + 1));
                        
                        // Insérer dans la table projects_images
                        $queryImg = "INSERT INTO project_images (project_id, image_path, caption, display_order) VALUES (:project_id, :image_path, :caption, :display_order)";
                        $paramsImg = [
                            ':project_id'   => $projectId,
                            ':image_path'   => $secondaryImagePath,
                            ':caption'      => '',        // Laisser vide ou ajouter un champ de saisie dans le formulaire si besoin
                            ':display_order'=> ($i + 1)   // Ordre d'affichage (1,2,3,…)
                        ];
                        $this->executeQuery($queryImg, $paramsImg);
                    }
                }
            }
            
            return $projectId;
        }

        
        // Gère l'upload d'une image.
        public function uploadImage($imageFile, $projectTitle, $folder, $radical) {
            // Extraire l'extension du fichier
            $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            // Créer un nom unique pour le fichier
            $filename = $projectTitle . ' - ' . $radical . '.' . $extension;
            $destination = $folder . $filename;

            if (move_uploaded_file($imageFile['tmp_name'], $destination)) {
                return "uploads/projects/" . $projectTitle . "/" . $filename; // Chemin relatif (par rapport à la racine du site)
            }
            return "";
        }


        // Récupérer tous les projets avec filtre, tri et pagination
        public function getProjects($filters = [], $sortField = 'created_at', $sortOrder = 'DESC', $limit = 10, $offset = 0) {
            $query = "SELECT * FROM projects WHERE 1";
            $params = [];

            // Filtrer par statut (ex: 'upcoming', 'ongoing', 'completed')
            if (!empty($filters['status'])) {
                $query .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }

            // Filtrer par priorité
            if (!empty($filters['priority'])) {
                $query .= " AND priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            // Filtrer par créé par
            if (!empty($filters['created_by'])) {
                $query .= " AND created_by = :created_by";
                $params[':created_by'] = $filters['created_by'];
            }

            // Filtrer par date de création : intervalle (date de début et/ou date de fin)
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query .= " AND DATE(created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date']   = $filters['end_date'];
            } elseif (!empty($filters['start_date'])) {
                $query .= " AND DATE(created_at) >= :start_date";
                $params[':start_date'] = $filters['start_date'];
            } elseif (!empty($filters['end_date'])) {
                $query .= " AND DATE(created_at) <= :end_date";
                $params[':end_date'] = $filters['end_date'];
            }

            // Filtrer par actif (0 ou 1)
            if ($filters['active'] !== '') {  // vérifier même si 0 est fourni
                $query .= " AND active = :active";
                $params[':active'] = $filters['active'];
            }

            // Validation du champ de tri
            $allowedSortFields = ['title', 'created_at'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            $sortOrder = strtoupper($sortOrder);
            if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
                $sortOrder = 'DESC';
            }

            $query .= " ORDER BY $sortField $sortOrder LIMIT :limit OFFSET :offset";
            // On ajoute les paramètres de pagination
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            $stmt = $this->executeQuery($query, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


        // Compter le nombre de projets selon les filtres
        public function countProjects($filters = []) {
            $query = "SELECT COUNT(*) as total FROM projects WHERE 1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $query .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $query .= " AND priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            if (!empty($filters['created_by'])) {
                $query .= " AND created_by = :created_by";
                $params[':created_by'] = $filters['created_by'];
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query .= " AND DATE(created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date']   = $filters['end_date'];
            }
            elseif (!empty($filters['start_date'])) {
                $query .= " AND DATE(created_at) >= :start_date";
                $params[':start_date'] = $filters['start_date'];
            }
            elseif (!empty($filters['end_date'])) {
                $query .= " AND DATE(created_at) <= :end_date";
                $params[':end_date'] = $filters['end_date'];
            }

            if ($filters['active'] !== '') {
                $query .= " AND active = :active";
                $params[':active'] = $filters['active'];
            }
            
            $stmt = $this->executeQuery($query, $params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        }


        // Compte le nombre total de projets
        public function countAllProjects(): int{
            $sql = "SELECT COUNT(*)
                    FROM projects";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }


        // Récupère les informations complètes d'un projet
        public function getProjectById($projectId) {
            $query = "SELECT * FROM projects WHERE id_project = :id";
            $stmt = $this->executeQuery($query, [':id' => $projectId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }


        // Récupère toutes les images secondaires d'un projet
        public function getProjectImages($projectId) {
            $query = "SELECT * FROM project_images WHERE project_id = :project_id ORDER BY display_order ASC";
            $stmt = $this->executeQuery($query, [':project_id' => $projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


        // Récupérer une image secondaire d'un projet par son ID
        public function getProjectImageById($id) {
            $query = "SELECT * FROM project_images WHERE id = :id";
            $stmt = $this->executeQuery($query, [':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }


        // Mettre à jour un projet
        public function updateProject ($id, $data, $mainImageFile, $secondaryImageFiles, $deleteSecondaryIds = []) {
            // 1) Récupérer l'ancien projet
            $project = $this->getProjectById($id);
            if (!$project) {
                return false;
            }

            // 2) Chemin racine absolu de l'appli
            $root = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

            // 3) Ancien et nouveau titre + slug
            $oldTitle     = $project['title'];
            $newTitle     = $data['title'];
            $titleChanged = ($oldTitle !== $newTitle);
            $slug         = normalizeText($newTitle);

            // 4) Dossiers relatifs et absolus avant et après
            $oldRelDir = dirname($project['main_image']); // ex: "uploads/projects/essai"
            $oldAbsDir = $root . str_replace('/', DIRECTORY_SEPARATOR, $oldRelDir);
            $newRelDir  = "uploads/projects/{$slug}";
            $newAbsDir  = $root . str_replace('/', DIRECTORY_SEPARATOR, $newRelDir);

            // 5) Renommer le dossier si le titre a changé
            if ($titleChanged && is_dir($oldAbsDir)) {
                rename($oldAbsDir, $newAbsDir);
            } else {
                // Sinon on reste sur l'ancien dossier
                $newRelDir = $oldRelDir;
                $newAbsDir = $oldAbsDir;
            }

            // 6) Supprimer les secondaires cochées (fichiers + BD)
            if (!empty($deleteSecondaryIds)) {
                foreach ($deleteSecondaryIds as $secId) {
                    $img = $this->getProjectImageById($secId);
                    if ($img) {
                        @unlink($root . $img['image_path']);
                        $this->executeQuery("DELETE FROM project_images WHERE id = :id", [':id' => $secId]);
                    }
                }
            }

            // 7) Détecter si on a uploadé de nouvelles images
            $mainUploaded = isset($mainImageFile['error']) 
                        && $mainImageFile['error'] === UPLOAD_ERR_OK;
            $secUploaded  = false;
            if ($secondaryImageFiles && isset($secondaryImageFiles['error'])) {
                foreach ($secondaryImageFiles['error'] as $e) {
                    if ($e === UPLOAD_ERR_OK) {
                        $secUploaded = true;
                        break;
                    }
                }
            }

            // 8) Gestion de l'image principale
            if ($mainUploaded) {
                // Supprimer l'ancienne
                @unlink($root . $project['main_image']);
                // Uploader la nouvelle
                $relMain = $this->uploadImage($mainImageFile, $slug, $newAbsDir . '/', 'main');
                // Conserver le chemin relatif en base
                $data['main_image'] = $relMain;
            }
            elseif ($titleChanged) {
                // Renommer physiquement l'image principale existante pour refléter le nouveau slug
                $oldName = basename($project['main_image']);
                $oldAbsMain = $newAbsDir . DIRECTORY_SEPARATOR . $oldName;
                if (file_exists($oldAbsMain)) {
                    $ext      = pathinfo($oldAbsMain, PATHINFO_EXTENSION);
                    $newName  = $slug . ' - main.' . $ext;
                    $newAbsMain = $newAbsDir . DIRECTORY_SEPARATOR . $newName;
                    rename($oldAbsMain, $newAbsMain);
                    $data['main_image'] = "{$newRelDir}/{$newName}"; // Chemin relatif
                } else {
                    // Pas de fichier à renommer
                    $data['main_image'] = $project['main_image'];
                }
            } else {
                // Ni renommage ni nouvel upload => on garde l'ancien chemin
                $data['main_image'] = $project['main_image'];
            }


            // 9) Renommer et ré-indexer TOUTES les images secondaires restantes
            $stmt = $this->executeQuery(
                "SELECT id, image_path FROM project_images WHERE project_id = :pid ORDER BY display_order ASC",
                [':pid' => $id]
            );
            $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $index = 0;
            foreach ($remaining as $img) {
                $index++;
                $oldName = basename($img['image_path']);
                // $oldRel = $img['image_path'];
                $oldAbs = $newAbsDir . DIRECTORY_SEPARATOR . $oldName;
                if (!file_exists($oldAbs)) {
                    continue;
                }
                // Nouveaux noms et chemins
                $ext       = pathinfo($oldAbs, PATHINFO_EXTENSION);
                $newName   = "{$slug} - secondary_{$index}.{$ext}";
                $newRel    = "{$newRelDir}/{$newName}";
                $newAbs    = $newAbsDir . DIRECTORY_SEPARATOR . $newName;
                rename($oldAbs, $newAbs);

                $this->executeQuery(
                    "UPDATE project_images
                        SET image_path = :path, display_order = :ord
                      WHERE id = :id",
                    [
                        ':path' => $newRel,
                        ':ord'     => $index,
                        ':id' => $img['id'],
                    ]
                );
            }

            // 10) Nbre actuel d'images secondaires
            $row = $this->executeQuery(
                "SELECT COUNT(*) as total FROM project_images WHERE project_id = :pid",
                [':pid' => $id]
            )->fetch(PDO::FETCH_ASSOC);
            $existingCount = (int)$row['total'];

            // 11) Ajouter les nouvelles images secondaires après le dernier index
            if ($secondaryImageFiles && !empty($secondaryImageFiles['name'])) {
                for ($i = 0; $i < count($secondaryImageFiles['name']); $i++) {
                    if ($secondaryImageFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $newIdx = $existingCount + $i + 1;
                        $file = [
                            'name'     => $secondaryImageFiles['name'][$i],
                            'type'     => $secondaryImageFiles['type'][$i],
                            'tmp_name' => $secondaryImageFiles['tmp_name'][$i],
                            'error'    => $secondaryImageFiles['error'][$i],
                            'size'     => $secondaryImageFiles['size'][$i],
                        ];
                        $relSec  = $this->uploadImage(
                            $file,
                            $slug,
                            $newAbsDir . '/',
                            'secondary_' . $newIdx
                        );
                        $this->executeQuery(
                            "INSERT INTO project_images (project_id, image_path, caption, display_order)
                            VALUES (:pid, :path, '', :ord)",
                            [
                                ':pid'  => $id,
                                ':path' => $relSec,
                                ':ord'  => $newIdx
                            ]
                        );
                    }
                }
            }

            // 12) Mettre à jour le reste des champs du projet
            $this->executeQuery(
                "UPDATE projects SET
                    title             = :title,
                    description       = :description,
                    short_description = :short_description,
                    status            = :status,
                    main_image        = :main_image,
                    priority          = :priority,
                    active            = :active
                WHERE id_project = :id",
                [
                    ':title'             => $data['title'],
                    ':description'       => $data['description'],
                    ':short_description' => $data['short_description'],
                    ':status'            => $data['status'],
                    ':main_image'        => $data['main_image'],
                    ':priority'          => $data['priority'],
                    ':active'            => $data['active'],
                    ':id'                => $id
                ]
            );

            return true;
        }


        // // Supprimer un dossier et tout son contenu de manière récursive.
        // public function deleteDirectory($dir) {
        //     if (!is_dir($dir)) {
        //         return false;
        //     }

        //     // Récupère tous les fichiers/dossiers du répertoire (en excluant . et ..)
        //     $files = array_diff(scandir($dir), array('.','..'));

        //     foreach ($files as $file) {
        //         $path = $dir . DIRECTORY_SEPARATOR . $file;
        //         if (is_dir($path)) {
        //             // Suppression récursive
        //             $this->deleteDirectory($path);
        //         } else {
        //             // Suppression d'un fichier
        //             unlink($path);
        //         }
        //     }
        //     // Supprime le dossier lui-même
        //     return rmdir($dir);
        // }
        

        // Supprime un projet, ses images (principale et secondaires) et le dossier associé
        public function deleteProject($id) {
            // Récupère le projet via son ID
            $project = $this->getProjectById($id);
            if (!$project) {
                return false;
            }

            // Détermine le dossier du projet. 
            $relativeFolder = dirname($project['main_image']);
            $folderPath = "../../" . $relativeFolder; // Chemin complet du dossier

            // Supprime le dossier et son contenu (si le dossier existe)
            if (is_dir($folderPath)) {
                deleteDirectory($folderPath);
            }

            // Supprime d'abord les enregistrements des images secondaires dans la table project_images
            $queryImages = "DELETE FROM project_images WHERE project_id = :project_id";
            $this->executeQuery($queryImages, [':project_id' => $id]);

            // Enfin, supprime le projet de la table projects
            $queryProject = "DELETE FROM projects WHERE id_project = :id";
            return $this->executeQuery($queryProject, [':id' => $id]);
        }


        // Vérifie si une catégorie existe déjà (même nom).
        public function projectExists(string $title): bool
        {
            $stmt = $this->executeQuery(
                "SELECT COUNT(*) AS cnt
                FROM projects
                WHERE title = :title",
                [':title' => $title]
            );
            $row = $stmt->fetch();
            return ((int)$row['cnt'] > 0);
        }

    }
?>