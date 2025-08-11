<?php
// Connexion à la base
require_once("bd.php");

if (!isset($_GET['id'])) {
    die("Article introuvable !");
}

$id_article = intval($_GET['id']);

// Récupération des infos de l'article
try {
    $pdo = getDbConnection();

    // Récupérer l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id_article]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article introuvable !");
    }

    // Récupérer les images associées
    $stmt = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
    $stmt->execute([$id_article]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

    <h1><?= htmlspecialchars($article['titre']) ?></h1>
    <p><strong>Description :</strong></p>
    <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>

    <!-- Image de couverture -->
    <h4>Image de couverture</h4>
    <?php if (!empty($article['image_principale'])): ?>
        <img src="/uploads/<?= htmlspecialchars($article['image_principale']) ?>" class="img-fluid mb-3" style="max-width:300px;">
        <br>
        <a href="supprimer_image.php?id=<?= $id_article ?>&type=principale" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette image ?')">Supprimer l'image de principale</a>
    <?php else: ?>
        <p>Aucune image principale.</p>
    <?php endif; ?>

    <hr>

    <!-- Galerie -->
    <h4>Galerie d'images</h4>
    <div class="row">
        <?php if (count($images) > 0): ?>
            <?php foreach ($images as $img): ?>
                <div class="col-md-3 mb-3">
                    <img src="/uploads/<?= htmlspecialchars($img['fichier']) ?>" class="img-fluid rounded">
                    <br>
                    <a href="supprimer_image.php?id=<?= $img['id'] ?>&type=galerie" class="btn btn-danger btn-sm mt-1" onclick="return confirm('Supprimer cette image ?')">Supprimer</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune image dans la galerie.</p>
        <?php endif; ?>
    </div>

    <hr>

    <!-- Formulaire d'ajout d'image -->
    <h4>Ajouter des images</h4>
    <form action="traiter_modif.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_article" value="<?= $id_article ?>">
        <div class="mb-3">
            <input type="file" name="galerie[]" multiple class="form-control">
        </div>
        <button type="submit" name="gallerie_images" class="btn btn-primary">Ajouter</button>
    </form>

    <hr>

    <a href="modifier_article.php?id=<?= $id_article ?>" class="btn btn-warning">Modifier l'article</a>
    <a href="galerie.php" class="btn btn-secondary">Retour à la liste</a>

</body>
</html>
