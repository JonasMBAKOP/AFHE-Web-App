<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once '../../includes/auth_guard.php';
require_once '../../config/db_config.php';
require_once '../../includes/db_connect.php';
require_once '../../models/ReportModel.php';

// // Connexion à la BDD (ajuste à ton config Ionos)
// $pdo = new PDO('mysql:host=localhost;dbname=ta_bdd', 'user', 'pass', [
//     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
// ]);

// Lecture des paramètres start/end via GET
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end   = $_GET['end']   ?? date('Y-m-d');

// Requête préparée pour sécuriser
$stmt = $pdo->prepare("
    SELECT visit_date AS date, COUNT(*) AS cnt
    FROM visits
    WHERE visit_date BETWEEN :start AND :end
    GROUP BY visit_date
    ORDER BY visit_date
");
$stmt->execute(['start' => $start, 'end' => $end]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer labels et data
$labels = [];
$data   = [];
foreach ($rows as $r) {
    $labels[] = $r['date'];
    $data[]   = (int) $r['cnt'];
}

// On renvoie le JSON
echo json_encode([
    'labels' => $labels,
    'data'   => $data
]);

?>

<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

<script>
// if (!extension_loaded('gd')) {
//     die('Extension GD non trouvée. Installez-la ou activez-la dans php.ini.');
// }

// // $gdInfo = gd_info();
// // if (empty($gdInfo['GD Version'])) {
// //     die('GD chargé, mais version introuvable.');
// // }

// // 1. Config + récupération des dates
// $start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
// $end   = $_GET['end']   ?? date('Y-m-d');

// try {
//     $pdo   = new PDO('mysql:host=localhost;dbname=afhe_db;charset=utf8', 'root', '');
//     $model = new ReportModel($pdo);
//     $visits = $model->getVisitsByDate($start, $end);
// } catch (Exception $e) {
//     // Erreur de connexion ou erreur SQL
//     $msg = 'Erreur BD: ' . substr($e->getMessage(), 0, 50);
//     imagestring($img = imagecreatetruecolor(400, 50), 5, 10, 10,
//         $msg, $black = imagecolorallocate($img,255,0,0));
//     imagepng($img);
//     imagedestroy($img);
//     exit;
// }

// $pdo   = new PDO('mysql:host=localhost;dbname=afhe_db;charset=utf8', 'root', '');

// $model = new ReportModel($pdo);
// $visits = $model->getVisitsByDate($start, $end);

// // Debug
// // Si aucune donnée, texte explicatif
// if (!$visits) {
//     $img = imagecreatetruecolor(400, 80);
//     $white = imagecolorallocate($img,255,255,255);
//     $black = imagecolorallocate($img,0,0,0);
//     imagefilledrectangle($img,0,0,400,80,$white);
//     imagestring($img, 4, 10, 30,
//         "Pas de visites\nentre $start et $end", $black);
//     imagepng($img);
//     imagedestroy($img);
//     exit;
// }


// // 2. Paramètres du graphique
// $width       = 800;
// $height      = 400;
// $marginLeft  = 60;
// $marginRight = 20;
// $marginTop   = 20;
// $marginBot   = 40;

// // 3. Calculs d’échelle
// $dates   = array_column($visits, 'date');
// $values  = array_column($visits, 'visits');
// $maxVal  = max($values) ?: 1;
// $days    = count($dates);
// $xStep   = ($width - $marginLeft - $marginRight) / max($days - 1, 1);
// $yScale  = ($height - $marginTop - $marginBot) / $maxVal;

// // 4. Création de l’image
// $img = imagecreatetruecolor($width, $height);

// // 5. Couleurs
// $white    = imagecolorallocate($img, 255,255,255);
// $black    = imagecolorallocate($img, 0,0,0);
// $blue     = imagecolorallocate($img, 0,123,255);
// $lightBg  = imagecolorallocate($img, 245,245,245);

// // 6. Fond & axes
// imagefilledrectangle($img, 0,0,$width,$height, $white);
// // Grille horizontale
// for ($i = 0; $i <= 5; $i++) {
//     $y = $marginTop + ($height-$marginTop-$marginBot) * $i / 5;
//     imageline($img, $marginLeft, $y, $width-$marginRight, $y, $lightBg);
// }
// imageline($img, $marginLeft, $marginTop, $marginLeft, $height-$marginBot, $black); // axe Y
// imageline($img, $marginLeft, $height-$marginBot, $width-$marginRight, $height-$marginBot, $black); // axe X

// // 7. Tracé de la courbe
// $prevX = null; $prevY = null;
// foreach ($visits as $i => $row) {
//     $x = $marginLeft + $i * $xStep;
//     $y = $height - $marginBot - ($row['visits'] * $yScale);

//     if ($prevX !== null) {
//         imageline($img, $prevX, $prevY, $x, $y, $blue);
//     }
//     $prevX = $x; $prevY = $y;
// }

// // 8. Légendes et labels
// // Valeurs Y (5 graduations)
// for ($i = 0; $i <= 5; $i++) {
//     $val = intval($maxVal * (5-$i) / 5);
//     $y   = $marginTop + ($height-$marginTop-$marginBot) * $i / 5;
//     imagestring($img, 3, 5, $y-7, $val, $black);
// }
// // Dates X (toutes les N points pour éviter surcharge)
// $stepLabel = max(1, floor($days/10));
// foreach ($dates as $i => $d) {
//     if ($i % $stepLabel === 0 || $i=== $days-1) {
//         $x = $marginLeft + $i * $xStep;
//         imagestring($img, 3, $x-15, $height-$marginBot+5, $d, $black);
//     }
// }

// // 9. Envoi du PNG
// header('Content-Type: image/png');
// imagepng($img);
// imagedestroy($img);
// exit;

</script>
