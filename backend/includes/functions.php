<?php
    //Fonctions utilitaires

    // Sécuriser les entrées utilisateur (Empêche XSS & SQL injection)
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Vérifier si un utilisateur est connecté
    function estConnecte() {
        return isset($_SESSION["id_user"]); // Si id_user existe, l'utilisateur est connecté
    }

    // Vérifier si un utilisateur a un rôle spécifique
    function hasRole($role) {
        return isset($_SESSION["role"]) && $_SESSION["role"] === $role;
    }

    // Vérifier si l'utilisateur est super admin
    function estSuperAdmin() {
        return hasRole("super_admin");
    }

    // Redirection sécurisée avec sortie immédiate
    function redirect($url) {
        header("Location: $url");
        exit();
    }

    // Formatage des dates en français
    function formatDate($date) {
        return date("d/m/Y à H:i:s", strtotime($date));
    }

    // Génération de token sécurisé (Utilisé pour CSRF ou authentification avancée)
    function generateToken($length = 32) {
        return base64_encode(random_bytes($length));
    }

    // Récupérer l'ID de l'utilisateur connecté
    function getUserId() {
        return $_SESSION["id_user"] ?? null;
    }

    // Récupérer le rôle de l'utilisateur connecté
    function getUserRole() {
        return $_SESSION["role"] ?? null;
    }

    // Récupérer le nom complet de l'utilisateur
    function getUserFullName() {
        return $_SESSION["full_name"] ?? null;
    }

    // Récupérer le nom d'utilisateur
    function getUsername() {
        return $_SESSION["username"] ?? null;
    }

    // Récupérer l'email de l'utilisateur
    function getUserEmail() {
        return $_SESSION["email"] ?? null;
    }

    // Déconnecter l'utilisateur proprement
    function deconnecter() {
        $_SESSION = []; // Vide les données de session
        session_unset(); // Supprime toutes les variables de session
        session_destroy(); // Détruit complètement la session
        setcookie(session_name(), '', time() - 600, '/'); // Supprime le cookie de session
        redirect("../login.php"); // Redirection vers login.php
    }

    function normalizeText($text) {
        // 🔹 Supprimer les espaces au début et à la fin
        $normalized = trim($text);

        // 🔹 Conserver les caractères accentués
        setlocale(LC_CTYPE, 'fr_FR.UTF-8'); // Assurer un bon encodage UTF-8

        // 🔹 Supprimer les caractères non autorisés (sauf apostrophe, "-", ".")
        $normalized = preg_replace('/[^a-zA-Z0-9À-ÿ\'\-\.\s]/u', '_', $normalized);

        // 🔹 Remplacer les espaces multiples par un seul
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    function getPriority($order) {
        switch ($order) {
            case 1: return "Élevée";
            case 2: return "Moyenne";
            case 3: return "Basse";
            default: return "Inconnue"; // 🔹 Gestion de cas imprévu
        }
    }

    function getStatus($status) {
        switch ($status) {
            case 'ongoing': return 'En cours';
            case 'completed': return 'Terminé';
            case 'upcoming': return 'À venir';
            default: return 'Statut inconnu'; // 🔹 Gestion de cas imprévu
        }
    }

    function getPriorityClass($priority) {
        switch ($priority) {
            case 1: return 'high';
            case 2: return 'medium';
            case 3: return 'low';
            default: return '';
        }
    }

    function getDisplayOrderLabel($order) {
        switch ($order) {
            case 1: return "Élevé";
            case 2: return "Moyen";
            case 3: return "Bas";
            default: return "Inconnu"; // 🔹 Gestion de cas imprévu
        }
    }

    /**
     * Renvoie le chemin de l'image avec un suffixe ?v=<timestamp>
     * pour forcer le rechargement quand le fichier change.
     */
    function bust_cache($relativePath) {
        $fullPath = __DIR__ . '/../' . $relativePath;  // adapte selon ton arborescence
        if (file_exists($fullPath)) {
            return $relativePath . '?v=' . filemtime($fullPath);
        }
        return $relativePath;
    }

    // Helper pour query string (pour Prev/Next)
    function buildPageUrl(int $p): string {
        $qs = array_merge($_GET, ['page' => $p]);
        return '?' . http_build_query($qs);
    }

    // Supprime un dossier et tout son contenu de manière récursive
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        // Récupère tous les fichiers/dossiers du répertoire (en excluant . et ..)
        $files = array_diff(scandir($dir), array('.','..'));

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                // Suppression récursive
                deleteDirectory($path);
            } else {
                // Suppression d'un fichier
                unlink($path);
            }
        }
        // Supprime le dossier lui-même
        return rmdir($dir);
    }
?>