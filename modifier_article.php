<?php
session_start();
require_once "bd.php";

// (Facultatif mais pratique) Évite les "headers already sent" si un BOM ou espace traîne
// ob_start();

$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$pdo = getDbConnection();

// Récupération de l'ID depuis GET (affichage) ou POST (soumission)
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die("ID article invalide.");
}

/* ========================== TRAITEMENT (POST) ========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $categorie    = trim($_POST['categorie'] ?? '');
    $date_ajout   = !empty($_POST['date_ajout']) ? $_POST['date_ajout'] : null;
    $auteur       = $_SESSION['username'];

    // Récupérer l'article courant (pour l'image si pas de nouvelle)
    $stmtArt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmtArt->execute([$id]);
    $article = $stmtArt->fetch(PDO::FETCH_ASSOC);
    if (!$article) die("Article introuvable.");

    if ($titre === '' || $description === '' || $categorie === '') {
        // Reviens sur la page avec message d'erreur via query string si tu préfères
        // Ici, on garde simple : on retombe en GET avec un flag d'erreur
        header("Location: modifier_article.php?id={$id}&err=missing");
        exit;
    }

    // Upload image principale (si fournie)
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

    // Mise à jour
    $sql = "UPDATE articles
            SET titre = ?, description = ?, categorie = ?,
                date_ajout = COALESCE(?, date_ajout),
                image_principale = ?, auteur = ?
            WHERE id = ?";
    $stmtUp = $pdo->prepare($sql);
    $stmtUp->execute([$titre, $description, $categorie, $date_ajout, $imagePrincipale, $auteur, $id]);

    // Upload images de galerie (si présentes)
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

    // ✅ Redirection vers l'accueil après MAJ (aucun output avant ceci)
    header("Location: accueil.php?success=1");
    exit;
}

/* ========================== AFFICHAGE (GET) ========================== */
// Article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) die("Article introuvable.");

// Catégories : on tente la table `categories`, sinon fallback sur `articles`
$categories = [];
try {
    $q = $pdo->query("SELECT nom FROM categories ORDER BY nom ASC");
    $categories = $q->fetchAll(PDO::FETCH_COLUMN);
    if (!$categories) {
        // fallback si table vide
        $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
        $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Throwable $e) {
    // fallback si table n'existe pas
    $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
    $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
}

// Images galerie
$stmtImg = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
$stmtImg->execute([$id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

include "inc/header.php";
?>

<div class="main-content">
    <div>
        <a href="accueil.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
        </a>
        <h2 class="mb-4 text-center">Modifier l'article</h2>
    </div>

    <?php if (!empty($_GET['err']) && $_GET['err'] === 'missing'): ?>
        <div class="alert alert-danger">Veuillez remplir tous les champs obligatoires.</div>
    <?php endif; ?>

    <form action="modifier_article.php?id=<?= (int)$id ?>" method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
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
                <img src="<?= htmlspecialchars($article['image_principale'], ENT_QUOTES, 'UTF-8') ?>" style="max-height:250px;">
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
                        <img src="<?= htmlspecialchars($img['fichier'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="img-thumbnail" style="max-height:200px;"><br>
                        <a href="supprimer_image.php?id=<?= (int)$img['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cette image ?')">
                           Supprimer
                        </a>
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
            <button type="submit" class="btn btn-secondary">Confirmer la modification</button>
        </div>

        <input type="hidden" name="auteur" value="<?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </form>
</div>
</body>
</html>
