<?php
    require_once __DIR__ . '/../backend/includes/db_connect.php';

    // Récupération du filtre
    $validStatuses = ['completed', 'ongoing', 'upcoming'];
    $status = $_GET['status'] ?? 'all';
    if (!in_array($status, $validStatuses) && $status !== 'all') {
        $status = 'all';
    }
    // Paramètres de pagination
    $perPage     = 9;
    $currentPage = max(1, intval($_GET['page'] ?? 1));

    // Comptage total des projets actifs (et filtrés)
    $countSql = "SELECT COUNT(*) AS total
                FROM projects
                WHERE active = 1" . ($status!=='all' ? " AND status = '" . $conn->real_escape_string($status) . "'" : '');
    $countRes   = $conn->query($countSql);
    $totalItems = (int)$countRes->fetch_assoc()['total'];
    $totalPages = (int)ceil($totalItems / $perPage);

    // Récupération des projets pour la page
    $offset = ($currentPage - 1) * $perPage;
    $dataSql = "SELECT id_project, main_image, title, short_description, status
                FROM projects
                WHERE active = 1" . ($status!=='all' ? " AND status = '" . $conn->real_escape_string($status) . "'" : '') .
                " ORDER BY title ASC
                LIMIT $offset, $perPage";
    $result = $conn->query($dataSql);

    // Fonction de rendu de pagination pour l'interface utilisateur
    function renderPagination(int $current, int $total): string {
        $html = '<nav class="pagination">';
        for ($p = 1; $p <= $total; $p++) {
            $active = $p === $current ? ' current' : '';
            // Conserve le filtre de statut dans l’URL
            $qs = http_build_query([
                'status' => $_GET['status'] ?? 'all',
                'page'   => $p
            ]);
            $html .= "<a href=\"?{$qs}\" class=\"page{$active}\">$p</a>";
        }
        $html .= '</nav>';
        return $html;
    }
?>

<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Projets | AFHE</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    </head>
    <body>
        <?php
            // Définir le nom de la page
            $_GET['page'] = "Projets";
            include '../backend/admin/reports/updateVisits.php';
        ?>

        <!-- Header -->
        <header>
            <div class="container header-container">
                <div class="logo">
                    <a href="index.php">
                        <div class="header-logo">
                            <img src="assets/images/logo/logo.png" alt="Logo AFHE">
                        </div>
                        <h1>AFHE</h1>
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="activities.php">Activités</a></li>
                        <li><a href="projects.php" class="active">Projets</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <!-- <li><a href="../backend/admin/login.php" class="btn btn-accent">Admin Login</a></li> -->
                    </ul>
                </nav>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </header>

        <br><br><br><br>
        
        <main class="projects-page-wrapper">
            <h2>Nos Projets</h2><hr>

            <!-- Filtre par statut -->
            <div class="filter-container">
                <label for="statusFilter">Filtrer par Statut :</label>
                <select id="statusFilter">
                    <option value="all"   <?= $status==='all'   ? 'selected' : '' ?>>Tous</option>
                    <option value="completed" <?= $status==='completed' ? 'selected' : '' ?>>Terminés</option>
                    <option value="ongoing"   <?= $status==='ongoing'   ? 'selected' : '' ?>>En cours</option>
                    <option value="upcoming"  <?= $status==='upcoming'  ? 'selected' : '' ?>>À venir</option>
                </select>
            </div>

            <!-- Grille des projets -->
            <div class="projects-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($proj = $result->fetch_assoc()): ?>
                        <div class="project-card" data-status="<?= htmlspecialchars($proj['status'], ENT_QUOTES) ?>">
                            <img
                                src="../backend/<?= htmlspecialchars($proj['main_image'], ENT_QUOTES) ?>"
                                alt="<?= htmlspecialchars($proj['title'], ENT_QUOTES) ?>"
                            >
                            <h3><?= htmlspecialchars($proj['title'], ENT_QUOTES) ?></h3>
                            <p><?= htmlspecialchars(mb_strimwidth($proj['short_description'], 0, 100, '…'), ENT_QUOTES) ?></p>
                            <a href="project_detail.php?id=<?= (int)$proj['id_project'] ?>" class="btn-view">Voir</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucun projet disponible pour ce statut.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination en bas -->
            <?= renderPagination($currentPage, $totalPages) ?>
        </main>

        <!-- Footer -->
        <?php require_once 'components/footer.php'; ?>
        
        <script src="assets/js/responsive.js"></script>
        <script src="assets/js/components.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>