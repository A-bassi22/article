<?php
require_once("bd.php");

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

// Récupération des catégories
try {
    $categories = $pdo->query("SELECT nom FROM categories ORDER BY id")
                      ->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
    error_log("Erreur SQL catégories : " . $e->getMessage());
}

// Filtres
$selectedCategory = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$searchKeyword    = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

// Requête dynamique
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

$sql .= " SELECT * FROM articles ORDER BY date_ajout DESC";

try {  
    $stmt = $pdo->prepare("SELECT * FROM articles ORDER BY date_ajout DESC");
    $stmt->execute();
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
    <title>Gestion des Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="text-center mb-5 ">
        <i class="fas fa-image me-2"></i> Gestion des Articles
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
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search me-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-4 text-end">
            <a href="ajout.php" class="btn btn-secondary rounded-pill">
                <i class="fas fa-plus me-1"></i> Ajouter un article
            </a>
        </div>
    </div>

    <!-- Tableau des articles -->
    <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Image</th> <!-- Nouvelle colonne -->
                <th>Titre</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Date d'ajout</th>
                <th>Auteur</th>
                <th class="text-center"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles)) : ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucun article trouvé.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <!-- Affichage image -->
                        <td class="text-center">
                            <?php if (!empty($article['image_principale'])): ?>
                                <img src="<?= htmlspecialchars($article['image_principale']) ?>" 
                                     alt="Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <span class="text-muted">Pas d'image</span>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($article['titre']) ?></td>
                        <td>
                            <?php
                                $desc = $article['description'];
                                echo htmlspecialchars(strlen($desc) > 100 ? mb_substr($desc, 0, 100) . '...' : $desc);
                            ?>
                        </td>
                        <td><?= htmlspecialchars($article['categorie']) ?></td>
                        <td><?= htmlspecialchars($article['date_ajout']) ?></td>
                        <td><?= htmlspecialchars($article['user'] ?? 'Inconnu') ?></td>
                        <td class="text-center">
                            <a href="details_article.php?id=<?= $article['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <a href="modifier_article.php?id=<?= $article['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="supprimer_article.php?id=<?= $article['id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Voulez-vous vraiment supprimer cet article ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>
