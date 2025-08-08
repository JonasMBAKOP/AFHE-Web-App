<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    </head>
    <body>
        <?php
            // Définit le nom de la page
            $_GET['page'] = "Accueil";
            require_once "../backend/admin/reports/updateVisits.php";
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
                        <li><a href="index.php" class="active">Accueil</a></li>
                        <li><a href="activities.php">Activités</a></li>
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

        <!-- Bannière -->
        <section class="banner">
            <section class="slider-container">
                <div class="slider">
                    <img src="assets/images/sliders/slider1.jpeg" alt="Image slide 1">
                    <img src="assets/images/sliders/slider2.png" alt="Image slide 2">
                    <img src="assets/images/sliders/slider3.jpg" alt="Image slide 3">
                    <img src="assets/images/sliders/slider4.jpg" alt="Image slide 4">
                    <img src="assets/images/sliders/slider5.png" alt="Image slide 5">
                </div>
                <div class="slider-navigation">
                    <span class="dot active" onclick="changeSlide(0)"></span>
                    <span class="dot" onclick="changeSlide(1)"></span>
                    <span class="dot" onclick="changeSlide(2)"></span>
                    <span class="dot" onclick="changeSlide(3)"></span>
                    <span class="dot" onclick="changeSlide(4)"></span>
                </div>
            </section>
        </section>

        <section class="presentation">
            <h2>Bienvenue à l'AFHE</h2>
            <hr>
            <p>L'Association des Femmes Handicapées pour l'Entrepreneuriat (AFHE) vise à promouvoir l'entrepreneuriat féminin handi comme levier de croissance économique.</p>
            <p>Notre vision est de positionner la femme handicapée comme actrice du développement à l'horizon 2035.</p>
        </section>

        <section class="presidente">
            <h2>Mot de la Présidente</h2>
            <hr>
            <div class="presidente-container">
                <img src="assets/images/presidente.png" alt="Mme SOUT Marie Florence">
                <div class="presidente-text">
                    <p>"Citation inspirante sur l'entrepreneuriat féminin handi et le développement..."</p>
                    <p class="nom">- Mme SOUT Marie Florence, Présidente de l'AFHE</p>
                </div>
            </div>
        </section>

        <section class="valeurs">
            <h2>Nos Valeurs</h2>
            <hr>
            <div class="valeurs-container">
                <div class="valeur">Autodiscipline</div>
                <div class="valeur">Adaptabilité</div>
                <div class="valeur">Compétence</div>
                <div class="valeur">Autonomie</div>
                <div class="valeur">Épanouissement</div>
            </div>
        </section>

        <section class="activities">
            <h2>Nos Activités</h2>
            <hr>
            <div class="activities-container">
                <?php
                    require_once __DIR__ . "/../backend/includes/db_connect.php"; // Connexion à la base
                    require_once __DIR__ . "/../backend/includes/functions.php"; // Fonctions utiles
                    
                    // Préparation et exécution
                    $sql    = "SELECT id_activity, main_image, title, short_description
                                FROM activities
                                WHERE featured = 1
                                ORDER BY created_at DESC
                                LIMIT 4";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($act = $result->fetch_assoc()):
                ?>
                            <div class="activity-card">
                                <img src="../backend/<?= htmlspecialchars($act['main_image'], ENT_QUOTES) ?>"
                                    alt="<?= htmlspecialchars($act['title'], ENT_QUOTES) ?>">
                                <h3><?= htmlspecialchars($act['title'], ENT_QUOTES) ?></h3>
                                <p><?= htmlspecialchars(mb_strimwidth($act['short_description'], 0, 100, '…'), ENT_QUOTES) ?></p>
                                <a href="activity_detail.php?id=<?= (int)$act['id_activity'] ?>" class="btn-view">Voir</a>
                            </div>
                <?php
                        endwhile;
                    else:
                        echo '<p>Aucune activité disponible pour le moment.</p>';
                    endif;
                ?>
            </div>
            <a href="activities.php" class="btn-more">Voir Plus</a>
        </section>

        <section class="projects">
            <h2>Nos Projets</h2>
            <hr>
            <div class="projects-container">
                <?php
                    $sql    = "SELECT id_project, main_image, title, short_description
                                FROM projects
                                WHERE active = 1
                                ORDER BY created_at DESC, priority ASC
                                LIMIT 4";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($proj = $result->fetch_assoc()):
                ?>
                            <div class="project-card">
                                <img src="../backend/<?= htmlspecialchars($proj['main_image'], ENT_QUOTES) ?>"
                                    alt="<?= htmlspecialchars($proj['title'], ENT_QUOTES) ?>">
                                <h3><?= htmlspecialchars($proj['title'], ENT_QUOTES) ?></h3>
                                <p><?= htmlspecialchars(mb_strimwidth($proj['short_description'], 0, 100, '…'), ENT_QUOTES) ?></p>
                                <a href="project_detail.php?id=<?= (int)$proj['id_project'] ?>" class="btn-view">Voir</a>
                            </div>
                <?php
                        endwhile;
                    else:
                        echo '<p>Aucun projet disponible pour le moment.</p>';
                    endif;
                ?>
            </div>
            <a href="projects.php" class="btn-more">Voir Plus</a>
        </section>

        <section class="testimonials">
            <h2>Quelques Témoignages</h2>
            <hr>
            <div class="testimonials-container">
                <?php
                    $sql    = "SELECT name, position, company, image_path, content, rating
                                FROM testimonials
                                WHERE active = 1
                                ORDER BY display_order ASC
                                LIMIT 8";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($t = $result->fetch_assoc()):
                ?>
                            <div class="testimonial-card">

                                <?php if (!empty($t['image_path'])) : ?>
                                    <img src="../backend/<?= htmlspecialchars($t['image_path'], ENT_QUOTES) ?>"
                                    alt="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>">
                                <?php else : ?>
                                    <span>
                                        <i class="fas fa-user"></i>
                                    </span>
                                <?php endif; ?>
                                <div class="name"><?= htmlspecialchars($t['name'], ENT_QUOTES) ?></div>
                                <div class="position"><?= htmlspecialchars($t['position'], ENT_QUOTES) ?></div>
                                <div class="company"><?= htmlspecialchars($t['company'], ENT_QUOTES) ?></div>
                                <div class="message">"<?= htmlspecialchars($t['content'], ENT_QUOTES) ?>"</div>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $t['rating'] ? 'fas fa-star' : 'far fa-star' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                <?php
                        endwhile;
                    else:
                        echo '<p>Aucun témoignage disponible pour le moment.</p>';
                    endif;
                ?>
            </div>
        </section>

        <section class="don">
            <a href="contact.php"><button>Faire un don pour soutenir nos projets inclusifs</button></a>
        </section>

        <?php require_once 'components/footer.php'; ?>

        <script src="assets/js/responsive.js"></script>
        <script src="assets/js/slider.js"></script>
        <script src="assets/js/components.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>