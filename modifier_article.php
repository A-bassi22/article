<?php
require_once("bd.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID article invalide.");
}
$id = (int) $_GET['id'];

try {
    $pdo = getDbConnection();

    // Récupérer l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$article) {
        die("Article introuvable.");
    }

    // Récupérer les images de la galerie
    $stmtImg = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
    $stmtImg->execute([$id]);
    $gallery = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les catégories
    $categories = $pdo->query("SELECT nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Modifier l'article et la galerie</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">

<h1 class="mb-4">Modifier l'article</h1>

<!-- Formulaire modification article -->
<form action="traiter_modifier_article.php?id=<?= $id ?>" method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="titre" class="form-label">Titre *</label>
        <input type="text" id="titre" name="titre" class="form-control" required value="<?= htmlspecialchars($article['titre']) ?>">
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description *</label>
        <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($article['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label for="categorie" class="form-label">Catégorie *</label>
        <select id="categorie" name="categorie" class="form-select" required>
            <option value="">-- Sélectionnez --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat === $article['categorie']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Image principale actuelle</label><br>
        <?php if (!empty($article['image_principale'])): ?>
            <img src="<?= htmlspecialchars($article['image_principale']) ?>" alt="Image principale" style="max-width: 200px; height: auto; border-radius: 6px;">
        <?php else: ?>
            <p class="text-muted">Pas d'image principale</p>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="image_principale" class="form-label">Changer l'image principale (laisser vide pour garder l'actuelle)</label>
        <input type="file" id="image_principale" name="image_principale" accept="image/*" class="form-control" />
    </div>

    <div class="mb-3">
        <label for="auteur" class="form-label">Auteur</label>
        <input type="text" id="auteur" name="auteur" class="form-control" value="<?= htmlspecialchars($article['auteur'] ?? '') ?>">
    </div>

    <!--<div class="mb-3">
        <label for="date_ajout" class="form-label">Date d'ajout *</label>
        <input type="date" id="date_ajout" name="date_ajout" class="form-control" required value="<?= htmlspecialchars($article['date_ajout']) ?>">
    </div> -->

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="details_article.php?id=<?= $id ?>" class="btn btn-secondary ms-2">Annuler</a>
</form>

<hr class="my-5">

<h2>Galerie d'images</h2>

<?php if (count($gallery) === 0): ?>
    <p>Aucune image dans la galerie.</p>
<?php else: ?>
    <div class="row">
        <?php foreach ($gallery as $img): ?>
            <div class="col-md-3 mb-4 text-center">
                <img src="<?= htmlspecialchars($img['fichier']) ?>" class="img-fluid img-thumbnail" alt="Image galerie" style="max-height: 150px; object-fit: cover; border-radius: 6px;">
                <form method="post" action="supprimer_image_galerie.php" onsubmit="return confirm('Supprimer cette image ?');">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <input type="hidden" name="article_id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-danger btn-sm mt-2">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h3>Ajouter des images à la galerie</h3>
<form action="ajouter_images_galerie.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="article_id" value="<?= $id ?>">
    <div class="mb-3">
        <input type="file" name="images[]" multiple accept="image/*" class="form-control" />
    </div>
    <button type="submit" class="btn btn-success">Ajouter</button>
</form>

</div>
</body>
</html>
