Ok commençons le dossier backend.
Plan de travail pour le dossier backend On va suivre cette structure :
- Configuration du projet (backend/config/) ***
- Connexion à la base de données (backend/includes/db_connect.php) ***
- Gestion des sessions et de l’authentification (backend/includes/session.php, backend/includes/auth.php) ***
- Création des modèles (backend/models/) → Définition des classes pour interagir avec la base de données.
- Développement des contrôleurs (backend/controllers/) → Gérer les interactions entre frontend et backend.
- Gestion de l’interface admin (backend/admin/) → CRUD des activités, projets, utilisateurs.
- Configuration des constantes et paramètres généraux (backend/config/constants.php).
- Sécurisation du backend (hashing des mots de passe, validation des entrées).
- Optimisation et bonnes pratiques (indexation, performance SQL).
- Tests et debug final.


Sachant que chaque activité est associée à une catégorie lors de sa création, et que chaque activité possède une image principale, et des images secondaires.
Et sachant qu'on peut ajouter et supprimer une catégorie, on va s'appuyer sur ces principes et gérer le CRUD des activités

Notons que les catégories seront représentées par des dossiers, dans le dossier "uploads/activities/" et les activités sont regoupées par dossiers de catégories. (Exemple si j'ai une catégorie "Sport", les activités de cette catégorie seront dans "uploads/activities/Sport/". Et si j'ai une activité "Football", ses images principale et secondaires seront dans "uploads/activities/Sport/Football/".)
La nomenclature des images se fera de la même manière que pour les projets.

Donc Aides moi à faire d'abord la création d'une catégorie (ce qui va créer son dossier dans "uploads/activities/")


<?php
// backend/models/ActivityModel.php
require_once __DIR__ . '/BaseModel.php';

class ActivityModel extends BaseModel
{
    // … autres méthodes (normalizeText, getActivityById, getCategoryById, getActivityImages, uploadImageFile) …

    /**
     * Met à jour une activité, déplace/renomme dossier si catégorie ou titre change,
     * met à jour tous les chemins d’images, gère main+secondaires.
     */
    public function updateActivity(
        int   $id,
        array $data,
        ?array $mainFile,
        ?array $secNew,
        array $toRemove
    ): bool {
        // 1) Charger l’existant
        $old   = $this->getActivityById($id);
        $oldCat= $this->getCategoryById($old['category_id']);
        $newCat= $this->getCategoryById($data['category_id']);
        if (!$old || !$oldCat || !$newCat) return false;

        // 2) Slugs et chemins
        $oldCatSlug = $this->normalizeText($oldCat['name']);
        $newCatSlug = $this->normalizeText($newCat['name']);
        $oldActSlug = $this->normalizeText($old['title']);
        $newActSlug = $this->normalizeText($data['title']);

        $root    = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
        $base    = $root . 'uploads' . DIRECTORY_SEPARATOR
                      . 'activities' . DIRECTORY_SEPARATOR;
        $oldDir  = "{$base}{$oldCatSlug}/{$oldActSlug}";
        $newDir  = "{$base}{$newCatSlug}/{$newActSlug}";

        // 3) Créer le dossier cible et déplacer/renommer
        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }
        if ($oldDir !== $newDir && is_dir($oldDir)) {
            rename($oldDir, $newDir);
        }

        // 4) Mettre à jour en BD tous les chemins
        $oldSeg = "uploads/activities/{$oldCatSlug}/{$oldActSlug}/";
        $newSeg = "uploads/activities/{$newCatSlug}/{$newActSlug}/";

        // a) main_image
        $this->executeQuery(
            "UPDATE activities
                SET main_image = REPLACE(main_image, :oldSeg, :newSeg)
              WHERE id_activity = :id",
            [':oldSeg'=>$oldSeg, ':newSeg'=>$newSeg, ':id'=>$id]
        );

        // b) secondaires
        $this->executeQuery(
            "UPDATE activity_images i
               JOIN activities a ON i.activity_id = a.id_activity
              SET i.image_path = REPLACE(i.image_path, :oldSeg, :newSeg)
             WHERE a.id_activity = :id",
            [':oldSeg'=>$oldSeg, ':newSeg'=>$newSeg, ':id'=>$id]
        );

        // 5) Gérer upload / renommage main_image
        $mainPath = $old['main_image'];
        if ($mainFile['error'] === UPLOAD_ERR_OK) {
            @unlink($root . $old['main_image']);
            $mainPath = $this->uploadImageFile(
                $mainFile,
                $newDir,
                "{$newActSlug}-main"
            );
        } elseif ($oldSeg !== $newSeg) {
            // renommer physiquement le fichier existant
            $ext    = pathinfo($old['main_image'], PATHINFO_EXTENSION);
            $oldAbs = $root . $old['main_image'];
            $newName= "{$newActSlug}-main.{$ext}";
            $newAbs = "{$newDir}/{$newName}";
            if (is_file($oldAbs)) {
                rename($oldAbs, $newAbs);
                $mainPath = str_replace('\\','/',
                    substr($newAbs, strlen($root))
                );
            }
        }

        // 6) Supprimer les secondaires cochées
        foreach ($toRemove as $imgId) {
            $row = $this->executeQuery(
                "SELECT image_path FROM activity_images WHERE id = :iid",
                [':iid'=>$imgId]
            )->fetch();
            if ($row) {
                @unlink($root . $row['image_path']);
                $this->executeQuery(
                    "DELETE FROM activity_images WHERE id = :iid",
                    [':iid'=>$imgId]
                );
            }
        }

        // 7) Ré-indexer/renommer restantes
        $remaining = $this->getActivityImages($id);
        $order = 0;
        foreach ($remaining as $img) {
            $order++;
            $ext    = pathinfo($img['image_path'], PATHINFO_EXTENSION);
            $oldAbs = $root . $img['image_path'];
            $newName= "{$newActSlug}-sec-{$order}.{$ext}";
            $newAbs = "{$newDir}/{$newName}";
            if (is_file($oldAbs)) {
                rename($oldAbs, $newAbs);
                $newRel = str_replace('\\','/',
                    substr($newAbs, strlen($root))
                );
                $this->executeQuery(
                    "UPDATE activity_images
                        SET image_path = :path,
                            display_order = :ord
                      WHERE id = :iid",
                    [':path'=>$newRel, ':ord'=>$order, ':iid'=>$img['id']]
                );
            }
        }

        // 8) Ajouter nouvelles secondaires
        if (!empty($secNew['name'][0])) {
            for ($i = 0; $i < count($secNew['name']); $i++) {
                if ($secNew['error'][$i] === UPLOAD_ERR_OK) {
                    $order++;
                    $file = [
                        'name'=>$secNew['name'][$i],
                        'type'=>$secNew['type'][$i],
                        'tmp_name'=>$secNew['tmp_name'][$i],
                        'error'=>$secNew['error'][$i],
                        'size'=>$secNew['size'][$i]
                    ];
                    $newRel = $this->uploadImageFile(
                        $file,
                        $newDir,
                        "{$newActSlug}-sec-{$order}"
                    );
                    $this->executeQuery(
                        "INSERT INTO activity_images
                           (activity_id,image_path,caption,display_order)
                         VALUES
                           (:aid,:path,'',:ord)",
                        [':aid'=>$id,':path'=>$newRel,':ord'=>$order]
                    );
                }
            }
        }

        // 9) Mise à jour finale de l’activité
        $this->executeQuery(
            "UPDATE activities SET
               title             = :title,
               description       = :desc,
               short_description = :shortDesc,
               category_id       = :cat,
               featured          = :fea,
               main_image        = :main,
             WHERE id_activity = :id",
            [
                ':title'    =>$data['title'],
                ':desc'     =>$data['description'],
                ':shortDesc'=>$data['short_description'],
                ':cat'      =>$data['category_id'],
                ':fea'      =>$data['featured'],
                ':main'     =>$mainPath,
                ':id'       =>$id
            ]
        );

        return true;
    }
}



<?php
// backend/admin/activities/edit_activity.php
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../models/ActivityModel.php';

$model      = new ActivityModel();
$id         = (int)($_GET['id'] ?? 0);
$activity   = $model->getActivityById($id);
$categories = $model->getCategories();        // renvoie ['id','name']
$images     = $model->getActivityImages($id);

if (!$activity) die("Activité introuvable.");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Récupérer les données
    $data = [
        'title'             => trim($_POST['title']),
        'description'       => trim($_POST['description']),
        'short_description' => trim($_POST['short_description']),
        'category_id'       => (int)$_POST['category_id'],
        'featured'          => isset($_POST['featured']) ? 1 : 0,
        'created_by'        => (int)$_POST['created_by']
    ];
    // 2) Unicité
    if ($data['title']==='') {
        $error = "Le titre est obligatoire.";
    }
    elseif (
      $model->activityExists($data['title'], $data['category_id'])
      && $data['title']!==$activity['title']
    ) {
        $error = "Une activité du même nom existe déjà dans cette catégorie.";
    }
    else {
        // 3) Fichiers
        $mainFile = $_FILES['main_image']       ?? ['error'=>1];
        $secNew   = $_FILES['secondary_images'] ?? [];
        $toRemove = $_POST['remove_images']     ?? [];

        // 4) Appel de la méthode
        $ok = $model->updateActivity(
            $id, $data, $mainFile, $secNew, $toRemove
        );
        if ($ok) {
            header("Location: list.php?updated=1");
            exit;
        }
        $error = "Erreur lors de la mise à jour.";
    }
}

// sélection courante de catégorie
$selCat = $_POST['category_id'] ?? $activity['category_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier activité</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if (isset($_GET['updated'])): ?>
<script>
  Swal.fire({
    icon:'success',
    title:'Succès',
    text:'Activité mise à jour.',
    confirmButtonText:'OK'
  }).then(()=>location='list.php');
</script>
<?php elseif($error): ?>
<script>
  Swal.fire({ icon:'error', title:'Erreur', text:<?= json_encode($error) ?> });
</script>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <h1>Modifier « <?= htmlspecialchars($activity['title']) ?> »</h1>

  <label>Titre *</label>
  <input type="text" name="title" required
    value="<?= htmlspecialchars($_POST['title'] ?? $activity['title']) ?>">

  <label>Description</label>
  <textarea name="description"><?= 
    htmlspecialchars($_POST['description'] ?? $activity['description']) ?></textarea>

  <label>Courte description</label>
  <input type="text" name="short_description"
    value="<?= htmlspecialchars($_POST['short_description'] ?? $activity['short_description']) ?>">

  <label>Catégorie *</label>
  <select name="category_id" required>
    <option value="">— Sélectionner —</option>
    <?php foreach($categories as $c): ?>
      <option value="<?= $c['id'] ?>"
        <?= $c['id']==$selCat?'selected':'' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Image principale</label>
  <?php if ($activity['main_image']): ?>
    <img src="/<?= $activity['main_image'] ?>" width="120" alt=""><br>
  <?php endif; ?>
  <input type="file" name="main_image" accept="image/*">

  <label>Images secondaires</label>
  <div>
    <?php foreach($images as $img): ?>
      <div style="display:inline-block; margin:.5em;">
        <img src="/<?= $img['image_path'] ?>" width="80"><br>
        <label>
          <input type="checkbox" name="remove_images[]" value="<?= $img['id'] ?>">
          Supprimer
        </label>
      </div>
    <?php endforeach; ?>
  </div>
  <input type="file" name="secondary_images[]" accept="image/*" multiple>

  <label>
    <input type="checkbox" name="featured" value="1"
      <?= (($_POST['featured'] ?? $activity['featured']) ? 'checked':'') ?>>
    À la une
  </label>

  <label>Créé par (ID)</label>
  <input type="number" name="created_by"
    value="<?= htmlspecialchars($_POST['created_by'] ?? $activity['created_by']) ?>">

  <button type="submit">Enregistrer</button>
  <a href="list.php">Annuler</a>
</form>
</body>
</html>