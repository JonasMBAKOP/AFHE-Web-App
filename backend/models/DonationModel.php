<?php
    //Pour gérer les dons

    require_once "BaseModel.php"; // Héritage du modèle de base

    class DonationModel extends BaseModel {

        /** 🔹 Ajouter un don **/
        public function addDonation($donor_name, $donor_phone, $donor_email, $status, $transaction_id, $amount, $payment_method, $currency, $is_anonymous, $message, $created_by) {
            $query = "INSERT INTO donations (donor_name, donor_phone, donor_email, status, transaction_id, amount, payment_method, currency, is_anonymous, message, created_by)
                    VALUES (:donor_name, :donor_phone, :donor_email, :status, :transaction_id, :amount, :payment_method, :currency, :is_anonymous, :message, :created_by)";
            $params = [
                ':donor_name' => $donor_name,
                ':donor_phone' => $donor_phone,
                ':donor_email' => $donor_email,
                ':status' => $status,
                ':transaction_id' => $transaction_id,
                ':amount' => $amount,
                ':payment_method' => $payment_method,
                ':currency' => $currency,
                ':is_anonymous' => $is_anonymous,
                ':message' => $message,
                ':created_by' => $created_by
            ];
            return $this->executeQuery($query, $params);
        }

        /** 🔹 Récupérer un don spécifique **/
        public function getDonationById($id) {
            $query = "SELECT * FROM donations WHERE id_donation = :id";
            $params = [':id' => $id];
            return $this->executeQuery($query, $params)->fetch();
        }
        
        /** 🔹 Récupérer les dons avec options de filtre et tri **/
        public function getDonations($sortBy = 'created_at', $order = 'DESC', $filters = [], $limit = 10, $offset = 0) {
            $allowedSorts = ['created_at', 'amount', 'donor_name'];
            $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            // Filtres autorisés
            $allowedFilters = [
                'currency',
                'payment_method',
                'status',
                'is_anonymous',
                'created_by'
            ];
            $where = [];
            $params = [];

            foreach ($filters as $key => $value) {
                if (in_array($key, $allowedFilters) && $value !== 'all') {
                    $where[] = "$key = :$key";
                    // cast booléen
                    $params[":$key"] = $key === 'is_anonymous'
                        ? ((int)$value)
                        : $value;
                }
            }

            $sql = "SELECT * FROM donations"
                 . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "")
                 . " ORDER BY $sortBy $order
            ";

            // pagination
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $params[':limit']  = $limit;
                $params[':offset'] = $offset;
            }

            return $this -> executeQuery($sql, $params) -> fetchAll(PDO::FETCH_ASSOC);
        }


        /** 🔹 Renvoie la liste des valeurs distinctes d'une colonne **/
        public function getDistinct(string $column): array {
            $sql = "SELECT DISTINCT `$column` FROM donations ORDER BY `$column` ASC";
            return $this->executeQuery($sql)
                        ->fetchAll(PDO::FETCH_COLUMN);
        }


        /** 🔹 Compter le nombre de projets selon les filtres **/
        public function countDonations(array $filters = []): int {
            $allowed = ['currency','payment_method','status','is_anonymous','created_by'];
            $where   = [];
            $params = [];

            foreach ($filters as $k => $v) {
                if (in_array($k, $allowed) && $v !== 'all') {
                    $where[] = "$k = :$k";
                    $params[":$k"] = $k==='is_anonymous' ? (int)$v : $v;
                }
            }
            $sql = "SELECT COUNT(*) FROM donations"
                . (!empty($where) ? " WHERE ".implode(' AND ',$where) : "");
            return (int) $this->executeQuery($sql, $params)
                            ->fetchColumn();
        }


        // Compte le nombre total de dons
        public function countAllDonations(): int{
            $sql = "SELECT COUNT(*)
                    FROM donations";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }


        /** 🔹 Mettre à jour un don **/
        public function updateDonation($id, $donor_name, $donor_email, $donor_phone, $amount, $status, $payment_method, $transaction_id, $currency, $is_anonymous, $message) {
            $query = "UPDATE donations
                    SET donor_name = :donor_name, donor_email = :donor_email, donor_phone = :donor_phone, amount = :amount, status = :status, payment_method = :payment_method, transaction_id = :transaction_id, currency = :currency, is_anonymous = :is_anonymous, message = :message
                    WHERE id_donation = :id";
            $params = [
                ':id' => (int) $id,
                ':donor_name' => $donor_name,
                ':donor_email' => $donor_email,
                ':donor_phone' => $donor_phone,
                ':amount' => (float) $amount,
                ':status' => $status,
                ':payment_method' => $payment_method,
                ':transaction_id' => $transaction_id,
                ':currency' => $currency,
                ':is_anonymous' => $is_anonymous,
                ':message' => $message
            ];
            return $this->executeQuery($query, $params);
        }

        /** 🔹 Supprimer un don **/
        public function deleteDonation($id) {
            $query = "DELETE FROM donations WHERE id_donation = :id";
            $params = [':id' => $id];
            return $this->executeQuery($query, $params);
        }
    }
?>