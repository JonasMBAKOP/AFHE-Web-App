-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 18 août 2025 à 11:40
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `afhe_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `activities`
--

DROP TABLE IF EXISTS `activities`;
CREATE TABLE IF NOT EXISTS `activities` (
  `id_activity` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `main_image` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_activity`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `activities`
--

INSERT INTO `activities` (`id_activity`, `title`, `description`, `short_description`, `category_id`, `featured`, `main_image`, `created_by`, `created_at`) VALUES
(1, 'Sport', 'jkzbdfjldbfdbfpip zjfbrzoifbzneeof', 'sport sportif', 1, 1, 'uploads/activities/AFHE Training/Sport/Sport - main.jpeg', 2, '2025-06-30 16:35:59'),
(2, 'Arbre de Noël', 'ljdbdfo zeifezifi', 'ksdfdj', 4, 1, 'uploads/activities/AFHE Gift/Arbre de Noël/Arbre de Noël - main.jpg', 2, '2025-06-30 16:36:34'),
(3, 'Faroty', 'pbfdzez pizjfpizjf', 'dfn', 2, 0, 'uploads/activities/AFHE School/Faroty/Faroty - main.jpeg', 2, '2025-06-30 16:37:08'),
(4, 'Cadeau', 'Ceci est un essai', 'essai kdo', 4, 1, 'uploads/activities/AFHE Gift/Cadeau/Cadeau - main.png', 2, '2025-06-30 16:37:56'),
(5, 'categorie 1', 'fmihdivp zpivhrpizv', 'zdf', 5, 0, 'uploads/activities/Catégorie nouvelle/categorie 1/categorie 1 - main.png', 1, '2025-07-02 12:22:09'),
(7, 'inclusive back to school', 'Découvrez les magnifiques sentiers de montagne avec des vues panoramiques exceptionnelles.\r\nCette randonnée vous permettra de vous reconnecter avec la nature tout en profitant d\'un exercice physique bénéfique.\r\n\r\nAccompagnés par des guides expérimentés, vous explorerez des paysages époustouflants et apprendrez sur la faune et la flore locales.\r\nL\'activité comprend des pauses régulières, des collations énergétiques et tout l\'équipement de sécurité nécessaire.\r\n\r\nAdaptée à tous les niveaux, cette expérience unique vous laissera des souvenirs inoubliables.', 'evenement de remise de don scolaire aux enfants a besoin specifiques', 2, 1, 'uploads/activities/AFHE School/inclusive back to school/inclusive back to school - main.jpeg', 17, '2025-07-02 14:29:27'),
(8, 'Studies Learning', 'zdihb zgbviznçuçàz', 'zljbvzl', 1, 0, 'uploads/activities/AFHE Training/Studies Learning/Studies Learning - main.PNG', 1, '2025-07-21 13:00:41');

-- --------------------------------------------------------

--
-- Structure de la table `activity_categories`
--

DROP TABLE IF EXISTS `activity_categories`;
CREATE TABLE IF NOT EXISTS `activity_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `display_order` int DEFAULT '1',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `activity_categories`
--

INSERT INTO `activity_categories` (`id`, `name`, `description`, `display_order`, `active`) VALUES
(1, 'AFHE Training', 'iginrikv', 3, 1),
(2, 'AFHE School', 'fbezoeuefbez', 2, 1),
(3, 'AFHE Agri', 'ekekfneeknf', 1, 1),
(4, 'AFHE Gift', 'ljndfnqdnl', 1, 0),
(5, 'Catégorie nouvelle', 'essai', 1, 0),
(6, 'AFHE Essai', 'pihgrizmh', 2, 1),
(7, 'AFHE RESSOURCES', 'Conservation des projets inachevés', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `activity_images`
--

DROP TABLE IF EXISTS `activity_images`;
CREATE TABLE IF NOT EXISTS `activity_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `activity_images`
--

INSERT INTO `activity_images` (`id`, `activity_id`, `image_path`, `caption`, `display_order`) VALUES
(1, 1, 'uploads/activities/AFHE Training/Sport/Sport - secondary_1.jpeg', '', 1),
(2, 1, 'uploads/activities/AFHE Training/Sport/Sport - secondary_2.png', '', 2),
(3, 1, 'uploads/activities/AFHE Training/Sport/Sport - secondary_3.jpg', '', 3),
(4, 2, 'uploads/activities/AFHE Gift/Arbre de Noël/Arbre de Noël - secondary_1.jpg', '', 1),
(5, 2, 'uploads/activities/AFHE Gift/Arbre de Noël/Arbre de Noël - secondary_2.jpg', '', 2),
(6, 2, 'uploads/activities/AFHE Gift/Arbre de Noël/Arbre de Noël - secondary_3.jpeg', '', 3),
(7, 3, 'uploads/activities/AFHE School/Faroty/Faroty - secondary_1.png', '', 1),
(8, 3, 'uploads/activities/AFHE School/Faroty/Faroty - secondary_2.png', '', 2),
(9, 3, 'uploads/activities/AFHE School/Faroty/Faroty - secondary_3.png', '', 3),
(10, 4, 'uploads/activities/AFHE Gift/Cadeau/Cadeau - secondary_1.png', '', 1),
(11, 4, 'uploads/activities/AFHE Gift/Cadeau/Cadeau - secondary_2.png', '', 2),
(12, 4, 'uploads/activities/AFHE Gift/Cadeau/Cadeau - secondary_3.png', '', 3),
(13, 5, 'uploads/activities/Catégorie nouvelle/categorie 1/categorie 1 - secondary_1.png', '', 1),
(14, 5, 'uploads/activities/Catégorie nouvelle/categorie 1/categorie 1 - secondary_2.png', '', 2),
(15, 5, 'uploads/activities/Catégorie nouvelle/categorie 1/categorie 1 - secondary_3.png', '', 3),
(23, 7, 'uploads/activities/AFHE School/inclusive back to school/inclusive back to school - secondary_3.jpg', '', 3),
(22, 7, 'uploads/activities/AFHE School/inclusive back to school/inclusive back to school - secondary_2.jpg', '', 2),
(21, 7, 'uploads/activities/AFHE School/inclusive back to school/inclusive back to school - secondary_1.jpg', '', 1),
(24, 8, 'uploads/activities/AFHE Training/Studies Learning/Studies Learning - secondary_1.PNG', '', 1),
(25, 8, 'uploads/activities/AFHE Training/Studies Learning/Studies Learning - secondary_2.jpeg', '', 2),
(26, 8, 'uploads/activities/AFHE Training/Studies Learning/Studies Learning - secondary_3.PNG', '', 3);

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id_contact` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `date_sent` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contact`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contacts`
--

INSERT INTO `contacts` (`id_contact`, `name`, `email`, `phone`, `subject`, `message`, `ip_address`, `date_sent`) VALUES
(1, 'MBAKOP AMBROISE CARTEL', 'superadmin@exemple.com', '656249091', 'information', 'Ceci est un simple essai', '::1', '2025-05-12 16:52:22'),
(2, 'MBAKOP AMBROISE CARTEL', 'superadmin@exemple.com', '656249091', 'information', 'Ceci est un simple essai', '::1', '2025-05-12 16:53:42'),
(3, 'MBAKOP AMBROISE CARTEL', 'ambroisembakop@gmail.com', '656249091', 'information', 'Ceci est un simple essai', '::1', '2025-05-12 16:54:21'),
(14, 'joseph', 'joseph@gmail.com', '56789009876', 'partenariat', 'LJDGFLDFLebfolzb', '::1', '2025-05-26 11:51:46'),
(13, 'Paul Nyame', 'Nyame@studiesholding.com', '456789876', 'partenariat', 'KFHXGHKJGGHLHVB/CV', '::1', '2025-05-14 11:07:45'),
(6, 'JONAS', 'carteljonas@yahoo.com', '656249091', 'don', 'afqfqfsqfqf', '::1', '2025-05-12 19:56:29'),
(7, 'Alex', 'Alex@gmail.com', '75679098008678567', 'adhesion', 'Je veux adhérer à votre association. Je suis un Scammer je peux vous aider', '::1', '2025-05-13 08:28:03'),
(8, 'Alex', 'Alex@gmail.com', '75679098008678567', 'adhesion', 'Je veux adhérer à votre association. Je suis un Scammer je peux vous aider', '::1', '2025-05-13 08:29:33'),
(9, 'FEMMES DYNAMIQUES HAOUSSA', 'a@yahoo.com', '2344525', 'partenariat', 'KNFENFPENFPEFN', '::1', '2025-05-14 06:27:23'),
(10, 'FEMMES DYNAMIQUES HAOUSSA', 'b@yahoo.com', '54324323', 'autre', 'ZRZZAARGZGFZ', '::1', '2025-05-14 06:29:31'),
(11, 'FEMMES DYNAMIQUES HAOUSSA', 'b@yahoo.com', '54324323', 'autre', 'ZRZZAARGZGFZ', '::1', '2025-05-14 06:31:02'),
(19, 'Paul Stéphane Nyame', 'Nyame@studiesholding.com', '4567887654', 'don', 'Je souhaite faire un don', '127.0.0.1', '2025-08-06 16:43:53'),
(15, 'sh', 'sh@sh.cm', '4567898765', 'autre', 'CVHKJHGJ', '::1', '2025-06-09 17:27:39'),
(16, 'Herve Eboulle', 'herve@gmail.com', '456789098', 'autre', 'UYCRFUYFLICYIYFGturcyifg', '127.0.0.1', '2025-06-14 16:19:56'),
(17, 'Mme SOUT', 'afhe@gmail.com', '34567898765', 'partenariat', 'LUDGFOUZGFUOZ', '127.0.0.1', '2025-07-02 14:09:46'),
(18, 'test', 'test@test.com', '4567887', 'don', 'rgbrfkbc l', '127.0.0.1', '2025-07-04 13:43:46');

-- --------------------------------------------------------

--
-- Structure de la table `donations`
--

DROP TABLE IF EXISTS `donations`;
CREATE TABLE IF NOT EXISTS `donations` (
  `id_donation` int NOT NULL AUTO_INCREMENT,
  `donor_name` varchar(100) DEFAULT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'XAF',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `message` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_donation`),
  KEY `create_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `donations`
--

INSERT INTO `donations` (`id_donation`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `currency`, `status`, `payment_method`, `transaction_id`, `is_anonymous`, `message`, `created_by`, `created_at`) VALUES
(1, 'Essai', 'essai@essai.com', '656249091', 10.00, 'XAF', 'pending', 'Mobile Money', '45687656789876', 0, 'Je suis généreux', 1, '2025-06-04 19:48:09'),
(5, 'donateur', 'don@gmail.com', '656249091', 2000000.00, 'XAF', 'completed', 'Orange Money', 'FGHJK567890', 1, 'Je suis un homme très riche et je veux pouvoir en faire profiter les plus nécessiteux', 1, '2025-07-15 12:30:19'),
(3, 'encore', 'encore@encore.com', '45678998765', 345678909876.00, 'USD', 'completed', 'PayPal', 'FGHJK45678', 1, 'CHJKLCVJKL', 2, '2025-06-04 20:19:48'),
(4, 'encore2 updated', 'encore2@encore.com', '4567899876', 567890.00, 'EUR', 'failed', 'Carte Bancaire', 'FGHJK567890', 0, 'VBNLMMLKJHG', 2, '2025-06-04 20:26:16'),
(6, 'nono', 'nono@nono.com', '45678765567', 458765567.00, 'XAF', 'pending', 'PayPal', '', 1, 'UOZBFIEZBFPIN', 1, '2025-07-20 15:56:54'),
(9, 'Paul Stéphane Nyame', 'nyame@studiesholding.com', '3456789876', 5000.00, 'EUR', 'failed', 'Carte Bancaire', 'FGHJK567890', 1, 'Je souhaite participer', 1, '2025-08-06 16:51:16'),
(8, 'Ndemoro', 'zdbozju@zjfbz.cm', '45678765567', 45678764.00, 'EUR', 'completed', 'Autre', 'JDFJDLBQC75797', 1, 'jfngrzngpkzjnpiajzrp opepjncpozje\r\nsrighzonvhzoq\r\nqiehcjnqziletn\r\nziqrnthvpisejriopnvhjioenjibfpbisjoreisngionho', 1, '2025-07-20 17:11:46');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id_project` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `priority` int DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_project`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id_project`, `title`, `description`, `short_description`, `status`, `main_image`, `priority`, `created_by`, `created_at`, `active`) VALUES
(1, 'Amour', 'long long long long long long long long long long long long ', 'description courte', 'upcoming', 'uploads/projects/Amour/Amour - main.jpeg', 1, 1, '2025-06-23 12:33:53', 1),
(2, 'Janvier', 'ljsdbdvlJDB', 'qdjbdjqlf', 'ongoing', 'uploads/projects/Janvier/Janvier - main.jpg', 3, 1, '2025-06-23 12:34:27', 1),
(8, 'SL', 'faire la refonte complète de la plateforme en ligne Studies Learning\r\nAjouter certaines fonctionnalités dans celle-ci', 'Refonte Studies Learning', 'ongoing', 'uploads/projects/SL/SL - main.jpeg', 1, 1, '2025-07-21 11:57:27', 1),
(6, 'Calme', 'Découvrez les magnifiques sentiers de montagne avec des vues panoramiques exceptionnelles.\r\nCette randonnée vous permettra de vous reconnecter avec la nature tout en profitant d\'un exercice physique bénéfique. \r\n\r\nAccompagnés par des guides expérimentés, vous explorerez des paysages époustouflants et apprendrez sur la faune et la flore locales.\r\nL\'activité comprend des pauses régulières, des collations énergétiques et tout l\'équipement de sécurité nécessaire. Adaptée à tous les niveaux, cette expérience unique vous laissera des souvenirs inoubliables.', 'Découvrez les magnifiques sentiers de montagne avec des vues panoramiques exceptionnelles. Cette randonnée vous permettra de vous reconnecter avec la nature tout en profitant d\'un exercice physique bénéfique. Accompagnés par des guides expérimentés, vous ', 'completed', 'uploads/projects/Calme/Calme - main.png', 2, 1, '2025-06-25 14:36:51', 1),
(9, 'youth sport for inclusive development', 'Tournoi de basket en faveur des enfants à besoins spécifiquesTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\n\r\nTournoi de basket en faveur des enfants à besoins spécifiques\r\nTournoi de basket en faveur des enfants à besoins spécifiques', 'Tournoi de basket en faveur des enfants à besoins spécifiques', 'ongoing', 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - main.jpeg', 1, 1, '2025-08-06 17:08:01', 1);

-- --------------------------------------------------------

--
-- Structure de la table `project_images`
--

DROP TABLE IF EXISTS `project_images`;
CREATE TABLE IF NOT EXISTS `project_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `image_path`, `caption`, `display_order`) VALUES
(1, 1, 'uploads/projects/Amour/Amour - secondary_1.jpeg', '', 1),
(2, 1, 'uploads/projects/Amour/Amour - secondary_2.png', '', 2),
(3, 1, 'uploads/projects/Amour/Amour - secondary_3.jpg', '', 3),
(4, 2, 'uploads/projects/Janvier/Janvier - secondary_1.jpg', '', 1),
(5, 2, 'uploads/projects/Janvier/Janvier - secondary_2.jpg', '', 2),
(28, 8, 'uploads/projects/SL/SL - secondary_1.PNG', '', 1),
(31, 8, 'uploads/projects/SL/SL - secondary_3.PNG', '', 3),
(32, 9, 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - secondary_1.jpeg', '', 1),
(20, 6, 'uploads/projects/Calme/Calme - secondary_1.png', '', 1),
(21, 6, 'uploads/projects/Calme/Calme - secondary_2.png', '', 2),
(22, 6, 'uploads/projects/Calme/Calme - secondary_3.png', '', 3),
(30, 8, 'uploads/projects/SL/SL - secondary_2.PNG', '', 2),
(33, 9, 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - secondary_2.jpeg', '', 2),
(34, 9, 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - secondary_3.png', '', 3),
(35, 9, 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - secondary_4.jpg', '', 4),
(36, 9, 'uploads/projects/youth sport for inclusive development/youth sport for inclusive development - secondary_5.jpg', '', 5);

-- --------------------------------------------------------

--
-- Structure de la table `site_stats`
--

DROP TABLE IF EXISTS `site_stats`;
CREATE TABLE IF NOT EXISTS `site_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_name` varchar(50) NOT NULL,
  `visit_count` int DEFAULT '0',
  `visit_date` date NOT NULL,
  `unique_visitors` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_date` (`page_name`,`visit_date`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `site_stats`
--

INSERT INTO `site_stats` (`id`, `page_name`, `visit_count`, `visit_date`, `unique_visitors`) VALUES
(81, 'Activités', 4, '2025-08-07', 1),
(23, 'Contact', 2, '2025-06-05', 1),
(21, 'Accueil', 2, '2025-06-05', 1),
(90, 'Projets', 11, '2025-08-15', 3),
(80, 'Accueil', 86, '2025-08-07', 1),
(26, 'Contact', 1, '2025-06-07', 1),
(27, 'Accueil', 11, '2025-06-06', 4),
(89, 'Activités', 20, '2025-08-15', 2),
(29, 'Contact', 11, '2025-06-06', 4),
(30, 'Accueil', 1, '2025-06-08', 1),
(31, 'Contact', 1, '2025-06-08', 1),
(88, 'Accueil', 390, '2025-08-15', 3),
(33, 'Accueil', 14, '2025-06-09', 4),
(87, 'Activités', 3, '2025-08-12', 1),
(35, 'Contact', 11, '2025-06-09', 2),
(36, 'Accueil', 1, '2025-06-12', 1),
(86, 'Projets', 2, '2025-08-12', 1),
(38, 'Accueil', 1, '2025-06-13', 1),
(39, 'Contact', 1, '2025-06-13', 1),
(40, 'Accueil', 2, '2025-06-14', 1),
(85, 'Accueil', 5, '2025-08-12', 1),
(42, 'Contact', 3, '2025-06-14', 1),
(43, 'Accueil', 1, '2025-06-19', 1),
(84, 'Accueil', 1, '2025-08-08', 1),
(45, 'Contact', 1, '2025-06-19', 1),
(46, 'Accueil', 3, '2025-06-23', 1),
(47, 'Accueil', 17, '2025-07-02', 2),
(48, 'Contact', 7, '2025-07-02', 1),
(83, 'Contact', 4, '2025-08-07', 1),
(82, 'Projets', 1, '2025-08-07', 1),
(51, 'Accueil', 169, '2025-07-03', 7),
(52, 'Contact', 14, '2025-07-03', 5),
(53, 'Projets', 4, '2025-07-03', 1),
(54, 'Accueil', 86, '2025-07-04', 6),
(55, 'Projets', 380, '2025-07-04', 8),
(57, 'Contact', 17, '2025-07-04', 3),
(58, 'Activités', 371, '2025-07-04', 5),
(59, 'Projets', 33, '2025-07-05', 3),
(60, 'Activités', 151, '2025-07-05', 5),
(61, 'Accueil', 32, '2025-07-05', 2),
(62, 'Contact', 11, '2025-07-05', 1),
(63, 'Activités', 82, '2025-07-07', 5),
(64, 'Accueil', 9, '2025-07-07', 4),
(65, 'Projets', 2, '2025-07-07', 1),
(66, 'Contact', 11, '2025-07-07', 2),
(67, 'Accueil', 1, '2025-07-09', 1),
(68, 'Accueil', 2, '2025-07-10', 1),
(69, 'Activités', 7, '2025-07-10', 1),
(70, 'Accueil', 1, '2025-07-14', 1),
(71, 'Activités', 1, '2025-07-14', 1),
(72, 'Projets', 1, '2025-07-14', 1),
(73, 'Contact', 1, '2025-07-14', 1),
(74, 'Accueil', 3, '2025-07-20', 3),
(75, 'Accueil', 1, '2025-07-21', 1),
(76, 'Accueil', 411, '2025-08-06', 7),
(77, 'Activités', 37, '2025-08-06', 3),
(78, 'Projets', 48, '2025-08-06', 3),
(79, 'Contact', 20, '2025-08-06', 3),
(91, 'Contact', 325, '2025-08-15', 5);

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `rating` int DEFAULT '5',
  `display_order` int DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `position`, `company`, `content`, `image_path`, `rating`, `display_order`, `created_by`, `created_at`, `active`) VALUES
(1, 'être aimé', 'ninp', 'iuhiph', 'ihpih', 'uploads/testimonials/Temoignage être aimé.jpg', 3, 2, 1, '2025-06-08 04:09:41', 1),
(11, 'raoul ', 'RO', 'AKOST', ' je suis tres content et je voudrais participer', 'uploads/testimonials/Temoignage raoul.jpeg', 4, 1, 17, '2025-07-02 14:35:19', 1),
(5, 'MAC', 'zjdbdfz', 'jbjojb', 'obj oub', NULL, 5, 2, 2, '2025-06-08 05:15:15', 1),
(6, 'essai essai', 'ljbljb', 'ljbljblj', 'ljbljbljb', NULL, 5, 1, 2, '2025-06-08 05:40:47', 0),
(7, 'essai', 'ljbljb', 'Essai essai essai essai', 'ljbljbljb', 'uploads/testimonials/Temoignage essai.jpeg', 4, 3, 2, '2025-06-08 05:41:01', 0),
(10, 'flnljdf', 'ljbkjbjkb', 'jbjlbjb', 'ljbljbljblbjl', NULL, 5, 1, 1, '2025-06-13 12:27:09', 1),
(12, 'Shane Embolla', 'Dev', 'SH', 'Test', NULL, 5, 1, NULL, '2025-08-15 11:21:05', 1),
(9, 'Empire Empire', 'PDG', 'afqffqdfljd vdljd vfDS', 'qfqffjlqddfldqdbfqljd', 'uploads/testimonials/Temoignage Empire Empire.png', 3, 2, 1, '2025-06-12 14:43:22', 0),
(13, 'Shane', 'Dev Jeux', 'Studies', 'Test 2', 'uploads/testimonials/689f187b0663d_code.PNG', 4, 0, NULL, '2025-08-15 11:22:35', 1),
(14, 'Shane', 'Dev Jeux', 'Studies', 'Test 2', 'uploads/testimonials/689f1bd8720de_code.PNG', 4, 1, NULL, '2025-08-15 11:36:56', 1),
(15, 'jzdbk', 'jqdbf', 'jlbdjq', 'jzvzbjb ', NULL, 5, 3, 1, '2025-08-15 12:13:04', 1),
(16, 'Embolla', 'Stagiaire', 'Studies Holding', 'Studies Ambassador', NULL, 5, 0, NULL, '2025-08-15 13:21:10', 0),
(17, 'Embolla Shane', 'Art et Humanité Numérique', 'ENSPD', 'Studies', 'uploads/testimonials/Temoignage Embolla Shane.PNG', 3, 0, NULL, '2025-08-15 13:27:16', 0),
(18, 'jonas', 'dev web', 'King', 'rfkn dzlckznd kcmndkc kzdnccpkzdnc\r\ndlcjkqkxbxncljqxbclj', 'uploads/testimonials/Temoignage jonas.jpeg', 5, 0, NULL, '2025-08-15 19:11:44', 0),
(19, 'The King', 'DSI', 'Jonas Tech', 'zdjcbdzj\r\nzdlvbzdjbv\r\nzdljvbdzjlvbdzkvbdv ldzjvkmzdnv\r\nzdljvkbzdjvbdzjvbz', 'uploads/testimonials/Temoignage The King.jpg', 5, 0, NULL, '2025-08-15 19:16:21', 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(200) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','super_admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'admin',
  `profile_image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `email`, `full_name`, `role`, `profile_image`, `active`) VALUES
(1, 'King Jonas', '$2y$10$4niRpWgmHfNv5M6K3ulkO.XiewvpatmZkfKjynENekJTljmDTFg56', 'superadmin@exemple.com', 'The King Jonas', 'super_admin', '', 1),
(2, 'Cartel', '$2y$10$e5LGhFTdKNbZPQMdNDKVB.lS5qsAHvpmXMNCEPCHE5m8N9czgrvie', 'admin@exemple.com', 'Cartel Jonas', 'admin', '', 1),
(11, 'CartelJonas237', '$2y$10$RBcPNOlqEsBm2h9gLO8zRuriYiTQk/TcCCRgNDimNcwXITVNF9rwi', 'carteljonas@yahoo.com', 'Ambroise Cartel Jonas', 'super_admin', '', 0),
(18, 'arafat', '$2y$10$HDbKhbordEu7Laao5.frf.npmgUS/C2apSpmACVOvrJfZtKtq9/gm', 'yorobo@ivoire.com', 'Dj Arafat', 'admin', '', 0),
(14, 'Alex', '$2y$10$Ksy2e4PRGrOtJuhp6iBA2u/LVGVxMzXqs774LqBWNjUM7mf3.JkmS', 'Alex@gmail.com', 'Alex le Scamer', 'admin', '', 1),
(15, 'Paul1', '$2y$10$s0sZDn3PlPqZGO4xLLv/G.azjV/SaEiaaASVb/Rn.ao22K0vaC0g2', 'Nyame@studiesholding.com', 'Paul Nyame', 'admin', '', 0),
(22, 'zfkmzn²', '$2y$10$r8c/lhg0vlXKvYbPINNRY.REjlV86BwnrVHtF9LPZfLhAkj3qD7jO', 'anf@rsdfdf.cm', 'jzbfozbfozfbdzof', 'admin', '', 1),
(17, 'Georges', '$2y$10$PZJqTYG.No9pich4Ku88X.0Q3.TD75a/YaEeBm5D2Y1znMvBJygVK', 'georges.afhe@gmail.com', 'EWANE Georges', 'super_admin', '', 1),
(20, 'idiot', '$2y$10$06hbDHYmj50FRn/STLQAO.jUuH8NLn3l7exZj0JCnbCYpOWxaMaT.', 'con@baka.com', 'mouphe', 'admin', '', 1),
(23, 'okay', '$2y$10$Vv8yOsk8nBMWgF0DmOCFdupfYSkqe4RY2W.pW/uCLBgM5f3sdVSNC', 'dak@rf.jng', 'modifié', 'admin', '', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
