<?php
include("bd.php");

try {
    $pdo = getDbConnection();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        die("Méthode non autorisée.");
    }

    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $date = $_POST['date'] ?? '';
    $date = !empty($date) ? $date : date('Y-m-d');

    if (empty($titre) || empty($contenu) || empty($categorie) || empty($date)) {
        die("Veuillez remplir tous les champs obligatoires.");
    }

    // Préparation dossier uploads
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Gestion image de couverture (image principale)
    $image_principale = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            die("Extension de l'image de couverture non valide.");
        }
        if ($_FILES['image']['size'] > $maxSize) {
            die("L'image de couverture dépasse la taille maximale autorisée (5MB).");
        }

        $image_principale = uniqid('cover_') . '.' . $ext;
        $target = $uploadDir . '/' . $image_principale;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            die("Impossible de déplacer l'image de couverture sur le serveur.");
        }
    } else {
        die("Image de couverture requise.");
    }

    // Chemin relatif à stocker en base (pour affichage ultérieur)
    $image_principale_chemin = 'uploads/' . $image_principale;

    // Insertion article avec chemin image principale en base
    $stmt = $pdo->prepare("INSERT INTO articles (titre, description, categorie, image_principale, date_ajout) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $contenu, $categorie, $image_principale_chemin, $date]);

    $id_article = $pdo->lastInsertId();

    // Gestion images supplémentaires (galerie)
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $index => $name) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    continue; // Ignore extension non valide
                }
                if ($_FILES['images']['size'][$index] > $maxSize) {
                    continue; // Ignore fichiers trop gros
                }

                $file_name = uniqid('img_') . '.' . $ext;
                $target = $uploadDir . '/' . $file_name;

                if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $target)) {
                    // Chemin relatif à stocker en base
                    $fichier_chemin = 'uploads/' . $file_name;

                    $stmtImg = $pdo->prepare("INSERT INTO gallerie_images (article_id, fichier) VALUES (?, ?)");
                    $stmtImg->execute([$id_article, $fichier_chemin]);
                }
            }
        }
    }

    header("Location: galerie.php?success=1");
    exit();

} catch (Exception $e) {
    error_log("Erreur traitement article : " . $e->getMessage());
    die("Erreur : " . $e->getMessage());
}
