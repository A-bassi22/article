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
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($article['titre']) ?> - Détails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">

    <a href="galerie.php" class="btn btn-secondary mb-4">&larr; Retour à la galerie</a>

    <div class="card p-4 shadow-sm">
        <h1 class="mb-3"><?= htmlspecialchars($article['titre']) ?></h1>

        <!-- Image principale -->
        <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($article['image_principale']) ?>" alt="Image de couverture" class="img-fluid" style="max-width:600px; height:auto; border-radius: 8px;" />
        </div>

        <p><strong>Description :</strong><br /><?= nl2br(htmlspecialchars($article['description'])) ?></p>
        <p><strong>Catégorie :</strong> <?= htmlspecialchars($article['categorie']) ?></p>
        <p><strong>Auteur :</strong> <?= htmlspecialchars($article['auteur'] ?? "Inconnu") ?></p>
        <p><strong>Date d'ajout :</strong> <?= htmlspecialchars($article['date_ajout']) ?></p>
    </div>

    <h3 class="mt-5 mb-3">Galerie d'images</h3>
    <?php if (!empty($gallerie_images)): ?>
        <div class="row">
            <?php foreach ($gallerie_images as $img): ?>
                <div class="col-6 col-md-3 mb-3">
                    <img src="<?= htmlspecialchars($img['fichier']) ?>" alt="Image galerie" class="img-thumbnail w-100" />
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucune image dans la galerie.</p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
