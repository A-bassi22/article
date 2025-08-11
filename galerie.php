<?php
require_once("bd.php");

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

// Récupération des catégories (sécurisé)
try {
    $categories = $pdo->query("SELECT nom FROM categories ORDER BY id")
                      ->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
    error_log("Erreur SQL catégories : " . $e->getMessage());
}

// Initialisation des filtres
$selectedCategory = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$searchKeyword    = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

// Requête dynamique avec paramètres
$sql = "SELECT * FROM articles WHERE 1";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND categorie = ?";
    $params[] = $selectedCategory;
}

if (!empty($searchKeyword)) {
    $sql .= " AND (titre LIKE ? OR contenu LIKE ?)";
    $params[] = '%' . $searchKeyword . '%';
    $params[] = '%' . $searchKeyword . '%';
}

$sql .= " ORDER BY date_ajout DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $articles = [];
    error_log("Erreur SQL articles : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Galerie des Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-img-top { height: 220px; object-fit: cover; }
        .stretched-link { z-index: 1; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="text-center mb-5 text-primary">
        <i class="fas fa-image me-2"></i>Galerie des Articles
    </h1>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="get" class="row gx-2 gy-2">
                <div class="col-md-5">
                    <select name="categorie" class="form-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat === $selectedCategory) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="recherche" class="form-control" placeholder="Rechercher un article..."
                           value="<?= htmlspecialchars($searchKeyword) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-4 text-end">
            <a href="ajout.php" class="btn btn-primary rounded-pill">
                <i class="fas fa-plus me-1"></i> Ajouter un article
            </a>
        </div>
    </div>

    <!-- Liste des articles -->
    <div class="row">
        <?php if (empty($articles)) : ?>
            <p class="text-center text-muted">Aucun article trouvé.</p>
        <?php else : ?>
            <?php foreach ($articles as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 position-relative">
                        <a href="details_article.php?id=<?= $article['id'] ?>" class="stretched-link text-decoration-none">
                            <img src="<?= htmlspecialchars($article['image_principale']) ?>" class="card-img-top" alt="Image de couverture">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($article['titre']) ?></h5>
                            <p class="card-text text-muted mb-1">
                                <strong>Description :</strong>
                                <?php
                                $desc = $article['description'];
                                echo htmlspecialchars(strlen($desc) > 100 ? mb_substr($desc, 0, 100) . '...' : $desc);
                                 ?>
                            </p>

                            <p class="card-text text-muted mb-1">
                                <strong>Catégorie :</strong> <?= htmlspecialchars($article['categorie']) ?>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">Ajouté le : <?= htmlspecialchars($article['date_ajout']) ?></small>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
