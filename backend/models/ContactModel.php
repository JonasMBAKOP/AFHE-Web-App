<?php
    //Pour gérer les formulaires

    require_once "BaseModel.php"; // Héritage du modèle de base
?> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<?php
    class ContactModel extends BaseModel {
        
        public function getAllContacts() {
            $query = "SELECT * FROM contacts";
            return $this->executeQuery($query)->fetchAll();
        }
        
        public function getContactById($id) {
            $query = "SELECT * FROM contacts WHERE id_contact = :id";
            return $this->executeQuery($query, [":id" => $id])->fetch();
        }
        
        public function createContact($name, $email, $phone, $subject, $message, $ip_address) {
            $query = "INSERT INTO contacts (name, email, phone, subject, message, ip_address) 
                      VALUES (:name, :email, :phone, :subject, :message, :ip_address)";
            
            if (empty($subject) || $subject == "") {
                echo "<script>Swal.fire('Erreur', 'Veuillez sélectionner un objet.', 'error');</script>";
            }
            else{
                return $this->executeQuery($query, [
                    ":name" => $name,
                    ":email" => $email,
                    ":phone" => $phone,
                    ":subject" => $subject,
                    ":message" => $message,
                    ":ip_address" => $ip_address
                ]);
            }
        }

        public function getContacts($sortBy = 'date_sent', $order = 'DESC', $subjectFilter = 'all', $limit = null, $offset = null) {
            // colonnes autorisées
            $valid = ['id','name','email','subject','date_sent'];
            if (!in_array($sortBy, $valid)) {
                $sortBy = 'date_sent';
            }
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            $sql    = "SELECT * FROM contacts WHERE 1=1";
            $params = [];

            // filtre par sujet
            if ($subjectFilter !== 'all') {
                $sql        .= " AND subject = :subject";
                $params[':subject'] = $subjectFilter;
            }

            // ordre et tri
            $sql .= " ORDER BY $sortBy $order";

            // pagination
            if ($limit !== null && $offset !== null) {
                $sql               .= " LIMIT :limit OFFSET :offset";
                $params[':limit']   = $limit;
                $params[':offset']  = $offset;
            }

            $stmt = $this->executeQuery($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function countContacts(string $subjectFilter = 'all'): int {
            $sql    = "SELECT COUNT(*) FROM contacts WHERE 1=1";
            $params = [];

            if ($subjectFilter !== 'all') {
                $sql                 .= " AND subject = :subject";
                $params[':subject']   = $subjectFilter;
            }

            return (int)$this->executeQuery($sql, $params)
                            ->fetchColumn();
        }

        // Compte le nombre total de messages
        public function countAllContacts(): int {
            $sql = "SELECT COUNT(*) FROM contacts";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }

        //  Renvoie tous les sujets distincts pour le filtre
        public function getDistinctSubjects(): array {
            $sql = "SELECT DISTINCT subject FROM contacts ORDER BY subject ASC";
            return $this->executeQuery($sql)
                        ->fetchAll(PDO::FETCH_COLUMN);
        }

        public function deleteContact($id) {
            $query = "DELETE FROM contacts WHERE id_contact = :id";
            return $this->executeQuery($query, [":id" => $id]);
        }
    }
?>