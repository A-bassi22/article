<?php
session_start();

require_once("bd.php");
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit();
}

$username = $_SESSION['username'];


try {
    $pdo = getDbConnection();
    // Récupérer ID et nom des catégories
    $stmtCat = $pdo->query("SELECT id, nom FROM categories ORDER BY id");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Traitement POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['titre'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $date = $_POST['date'] ?? '';
    $date = !empty($date) ? $date : date('Y-m-d\TH:i');

    if (empty($titre) || empty($contenu) || empty($categorie) || empty($date)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        $image_principale = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = "Extension de l'image de couverture non valide.";
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $_SESSION['error'] = "L'image de couverture dépasse la taille maximale autorisée (5MB).";
            } else {
                $image_principale = uniqid('cover_') . '.' . $ext;
                $target = $uploadDir . '/' . $image_principale;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $image_principale_chemin = 'uploads/' . $image_principale;

                    $stmt = $pdo->prepare("INSERT INTO articles (titre, description, categorie, image_principale, date_ajout, auteur) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$titre, $contenu, $categorie, $image_principale_chemin, $date, $username])) {
                        $id_article = $pdo->lastInsertId();

                        if (!empty($_FILES['images']['name'][0])) {
                            foreach ($_FILES['images']['name'] as $index => $name) {
                                if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                    if (!in_array($ext, $allowed) || $_FILES['images']['size'][$index] > $maxSize) continue;
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
                        $_SESSION['success'] = "Article ajouté avec succès !";
                    } else {
                        $_SESSION['error'] = "Erreur lors de l'enregistrement de l'article.";
                    }
                } else {
                    $_SESSION['error'] = "Impossible de déplacer l'image de couverture sur le serveur.";
                }
            }
        } else {
            $_SESSION['error'] = "Image de couverture requise.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Filtres & Pagination (inchangés)
$selectedCategory = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$searchKeyword = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

$itemsPerPage = 10;
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));

$sqlCount = "SELECT COUNT(*) FROM articles WHERE 1";
$params = [];

if (!empty($selectedCategory)) { $sqlCount .= " AND categorie = ?"; $params[] = $selectedCategory; }
if (!empty($searchKeyword))   { $sqlCount .= " AND (titre LIKE ? OR description LIKE ? OR categorie LIKE ?)"; $params[] = "%$searchKeyword%"; $params[] = "%$searchKeyword%"; $params[] = "%$searchKeyword%"; }

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalItems = $stmtCount->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

$sql = "SELECT * FROM articles WHERE 1";
$params = [];

if (!empty($selectedCategory)) { $sql .= " AND categorie = ?"; $params[] = $selectedCategory; }
if (!empty($searchKeyword))   { $sql .= " AND (titre LIKE ? OR description LIKE ? OR categorie LIKE ?)"; $params[] = "%$searchKeyword%"; $params[] = "%$searchKeyword%"; $params[] = "%$searchKeyword%"; }

$sql .= " ORDER BY date_ajout ASC LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = ($page - 1) * $itemsPerPage;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "inc/header.php"; ?>

<div class="main-content">
    <div class="d-flex justify-content-center" style="padding: 40px 20px;">
        <div class="content shadow rounded-3 bg-white p-4 w-100" style="max-width: 1100px;">
            <!-- Titre + Bouton Ajouter -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold text-dark">Gestion des articles</h1>
            </div>
            <div class="mb-4 text-end">
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addArticleModal">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </button>
              </div>
            <!-- Filtres -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row gx-2 gy-2 align-items-end">
                        <!-- Sélection par ID de catégorie -->
                        <div class="col-md-4">
                            <label for="categorieSelect" class="form-label visually-hidden">Catégorie</label>
                            <select id="categorieSelect" class="form-select">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Recherche en temps réel -->
                        <div class="col-md-8">
                            <label for="searchLive" class="form-label visually-hidden">Rechercher</label>
                            <input 
                                type="text" 
                                id="searchLive" 
                                class="form-control" 
                                placeholder="Rechercher (titre, auteur, catégorie)..."
                            >
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tableau des articles -->
            <div class="table-responsive">
            
                <table class="users-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); margin-top: 24px;">
         <thead>
         <tr style="background: linear-gradient(135deg, #02c2fe, #02a8d6); color: white;">
            <th class="text-center" style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Photo</th>
            <th class="text-center" style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Titre</th>
            <th class="text-center" style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Description</th>
            <th class="text-center" style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Catégorie</th>
            <th class="text-center" style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Date d'ajout</th>
            <th style="padding: 16px 20px; text-align: center; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Auteur</th>
            <th class="actions-col">Actions</th>
         </tr>
         </thead>
                    <tbody>
                        <?php if (empty($articles)): ?>
                            <tr><td colspan="7" class="text-center text-muted">Aucun article trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($articles as $article): ?>
                                 <tr class="article-row"
                                     data-id="<?= $article['id'] ?>"
                                     data-titre="<?= htmlspecialchars(strtolower($article['titre'])) ?>"
                                     data-auteur="<?= htmlspecialchars(strtolower($article['auteur'] ?? '')) ?>"
                                     data-categorie-id="<?= $article['categorie_id'] ?? '' ?>"
                                     data-categorie-nom="<?= htmlspecialchars(strtolower($article['categorie'])) ?>">
                                    <td class="text-center" style="padding: 16px 20px; text-align: center; color: #334155; font-size: 15px;">
                                        <?php if (!empty($article['image_principale'])): ?>
                                            <img src="<?= htmlspecialchars($article['image_principale']) ?>" alt="Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px;">
                                        <?php else: ?>
                                            <span class="text-muted">Pas d'image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="padding: 16px 20px; color: #334155; font-size: 15px;" class="user-id"><?= htmlspecialchars($article['titre']) ?></td>
                                    <td class="text-center" style="padding: 16px 20px; text-align: center; color: #334155; font-size: 15px;">
                                        <?= htmlspecialchars(mb_strlen($article['description']) > 100 ? mb_substr($article['description'], 0, 100) . '...' : $article['description']) ?>
                                    </td>
                                    <td class="text-center" style="padding: 16px 20px; text-align: center; color: #334155; font-size: 15px;">
                                       <?= htmlspecialchars($article['categorie']) ?>
                                    </td>
                                    <td class="text-center" style="padding: 16px 20px; text-align: center; color: #334155; font-size: 15px;">
                                       <?= htmlspecialchars($article['date_ajout']) ?>
                                    </td>
                                    <td class="text-center" style="padding: 16px 20px; text-align: center; color: #334155; font-size: 15px;">
                                        <?= htmlspecialchars($article['auteur'] ?? 'Inconnu') ?>
                                    </td>
                                    <td class="text-center" style="text-align: center; color: #334155; font-size: 15px;">
                                         <a href="#" class="btn btn-warning btn-sm me-1" title="Modifier" data-bs-toggle="modal" data-bs-target="#editArticleModal" data-article-id="<?= $article['id'] ?>">
    <i class="fas fa-edit"></i>
</a>
                                        <button type="button" class="btn btn-danger btn-sm m1" title="Supprimer" data-bs-toggle="modal" data-bs-target="#confirmDeleteArticleModal<?= $article['id'] ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>


                                <!-- Modal suppression -->
                                <div class="modal fade" id="confirmDeleteArticleModal<?= $article['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">Voulez-vous vraiment supprimer cet article ?</div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <a href="supprimer_article.php?id=<?= (int)$article['id'] ?>" class="btn btn-danger">Supprimer</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

              <!-- Pagination -->
               <?php if ($totalPages > 1): ?>
                <nav class="mt-4" aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&categorie=<?= urlencode($selectedCategory) ?>&recherche=<?= urlencode($searchKeyword) ?>" aria-label="Previous">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&categorie=<?= urlencode($selectedCategory) ?>&recherche=<?= urlencode($searchKeyword) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&categorie=<?= urlencode($selectedCategory) ?>&recherche=<?= urlencode($searchKeyword) ?>" aria-label="Next">&raquo;</a>
                        </li>
                    </ul>
                </nav>
              <?php endif; ?>
          </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Article -->
<div class="modal fade" id="addArticleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
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
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="datetime-local" id="date" name="date" class="form-control form-control-lg rounded-pill" value="<?= date('Y-m-d\TH:i'); ?>">
                        <small class="text-muted">Date actuelle : <?= date('d/m/Y H:i'); ?></small>
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
                        <button type="submit" class="btn-add"><i class="fas fa-save me-1"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal pour modifier l'article -->
<div class="modal fade" id="editArticleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"  style="max-width: 95vw; max-height: 95vh;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editArticleModalLabel">Modifier l'article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="editArticleIframe" src="" frameborder="0" style="width: 100%; height: 85vh;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editArticleModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const articleId = button.getAttribute('data-article-id');
        const iframe = editModal.querySelector('#editArticleIframe');
        iframe.src = 'modifier_article.php?id=' + articleId + '&modal=1';
    });
    
    editModal.addEventListener('hidden.bs.modal', function () {
        const iframe = editModal.querySelector('#editArticleIframe');
        iframe.src = '';
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const defaultDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        const datetimeInput = document.getElementById('date');
        if (datetimeInput) datetimeInput.value = defaultDateTime;
    });

    // Filtrage en temps réel
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchLive');
        const categorieSelect = document.getElementById('categorieSelect');
        const articleRows = document.querySelectorAll('.article-row');

        function filterArticles() {
            const searchText = searchInput.value.toLowerCase().trim();
            const selectedCatId = categorieSelect.value;

            articleRows.forEach(row => {
                const titre = row.dataset.titre || '';
                const auteur = row.dataset.auteur || '';
                const catNom = row.dataset.categorieNom || '';
                const catId = row.dataset.categorieId || '';

                // Vérifier la catégorie
                const categoryMatch = !selectedCatId || catId === selectedCatId;

                // Vérifier la recherche (début des mots)
                const textMatch = (
                    searchText === '' ||
                    titre.startsWith(searchText) ||
                    auteur.startsWith(searchText) ||
                    catNom.startsWith(searchText)
                );

                row.style.display = (categoryMatch && textMatch) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterArticles);
        categorieSelect.addEventListener('change', filterArticles);
    });
</script>