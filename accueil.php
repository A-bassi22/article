<?php
session_start();

require_once("bd.php");
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();}

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

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
    $sql .= " AND (titre LIKE ? OR description LIKE ?)";
    $params[] = "%$searchKeyword%";
    $params[] = "%$searchKeyword%";
}

$sql .= " ORDER BY date_ajout DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php 
include "inc/header.php";
?>

    <!-- Main Content -->
    <div class="main-content">

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
        </div>
                            
        <!-- Tableau des articles -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                 <thead class="table-dark">
                  <tr>
                     <th>Article</th> 
                     <th>Titre</th>
                     <th>Description</th>
                     <th>Catégorie</th>
                     <th>Date d'ajout</th>
                     <th>Auteur</th>
                     <th class="text-center"> Actions</th>
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
                                <td class="text-center" style="width: 90px;">
                                    <?php if (!empty($article['image_principale'])): ?>
                                        <img src="<?= htmlspecialchars($article['image_principale']) ?>" 
                                             alt="Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px;">
                                    <?php else: ?>
                                        <span class="text-muted">Pas d'image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($article['titre']) ?></td>
                                <td>
                                    <?php
                                        $desc = $article['description'];
                                        echo htmlspecialchars(mb_strlen($desc) > 100 ? mb_substr($desc, 0, 100) . '...' : $desc);
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($article['categorie']) ?></td>
                                <td><?= htmlspecialchars($article['date_ajout']) ?></td>
                                <td><?= htmlspecialchars($article['auteur'] ?? 'Inconnu') ?></td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="details_article.php?id=<?= $article['id'] ?>" class="btn btn-primary btn-sm me-1" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="modifier_article.php?id=<?= $article['id'] ?>" class="btn btn-warning btn-sm me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm" title="Supprimer" onclick="supprimerArticle(<?= $article['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
function supprimerArticle(id) {
    if (confirm("Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.")) {
        window.location.href = "supprimer_article.php?id=" + id;
    }
}
</script>
</body>
</html>
