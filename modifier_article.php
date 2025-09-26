<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Article - Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-fullscreen {
            max-width: 1200px;
            margin: 1.75rem auto;
        }
        .modal-body {
            padding: 0;
        }
        .content {
            padding: 40px 20px;
        }
        .btn-add {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-add:hover {
            background-color: #4338ca;
        }
        .main-content {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<?php
session_start();
require_once "bd.php";

$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

$pdo = getDbConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die("ID article invalide.");
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_image = (int) $_GET['delete'];

    $stmt = $pdo->prepare("SELECT * FROM gallerie_images WHERE id = ? AND article_id = ?");
    $stmt->execute([$id_image, $id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        if (!empty($image['fichier']) && file_exists($image['fichier'])) {
            unlink($image['fichier']);
        }

        $stmtDel = $pdo->prepare("DELETE FROM gallerie_images WHERE id = ?");
        $stmtDel->execute([$id_image]);
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id . "&modal=1");
    exit();
}

/* --- MODIFICATION ARTICLE --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $categorie    = trim($_POST['categorie'] ?? '');
    $date_ajout   = !empty($_POST['date_ajout']) ? $_POST['date_ajout'] : null;
    $auteur       = $_SESSION['username'];

    $stmtArt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmtArt->execute([$id]);
    $article = $stmtArt->fetch(PDO::FETCH_ASSOC);
    if (!$article) die("Article introuvable.");

    if ($titre === '' || $description === '' || $categorie === '') {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id={$id}&err=missing&modal=1");
        exit;
    }

    $imagePrincipale = $article['image_principale'] ?? '';
    if (!empty($_FILES['image_principale']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName   = time() . "_" . basename($_FILES['image_principale']['name']);
        $targetPath = $uploadDir . $fileName;

        if (is_uploaded_file($_FILES['image_principale']['tmp_name']) &&
            move_uploaded_file($_FILES['image_principale']['tmp_name'], $targetPath)) {
            $imagePrincipale = $targetPath;
        }
    }

    $sql = "UPDATE articles
            SET titre = ?, description = ?, categorie = ?,
                date_ajout = COALESCE(?, date_ajout),
                image_principale = ?, auteur = ?
            WHERE id = ?";
    $stmtUp = $pdo->prepare($sql);
    $stmtUp->execute([$titre, $description, $categorie, $date_ajout, $imagePrincipale, $auteur, $id]);

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['tmp_name'] as $k => $tmpName) {
            if (!empty($_FILES['images']['name'][$k]) && is_uploaded_file($tmpName)) {
                $fileName   = time() . "_" . basename($_FILES['images']['name'][$k]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $stmtImg = $pdo->prepare("INSERT INTO gallerie_images (article_id, fichier) VALUES (?, ?)");
                    $stmtImg->execute([$id, $targetPath]);
                }
            }
        }
    }

    // Redirection après succès
    if (isset($_GET['modal']) && $_GET['modal'] == 1) {
        echo "<script>parent.location.href='accueil.php?success=1';</script>";
        exit();
    } else {
        header("Location: accueil.php?success=1");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) die("Article introuvable.");

$categories = [];
try {
    $q = $pdo->query("SELECT nom FROM categories ORDER BY nom ASC");
    $categories = $q->fetchAll(PDO::FETCH_COLUMN);
    if (!$categories) {
        $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
        $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Throwable $e) {
    $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
    $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
}

$stmtImg = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
$stmtImg->execute([$id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Structure -->

            <div class="modal-header">
    </div>
<div class="modal-body p-0">
    <div class="main-content">
        <div class="d-flex justify-content-center">
            <div class="content shadow rounded-3 bg-white p-4 w-100" style="max-width: 1200px; min-height: 80vh;">
                <a href="#" class="btn btn-secondary mb-3" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
                </a>

                <?php if (!empty($_GET['err']) && $_GET['err'] === 'missing'): ?>
                    <div class="alert alert-danger">Veuillez remplir tous les champs obligatoires.</div>
                <?php endif; ?>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?= (int)$id ?>&modal=1" method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($article['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Titre :</label>
                        <input type="text" class="form-control" name="titre"
                               value="<?= htmlspecialchars($article['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description :</label>
                        <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($article['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catégorie :</label>
                        <select name="categorie" class="form-select" required>
                            <option value="">-- Choisissez une catégorie --</option>
                            <?php foreach ($categories as $catName): ?>
                                <option value="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= ($catName === ($article['categorie'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date d'ajout :</label>
                        <input type="date" class="form-control" name="date_ajout"
                               value="<?= htmlspecialchars($article['date_ajout'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <small class="text-muted">Laisser vide pour conserver l'ancienne date.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image principale actuelle :</label><br>
                        <?php if (!empty($article['image_principale'])): ?>
                            <img src="<?= htmlspecialchars($article['image_principale'], ENT_QUOTES, 'UTF-8') ?>" style="max-height:250px; max-width: 100%;">
                        <?php else: ?>
                            <p>Aucune image</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Changer l'image principale :</label>
                        <input type="file" name="image_principale" accept="image/*" class="form-control">
                    </div>

                    <hr>
                    <h4>Galerie d'images</h4>
                    <?php if (!empty($images)): ?>
                        <div class="row mb-3">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3 text-center mb-2">
                                    <img src="<?= htmlspecialchars($img['fichier'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="img-thumbnail" style="max-height:200px; width: 100%; object-fit: cover;"><br>
                                    <button type="button" class="btn btn-danger btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $img['id'] ?>">
                                        Supprimer
                                    </button>

                                    <!-- Modal spécifique à chaque image -->
                                    <div class="modal fade" id="confirmDeleteModal<?= $img['id'] ?>" tabindex="-1" aria-hidden="true">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <h5 class="modal-title">Confirmation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body">
                                            Voulez-vous vraiment supprimer cette image ?
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?= (int)$id ?>&delete=<?= (int)$img['id'] ?>&modal=1" class="btn btn-danger">Supprimer</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Aucune image dans la galerie.</p>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Ajouter des images à la galerie :</label>
                        <input type="file" name="images[]" multiple accept="image/*" class="form-control">
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-secondary">Confirmer</button>
                    </div>

                    <input type="hidden" name="auteur" value="<?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>