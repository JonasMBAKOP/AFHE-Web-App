<?php
    //  Classe parent pour les modèles de la base de données
    
    class BaseModel {
        protected $pdo;

        public function __construct() {
            global $pdo;
            $this->pdo = $pdo;
        }

        protected function executeQuery($query, $params = []) {
            $stmt = $this->pdo->prepare($query);

            foreach ($params as $key => &$value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR; // Détection automatique du type
                $stmt->bindParam($key, $value, $type);
            }

            $stmt->execute();
            return $stmt;
        }
    //     protected function executeQuery(string $sql, array $params = []): PDOStatement {
    //         try {
    //             $stmt = $this->pdo->prepare($sql);
    //             $stmt->execute($params);
    //             return $stmt;
    //         }
    //         catch (\PDOException $e) {
    //             // dump SQL + params et bloquer l’exécution
    //             echo "<pre style='color:red'>";
    //             echo "❌ SQL ERROR: " . $e->getMessage() . "\n\n";
    //             echo "---------- QUERY ----------\n$sql\n";
    //             echo "-------- PARAMETERS --------\n" . print_r($params, true);
    //             echo "</pre>";
    //             exit;
    //         }
    //     }
    // }

        
    // require_once "../includes/db_connect.php"; // Connexion à la base

    // class BaseModel {
    //     protected $pdo;

    //     public function __construct() {
    //         global $pdo;
    //         $this->pdo = $pdo;
    //     }

    //     protected function executeQuery($query, $params = []) {
    //         $stmt = $this->pdo->prepare($query);
    //         $stmt->execute($params);
    //         return $stmt;
    //     }
    }

?>
