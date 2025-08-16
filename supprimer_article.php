<?php
include 'bd.php';
$pdo = getDbConnection();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $article_id = (int)$_GET['id'];

    try {
        // Récupérer l'article avec son image principale
        $stmt = $pdo->prepare("SELECT image_principale FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            die("Article introuvable.");
        }

        // Supprimer l'image principale du serveur si elle existe
        if (!empty($article['image_principale']) && file_exists($article['image_principale'])) {
            unlink($article['image_principale']);
        }

        // Récupérer toutes les images de la galerie associées à l'article
        $stmt = $pdo->prepare("SELECT fichier FROM gallerie_images WHERE article_id = ?");
        $stmt->execute([$article_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Supprimer physiquement chaque image de la galerie
        foreach ($images as $img) {
            if (!empty($img['fichier']) && file_exists($img['fichier'])) {
                unlink($img['fichier']);
            }
        }

        // Supprimer les entrées dans la table gallerie_images
        $stmt = $pdo->prepare("DELETE FROM gallerie_images WHERE article_id = ?");
        $stmt->execute([$article_id]);

        // Supprimer l'article
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);

        // Redirection vers la galerie avec message de succès
        header("Location: galerie.php?deleted=1");
        exit;

    } catch (Exception $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }

} else {
    die("ID article invalide.");
}
?>
