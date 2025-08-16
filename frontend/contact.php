<?php
    require_once "../backend/includes/db_connect.php"; // Connexion à la base
    require_once "../backend/includes/functions.php"; // Fonctions utiles
    require_once "../backend/models/ContactModel.php"; // Modèle contact

    $contactModel = new ContactModel($pdo); // Instancier le modèle contact
?> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = htmlspecialchars($_POST["name"]);
        $email = htmlspecialchars($_POST["email"]);
        $phone = htmlspecialchars($_POST["phone"]);
        $subject = htmlspecialchars($_POST["subject"]);
        $message = htmlspecialchars($_POST["message"]);
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Enregistrement en base
        if ($contactModel->createContact($name, $email, $phone, $subject, $message, $ip_address) /* Utiliser le modèle pour créer le contact*/){
            echo "<script>Swal.fire('Message envoyé !', 'Votre message a été transmis à AFHE.', 'success');</script>";
        } else {
            echo "<script>Swal.fire('Erreur', 'Impossible d’envoyer le message.', 'error');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contactez-nous | AFHE</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/components.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        
    </head>
    <body>
        <?php
            // Définir le nom de la page
            $_GET['page'] = "Contact";
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
                        <li><a href="projects.php">Projets</a></li>
                        <li><a href="contact.php" class="active">Contact</a></li>
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

        <section class="contact-banner">
            <div class="container">
                <div class="section-title">
                    <h1 >Contactez-nous</h1>
                </div>
            </div>
        </section>

        <div class="container contact-container">
            <!-- Coordonnées -->
            <section class="contact-info">
                <h2>Nos Coordonnées</h2>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <p> Douala-Bonapriso, face le Bistro Latin</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <p> +237 699 94 99 18</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <p> +237 679 71 01 01</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <p> <a href="mailto:assfemmhandi@gmail.com">assfemmhandi@gmail.com</a></p>
                </div>
            </section>

            <!-- Formulaire de contact -->
            <section class="contact-form-section">
                <h2>Envoyez nous un message</h2>
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label>Nom et prénom :</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email :</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone :</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="form-group">
                            <label for="">Objet :</label>
                            <select name="subject" id="">
                                <option value="">Sélectionner une option</option>
                                <option value="information">Demande d'informations</option>
                                <option value="partenariat">Proposition de Partenariat</option>
                                <option value="adhesion">Adhésion à AFHE</option>
                                <option value="don">Faire un don</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    <div class="form-group">
                        <label>Message :</label>
                        <textarea name="message" rows="10" required></textarea>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">ENVOYER</button>
                    <div class="separator">OU</div>
                    <a href="https://wa.me/237699949198" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i>Contactez-nous via WhatsApp</a>
                </form>
            </section>

            <!-- Carte Google Maps -->
            <section class="map-section">
                <h2>Notre Localisation</h2>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d215.13332343539105!2d9.69988638064302!3d4.025394919455889!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x106112c62c45766f%3A0xf85d6cdca4d64740!2sBonadounbe%2C%20Douala!5e1!3m2!1sfr!2scm!4v1747043719563!5m2!1sfr!2scm" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <?php require_once 'components/footer.php'; ?>
        
        <script src="assets/js/responsive.js"></script>
        <script src="assets/js/components.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>