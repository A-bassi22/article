<?php
session_start();
require_once "bd.php";
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'] ?? 'Inconnu';

$pdo = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $date = $_POST['date'] ?? '';
    $date = !empty($date) ? $date : date('Y-m-d\TH:i');

    if (empty($titre) || empty($contenu) || empty($categorie) || empty($date)) {
        die("Veuillez remplir tous les champs obligatoires.");
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; 

    
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

    $image_principale_chemin = 'uploads/' . $image_principale;

    // Insertion article
    $stmt = $pdo->prepare("INSERT INTO articles (titre, description, categorie, image_principale, date_ajout, auteur) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $contenu, $categorie, $image_principale_chemin, $date, $username]);

    $id_article = $pdo->lastInsertId();

    // Gestion galerie
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $index => $name) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    continue;
                }
                if ($_FILES['images']['size'][$index] > $maxSize) {
                    continue;
                }

                $file_name = uniqid('img_') . '.' . $ext;
                $target = $uploadDir . '/' . $file_name;

                if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $target)) {
                    $fichier_chemin = 'uploads/' . $file_name;

                    $stmtImg = $pdo->prepare("INSERT INTO gallerie_images (article_id, fichier) VALUES (?, ?)");
                    $stmtImg->execute([$id_article, $fichier_chemin]);
                }
            }
        }
    }

    header("Location: accueil.php?success=1");
    exit();
}


$categories = [];
try {
    $q = $pdo->query("SELECT nom FROM categories ORDER BY nom ASC");
    $categories = $q->fetchAll(PDO::FETCH_COLUMN);
    if (!$categories) {
        $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
        $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Throwable $e) {
    $q2 = $pdo->query("SELECT DISTINCT categorie AS nom FROM articles WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY nom ASC");
    $categories = $q2->fetchAll(PDO::FETCH_COLUMN);
}

include "inc/header.php";
?>

<div class="main-content">
    <div>
        <a href="accueil.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
        </a>
        <h2 class="mb-4 text-center">Ajouter un article</h2>
    </div>

    <form method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Titre :</label>
            <input type="text" class="form-control" name="titre" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contenu :</label>
            <textarea class="form-control" name="contenu" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégorie :</label>
            <select name="categorie" class="form-select" required>
                <option value="">-- Choisissez une catégorie --</option>
                <?php foreach ($categories as $catName): ?>
                    <option value="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <input type="datetime-local" 
           id="date" 
           name="date" 
           class="form-control form-control-lg rounded-pill"
           value="<?php echo date('Y-m-d\TH:i'); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Image principale :</label>
            <input type="file" name="image" accept="image/*" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Images supplémentaires (galerie) :</label>
            <input type="file" name="images[]" multiple accept="image/*" class="form-control">
        </div>

        <div class="text-end">
           <button type="submit" class="btn btn-secondary mb-3">
            <i class="fas fa-save me-1"></i> Enregistrer 
        </button>
        </div>
    </form>
</div>
</body>
</html>
