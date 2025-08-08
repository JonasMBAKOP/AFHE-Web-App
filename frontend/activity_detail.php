<?php
    require_once '../backend/includes/db_connect.php';

    // Récupérer et valider l’ID
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        redirect ("activities.php");
    }

    // Charger l’activité + catégorie
    $sql = "SELECT a.title, a.description, a.main_image, c.name AS category
            FROM activities a
            JOIN activity_categories c ON a.category_id = c.id
            WHERE a.id_activity = ?
            LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        redirect ("activities.php");
    }
    $act = $res->fetch_assoc();

    // Charger les images secondaires
    $secSql  = "SELECT image_path FROM activity_images WHERE activity_id = ? ORDER BY id ASC";
    $secStmt = $conn->prepare($secSql);
    $secStmt->bind_param('i', $id);
    $secStmt->execute();
    $secRes = $secStmt->get_result();
    $secondary = [];
    while ($row = $secRes->fetch_assoc()) {
        $secondary[] = $row['image_path'];
    }
?>

<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AFHE | <?php echo htmlspecialchars($act['title']); ?> - Détail de l'activité</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="assets/css/activity_detail.css">
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
                        <img src="assets/images/logo/logo.png" alt="Logo AFHE">
                        <h1>AFHE</h1>
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="activities.php"  class="active">Activités</a></li>
                        <li><a href="projects.php">Projets</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="../backend/admin/login.php" class="btn btn-accent">Admin Login</a></li>
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
        
        <main class="activity-detail">
            <!-- Bouton retour -->
            <div class="back-button">
                <a href="activities.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux activités
                </a>
            </div>

            <!-- En-tête de l'activité -->
            <div class="activity-header">
                <div class="header-content">
                    <h1 class="activity-title"><?php echo htmlspecialchars($act['title']); ?></h1>
                    <div class="activity-category">
                        <i class="fas fa-tag"></i>
                        <span><?php echo htmlspecialchars($act['category']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="activity-content">
                <!-- Section images -->
                <section class="images-section">
                    <div class="main-image-container">
                        <img src="../backend/<?php echo htmlspecialchars($act['main_image']); ?>" 
                            alt="../backend/<?php echo htmlspecialchars($act['title']); ?>" 
                            onclick="openModal(this.src)"
                            class="main-image">
                    </div>

                    <?php if (!empty($secondary)): ?>
                        <div class="secondary-images">
                            <h2><i class="fas fa-images"></i> Galerie d'images</h2>
                            <div class="secondary-grid">
                                <?php foreach ($secondary as $image): ?>
                                    <?php $image = trim($image); ?>
                                    <?php if (!empty($image)): ?>
                                        <div class="secondary-image-item">
                                            <img src="../backend/<?php echo htmlspecialchars($image); ?>" 
                                                alt="Image secondaire" 
                                                onclick="openModal(this.src)">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                

                <!-- Section description -->
                <section class="description-section">
                    <h2><i class="fas fa-info-circle"></i> Description détaillée</h2>
                    <div class="description-content">
                        <p><?php echo nl2br(htmlspecialchars($act['description'])); ?></p>
                    </div>
                </section>

                <!-- Section actions -->
                <!-- <section class="actions-section">
                    <div class="action-buttons">
                        <button class="btn-share" onclick="shareActivity()">
                            <i class="fas fa-share-alt"></i>
                            Partager
                        </button>
                    </div>
                </section> -->
            </div>
        </main>

        <!-- Modal pour l'image agrandie -->
        <div id="imageModal" class="modal">
            <span class="close" onclick="closeModal()">&times;</span>
            <img class="modal-content" id="modalImage">
            <div class="modal-nav">
                <button class="nav-btn prev" onclick="changeImage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-btn next" onclick="changeImage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <?php require_once 'components/footer.php'; ?>
        
        <script src="assets/js/responsive.js"></script>
        <script src="assets/js/components.js"></script>
        <script src="assets/js/main.js"></script>
        <script src="assets/js/activity_detail.js"></script>
    </body>
</html>