<?php
session_start();
require_once("bd.php");
include("header.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID article invalide.");
$id = (int) $_GET['id'];

try {
    $pdo = getDbConnection();

    // Récupérer l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$article) die("Article introuvable.");

    // Liste des catégories
    $stmtCat = $pdo->query("SELECT DISTINCT categorie FROM articles ORDER BY categorie ASC");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    // Galerie d’images
    $stmtImg = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
    $stmtImg->execute([$id]);
    $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier l'article</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div> 
        <a href="accueil.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
           </a>
     <h2>Modifier l'article</h2>
      </div>
<form action="traiter_modifier_article.php" method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($article['id']) ?>">
    <div class="mb-3">
        <label>Titre :</label>
        <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($article['titre']) ?>" required>
    </div>

    <div class="mb-3">
        <label>Description :</label>
        <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($article['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Catégorie :</label>
        <select name="categorie" class="form-select" required>
                <option value="">-- Choisissez une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nom']) ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                <?php endforeach; ?>
            </select>
    </div>

    <div class="mb-3">
        <label>Date d'ajout :</label>
        <input type="date" class="form-control" name="date_ajout" value="<?= htmlspecialchars($article['date_ajout']) ?>">
        <small class="text-muted">Laisser vide pour conserver l'ancienne date</small>
    </div>

    <div class="mb-3">
        <label>Image principale actuelle :</label><br>
        <?php if (!empty($article['image_principale']) && file_exists($article['image_principale'])): ?>
            <img src="<?= htmlspecialchars($article['image_principale']) ?>" style="max-height:250px;"><br>
        <?php else: ?>
            <p>Aucune image</p>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label>Changer l'image principale :</label>
        <input type="file" name="image_principale" accept="image/*" class="form-control">
    </div>
   <hr>
<h4>Galerie d'images</h4>
<?php if (count($images) > 0): ?>
    <div class="row mb-3">
        <?php foreach ($images as $img): ?>
            <div class="col-md-3 text-center mb-2">
                <img src="<?= htmlspecialchars($img['fichier'] ?? '') ?>" class="img-thumbnail" style="max-height:200px;"><br>
                <a href="supprimer_image.php?id=<?= $img['id'] ?>" 
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
    <label>Ajouter des images à la galerie :</label>
    <input type="file" name="images[]" multiple accept="image/*" class="form-control">
</div>

<div class="text-end">
    <button type="submit" class="btn btn-primary"> Confirmer la modification</button>
</div>
<input type="hidden" name="auteur" value="<?= htmlspecialchars($_SESSION['username']) ?>">

    

</form>
</div>
<?php include("footer.php"); ?>
</body>
</html>
