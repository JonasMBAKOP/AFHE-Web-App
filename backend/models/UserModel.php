<?php
    //Pour gérer les utilisateurs admin

    require_once "BaseModel.php"; // Héritage du modèle de base

    class UserModel extends BaseModel {
        
        public function getAllUsers() {
            $query = "SELECT * FROM users";
            return $this->executeQuery($query)->fetchAll();
        }

        public function getAdmins() {
            $query = "SELECT id_user, username FROM users WHERE role IN ('admin','super_admin') ORDER BY username ASC";
            return $this->executeQuery($query)->fetchAll();
        }
        
        public function getUserById($id) {
            $query = "SELECT * FROM users WHERE id_user = :id";
            return $this->executeQuery($query, [":id" => $id])->fetch();
        }
        
        public function getUserByEmail($email) {
            $query = "SELECT * FROM users WHERE email = :email";
            return $this->executeQuery($query, [":email" => $email])->fetch();
        }

        // Compte le nombre total d'administrateurs
        public function countAllUsers(): int{
            $sql = "SELECT COUNT(*)
                    FROM users";
            $stmt = $this->executeQuery($sql);
            return (int)$stmt->fetchColumn();
        }

        public function createUser($username, $email, $password, $full_name, $role) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (username, email, password, full_name, role, profile_image, active) 
                    VALUES (:username, :email, :password, :full_name, :role, '', 1)";
            
            return $this->executeQuery($query, [
                ":username" => $username,
                ":email" => $email,
                ":password" => $hashed_password,
                ":full_name" => $full_name,
                ":role" => $role
            ]);
        }

        public function updateUser($id, $username, $email, $full_name, $role, $password, $active) {
            $query = "UPDATE users
                      SET username = :username, email = :email, full_name = :full_name, role = :role, active = :active";
            $params = [
                ":id" => $id,
                ":username" => $username,
                ":email" => $email,
                ":full_name" => $full_name,
                ":role" => $role,
                ":active" => $active
            ];

            if(!empty($password)) {  //Si on saisit un nouveau mot de passe
                $query .= ", password = :password";
                $params[":password"] = password_hash($password, PASSWORD_BCRYPT); // Hachage du mot de passe
            }

            $query .= " WHERE id_user = :id";
            
            return $this->executeQuery($query, $params);
        }

        public function deleteUser($id) {
            $query = "DELETE FROM users WHERE id_user = :id";
            return $this->executeQuery($query, [":id" => $id]);
        }

    }
?>