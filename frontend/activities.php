<?php
    require_once '../backend/includes/db_connect.php';

    // Récupérer la liste des catégories
    $catRes = $conn->query("SELECT id, name FROM activity_categories ORDER BY name ASC");
    $categories = [];
    while ($c = $catRes->fetch_assoc()) {
        $categories[(int)$c['id']] = $c['name'];
    }

    // Filtre par catégorie
    $catParam      = $_GET['category'] ?? 'all';
    $categoryFilter = 'all';
    if ($catParam !== 'all') {
        $id = (int)$catParam;
        if (isset($categories[$id])) {
            $categoryFilter = $id;
        }
    }

    // Pagination
    $perPage     = 8;
    $currentPage = max(1, intval($_GET['page'] ?? 1));

    // Compter le total
    $countSql = "SELECT COUNT(*) AS total
                FROM activities
                WHERE 1 = 1" . ($categoryFilter !== 'all' ? " AND category_id = $categoryFilter" : '');
    $countRes   = $conn->query($countSql);
    $totalItems = (int)$countRes->fetch_assoc()['total'];
    $totalPages = (int)ceil($totalItems / $perPage);

    // Charger les activités de la page
    $offset = ($currentPage - 1) * $perPage;
    $dataSql = "SELECT id_activity, main_image, title, short_description, category_id
                FROM activities
                WHERE 1 = 1" . ($categoryFilter !== 'all' ? " AND category_id = $categoryFilter" : '') . 
                " ORDER BY title ASC
                LIMIT $offset, $perPage";
    $result = $conn->query($dataSql);

    // Fonction de pagination (même principe que projects.php)
    function renderPagination(int $cur, int $total): string {
        $html = '<nav class="pagination">';
        for ($p = 1; $p <= $total; $p++) {
            $cls = $p === $cur ? ' current' : '';
            // Conserver le filtre catégorie
            $qs = http_build_query([
                'category' => $_GET['category'] ?? 'all',
                'page'     => $p
            ]);
            $html .= "<a href=\"?{$qs}\" class=\"page{$cls}\">$p</a>";
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
        <title>Activités | AFHE</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    </head>
    <body>
        <?php
            // Définir le nom de la page
            $_GET['page'] = "Activités";
            include '../backend/admin/reports/updateVisits.php';
        ?>

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
                        <li><a href="activities.php"  class="active">Activités</a></li>
                        <li><a href="projects.php">Projets</a></li>
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

        <main class="activities-page-wrapper">
            <h2>Nos Activités</h2><hr>

            <!-- Filtre -->
            <div class="filter-container">
                <label for="categoryFilter">Catégorie :</label>
                <select id="categoryFilter">
                    <option value="all" <?= $categoryFilter==='all'? 'selected':'' ?>>Toutes</option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $categoryFilter===$id? 'selected':'' ?>>
                            <?= htmlspecialchars($name, ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Grille des activités -->
            <div class="activities-container">
                <?php if ($result && $result->num_rows): ?>
                    <?php while ($act = $result->fetch_assoc()): ?>
                        <div class="activity-card">
                            <img
                                src="../backend/<?= htmlspecialchars($act['main_image'], ENT_QUOTES) ?>"
                                alt="<?= htmlspecialchars($act['title'], ENT_QUOTES) ?>"
                            >
                            <h3><?= htmlspecialchars($act['title'], ENT_QUOTES) ?></h3>
                            <p>
                                <?= htmlspecialchars(
                                    mb_strimwidth($act['short_description'], 0, 100, '…'),
                                    ENT_QUOTES
                                ) ?>
                            </p>
                            <a href="activity_detail.php?id=<?= (int)$act['id_activity'] ?>"
                                class="btn-view">
                                Voir
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucune activité disponible.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination bas -->
            <?= renderPagination($currentPage, $totalPages) ?>
        </main>


        <!-- Footer -->
        <?php require_once 'components/footer.php'; ?>
        
        <script src="assets/js/responsive.js"></script>
        <script src="assets/js/components.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>