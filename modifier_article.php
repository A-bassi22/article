<?php
include 'bd.php';
$pdo = getDbConnection();

if (isset($_GET['id']) && isset($_GET['article_id'])) {
    $id = intval($_GET['id']);
    $article_id = intval($_GET['article_id']);

    // Récupérer l'image actuelle
    $stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nouvelle_image'])) {
        $file = $_FILES['nouvelle_image'];

        if ($file['error'] === 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $target_file);

            // Supprimer l'ancienne image
            if (file_exists($image['image_path'])) {
                unlink($image['image_path']);
            }

            // Mettre à jour la base
            $stmt = $pdo->prepare("UPDATE images SET image_path = ? WHERE id = ?");
            $stmt->execute([$target_file, $id]);

            header("Location: details_articles.php?id=" . $article_id);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier Image</title>
</head>
<body>
    <h2>Modifier l'image</h2>
    <?php if ($image): ?>
        <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Image actuelle" style="max-width:200px;">
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="nouvelle_image" required>
        <button type="submit">Mettre à jour</button>
    </form>
</body>
</html>
