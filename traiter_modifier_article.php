<?php
require_once("bd.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée.");
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    die("ID article invalide.");
}

$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$categorie = trim($_POST['categorie'] ?? '');
$auteur = trim($_POST['auteur'] ?? '');
$date_ajout = $_POST['date_ajout'] ?? '';

if (empty($titre) || empty($description) || empty($categorie) || empty($date_ajout)) {
    die("Veuillez remplir tous les champs obligatoires.");
}

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("SELECT image_principale FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article introuvable.");
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    $image_principale_chemin = $article['image_principale'];

    if (!empty($_FILES['image_principale']['name']) && $_FILES['image_principale']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image_principale']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            die("Extension non valide.");
        }
        if ($_FILES['image_principale']['size'] > $maxSize) {
            die("Fichier trop volumineux.");
        }

        $nouveauNom = uniqid('cover_') . '.' . $ext;
        $target = $uploadDir . '/' . $nouveauNom;

        if (move_uploaded_file($_FILES['image_principale']['tmp_name'], $target)) {
            if ($image_principale_chemin && file_exists(__DIR__ . '/' . $image_principale_chemin)) {
                unlink(__DIR__ . '/' . $image_principale_chemin);
            }
            $image_principale_chemin = 'uploads/' . $nouveauNom;
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE articles 
        SET titre = ?, description = ?, categorie = ?, image_principale = ?, auteur = ?, date_ajout = ? 
        WHERE id = ?");
    $stmtUpdate->execute([$titre, $description, $categorie, $image_principale_chemin, $auteur, $date_ajout, $id]);

    header("Location: details_article.php?id=$id&success=1");
    exit();

} catch (Exception $e) {
    die("Erreur lors de la modification : " . $e->getMessage());
}
