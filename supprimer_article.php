<?php
include 'bd.php';
$pdo = getDbConnection();

if (isset($_GET['id']) && isset($_GET['article_id'])) {
    $id = intval($_GET['id']);
    $article_id = intval($_GET['article_id']);

    // Récupérer le chemin de l'image
    $stmt = $pdo->prepare("SELECT image_path FROM images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        // Supprimer le fichier du serveur
        if (file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }

        // Supprimer l'enregistrement de la base
        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Retour à la page de l'article
    header("Location: details_articles.php?id=" . $article_id);
    exit;
}
?>
