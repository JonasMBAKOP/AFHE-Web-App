<?php
    require_once __DIR__ . '/../backend/includes/db_connect.php';

    // Récupérer et valider l’ID
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        redirect ("projects.php");
    }

     // Charger le projet
    $sql = "SELECT title, description, main_image, status
            FROM projects
            WHERE id_project = $id
            AND active = 1
            LIMIT 1";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows === 0) {
        redirect ('projects.php');
    }
    $project = $res->fetch_assoc();

    // Charger les images secondaires
    $secRes = $conn->query(
        "SELECT image_path
        FROM project_images
        WHERE project_id = $id
        ORDER BY display_order ASC"
    );
    $secondaryImages = [];
    while ($row = $secRes->fetch_assoc()) {
        $secondaryImages[] = $row['image_path'];
    }

    function getStatusColor($status) {
        switch(strtolower($status)) {
            case 'upcoming': return '#ff69b4'; // Rose bonbon
            case 'ongoing': return '#1e90ff'; // Bleu dodger
            case 'completed': return '#32cd32'; // Vert
            default: return '#666';
        }
    }

    // Fonction pour traduire le statut
    function translateStatus($status) {
        switch(strtolower($status)) {
            case 'upcoming': return 'À venir';
            case 'ongoing': return 'En cours';
            case 'completed': return 'Terminé';
            default: return ucfirst($status);
        }
    }
?>

<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AFHE | <?php echo htmlspecialchars($project['title']); ?> - Détails du Projet</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="assets/css/project_detail.css">
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
        
        <main class="project-detail-page">
            <!-- Bouton retour -->
            <div class="back-button">
                <a href="projects.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour aux projets
                </a>
            </div>

            <!-- En-tête du projet -->
            <div class="project-header">
                <div class="project-title-section">
                    <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
                    <div class="project-status" style="background-color: <?php echo getStatusColor($project['status']); ?>">
                        <i class="fas fa-circle"></i>
                        <?php echo translateStatus($project['status']); ?>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="project-content">
                <!-- Image principale -->
                <div class="main-image-container">
                    <?php if (!empty($project['main_image'])): ?>
                        <img src="../backend/<?php echo htmlspecialchars($project['main_image']); ?>" 
                            alt="<?php echo htmlspecialchars($project['title']); ?>"
                            onclick="openModal(this.src)" 
                            class="main-image">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>Aucune image disponible</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="description-section">
                    <h2><i class="fas fa-info-circle"></i> Description détaillée</h2>
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    </div>
                </div>

                <!-- Images secondaires -->
                <?php if (!empty($secondaryImages)): ?>
                    <div class="gallery-section">
                        <h2><i class="fas fa-images"></i> Galerie d'images</h2>
                        <div class="gallery-grid">
                            <?php foreach ($secondaryImages as $image): ?>
                                <?php $image = trim($image); ?>
                                <?php if (!empty($image)): ?>
                                    <div class="gallery-item">
                                        <img src="../backend/<?php echo htmlspecialchars($image, ENT_QUOTES); ?>" 
                                            alt="Image secondaire" 
                                            onclick="openModal(this.src)">
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Modal pour l'affichage des images -->
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
        <script src="assets/js/project_detail.js"></script>
    </body>
</html>