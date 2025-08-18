<?php
session_start();
require_once("bd.php");
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();}
// Connexion BDD
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
    $sql .= " AND (titre LIKE ? OR description LIKE ?)";
    $params[] = "%$searchKeyword%";
    $params[] = "%$searchKeyword%";
}

$sql .= " ORDER BY date_ajout DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f0f0f1;
            color: #1d2327;
        }
        .topbar {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            height: 50px;
            background: #23282d;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }
        .sidebar {
            width: 220px;
            background-color: #23282d;
            color: #f0f0f1;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        .sidebar h2 {
            padding: 0 20px;
            font-size: 16px;
            margin-bottom: 10px;
            color: #f0f0f1;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            padding: 8px 20px;
        }
        .sidebar li:hover {
            background-color: #2c3338;
            cursor: pointer;
        }
        .sidebar li.active {
            background-color: #2c3338;
        }
        .main-content {
            margin-left: 220px;
            padding: 70px 20px 20px 20px; /* espace pour la topbar */
            width: calc(100% - 220px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div><img src="image.jpg.jpg"></div>
        <ul>
    <li>
        <a href="accueil.php" style="color:inherit; text-decoration:none;">
            <i class="fas fa-home"></i> Accueil
        </a>
    </li>
    <li>
        <a href="ajout.php" style="color:inherit; text-decoration:none;">
            <i class="fas fa-sign"></i> Ajouter un article
        </a>
    </li>
    <li>
        <a href="categorie.php" style="color:inherit; text-decoration:none;">
            <i class="fas fa-tasks"></i> Catégorie
        </a>
    </li>
</ul>
        <ul>
            <li>
                <a href ="utilisateur.php" style ="color:inherit; text-decoration:none;">
                <i class= "fas fa-user"></I> Utilisateur
                </a>
             </li>   
        </ul>
         <ul>
            <li>
    <a href="index.php" style="color:white; text-decoration:none;">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</li>

        </ul>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div></div>
        <div>
             Bienvenue <strong> <?= htmlspecialchars($_SESSION['username']); ?></strong> 
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
