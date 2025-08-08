<?php
    // Modèle pour les rapports statistiques

    require_once "BaseModel.php";

    class ReportModel extends BaseModel {

        public function getStats($pageName = null, $startDate = null, $endDate = null, $minVisits = null, $minUniqueVisitors = null, $sortBy = 'visit_date', $order = 'DESC', $limit = 10, $page = 1) {
            // Définir les colonnes autorisées pour le tri
            $allowedSorts = ['visit_date', 'visit_count', 'page_name', 'unique_visitors'];

            // Valider que la colonne passée est autorisée, sinon on utilise 'visit_date'
            $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'visit_date';
            
            // On force l'ordre à être soit ASC soit DESC (par défaut DESC)
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            $conditions = [];
            $params = [];

            // 🔹 Filtrage par nom de page
            if (!empty($pageName)) {
                $conditions[] = "page_name = :page_name";
                $params[':page_name'] = $pageName;
            }

            // 🔹 Filtrage par plage de dates
            if (!empty($startDate)) {
                $conditions[] = "visit_date >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $conditions[] = "visit_date <= :end_date";
                $params[':end_date'] = $endDate;
            }

            // 🔹 Filtrage par minimum de visites
            if (!empty($minVisits)) {
                $conditions[] = "visit_count >= :min_visits";
                $params[':min_visits'] = $minVisits;
            }

            // 🔹 Filtrage par visiteurs uniques
            if (!empty($minUniqueVisitors)) {
                $conditions[] = "unique_visitors >= :min_unique_visitors";
                $params[':min_unique_visitors'] = $minUniqueVisitors;
            }

            // 🔹 Construire la requête
            $query = "SELECT * FROM site_stats";
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            $query .= " ORDER BY $sortBy $order";

            // 🔹 Gestion de la pagination
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;

            // 🔹 Exécuter la requête et retourner les résultats
            return $this->executeQuery($query, $params)->fetchAll();
        }

        public function recordVisit($pageName, $unique_increment = 0) {
            // Utilise CURRENT_DATE pour la date et la clause ON DUPLICATE KEY UPDATE
            // La requête insère ou met à jour l'enregistrement pour la date courante
            $query = "INSERT INTO site_stats (page_name, visit_date, visit_count, unique_visitors)
                    VALUES (:page_name, CURRENT_DATE, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                        visit_count = visit_count + 1,
                        unique_visitors = unique_visitors + :unique_increment";

            $params = [
                ':page_name' => $pageName,
                ':unique_increment' => $unique_increment
            ];
            return $this->executeQuery($query, $params);
        }

        public function getTotalPages($limit) {
            // 🔹 Récupérer le nombre total de lignes dans la base
            $query = "SELECT COUNT(*) AS total FROM site_stats";
            $totalRows = $this->executeQuery($query)->fetch()['total'];

            // 🔹 Calcul du nombre total de pages
            return ceil($totalRows / $limit);
        }

        // Compte le nombre total de visites
        public function countAllVisits(): int{
            $sql = "SELECT COUNT(*)
                    FROM site_stats";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }

        public function countTotalVisitCount(): int {
            $sql  = "SELECT SUM(visit_count) AS total FROM site_stats";
            $stmt = $this->executeQuery($sql);
            return (int) $stmt->fetchColumn();
        }

        public function countTotalUniqueVisitors(): int {
            $sql  = "SELECT SUM(unique_visitors) AS total FROM site_stats";
            $stmt = $this->executeQuery($sql);
            return (int) $stmt->fetchColumn();
        }

        // Récupère les visites par date dans une plage donnée
        // Retourne un tableau associatif avec la date et le nombre de visites
        public function getVisitsByDate(string $start, string $end): array {
            $sql = "
                SELECT visit_date AS date, SUM(visit_count) AS visits
                FROM site_stats
                WHERE visit_date BETWEEN :start AND :end
                GROUP BY visit_date
                ORDER BY visit_date
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':start'=>$start,':end'=>$end]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


        public function hasNextPage($page, $limit) {
            $offset = $page * $limit; // Commence après la dernière ligne affichée
            $query = "SELECT COUNT(*) AS total FROM site_stats LIMIT 1 OFFSET :offset";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT); // 🔹 Sécuriser la liaison du paramètre en entier
            $stmt->execute();

            $nextPageCount = $stmt->fetch()['total'];

            return $nextPageCount > 0; // Retourne vrai si la prochaine page contient des résultats
        }
    }
?>