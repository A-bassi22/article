<?php
require_once("bd.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = (int) $_GET['id'];

try {
    $pdo = getDbConnection();

    // Récupération de l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article introuvable.");
    }

    // Récupération des images de la galerie
    $stmt_images = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
    $stmt_images->execute([$id]);
    $gallerie_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4"><?= htmlspecialchars($article['titre']) ?></h1>
    <img src="<?= htmlspecialchars($article['image_principale']) ?>" class="img-fluid mb-3" alt="Image de couverture">

    <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($article['description'])) ?></p>
    <p><strong>Catégorie :</strong> <?= htmlspecialchars($article['categorie']) ?></p>
    <p><strong>Auteur :</strong> <?= htmlspecialchars($article['auteur'] ?? "Inconnu") ?></p>
    <p><strong>Date d'ajout :</strong> <?= htmlspecialchars($article['date_ajout']) ?></p>

    <h3 class="mt-5">Galerie d'images</h3>
    <div class="row">
        <?php if (!empty($gallerie_images)): ?>
            <?php foreach ($gallerie_images as $img): ?>
                <div class="col-md-3 mb-4 text-center">
                     <img src="<?= htmlspecialchars($img['fichier']) ?>" class="img-fluid img-thumbnail" alt="Image" onclick="afficherPleinEcran(this)">

                <div class="mt-2">
                        <a href="modifier_image.php?id=<?= $img['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="supprimer_image.php?id=<?= $img['id'] ?>&article_id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette image ?');">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune image dans la galerie.</p>
        <?php endif; ?>
    </div>

    <a href="galerie.php" class="btn btn-secondary">Retour</a>
</div>

<script>
function afficherPleinEcran(img) {
    var fenetre = window.open("", "_blank");
    fenetre.document.write('<img src="' + img.src + '" style="width:100%">');
}
</script>
</body>
</html>  
