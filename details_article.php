<?php
session_start();
require_once("bd.php");
 $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();} 
include "inc/header.php";

if (!isset($_SESSION['username'])) {
    die("vous n'est pas connecter");
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = (int) $_GET['id'];

try {
    $pdo = getDbConnection();

    // Récupération de l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article introuvable.");
    }

    // Récupération des images de la galerie
    $stmt_images = $pdo->prepare("SELECT * FROM gallerie_images WHERE article_id = ?");
    $stmt_images->execute([$id]);
    $gallerie_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

    <style>
    
        #imagePleinEcran {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }
        .modal-content {
            background: transparent;
            border: none;
            text-align: center;
        }
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: white;
            cursor: pointer;
            user-select: none;
        }
        .nav-arrow.left {
            left: 20px;
        }
        .nav-arrow.right {
            right: 20px;
        }
    </style>
    <?php 
include "inc/header.php";
?>
  <div class="main-content">
    <div>
        <a href="accueil" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
        </a>    
    <h2 class="mb-4">Détails de l'article</h2>
    </div>
    <div class="card p-4 shadow-sm">
        <h1 class="mb-3"><?= htmlspecialchars($article['titre']) ?></h1>

        <!-- Image principale (cliquable en plein écran) -->
        <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($article['image_principale']) ?>" 
                 alt="Image de couverture" 
                 class="img-fluid" 
                 style="max-width:300px; height:auto; border-radius: 5px; cursor:pointer;"
                 data-bs-toggle="modal" 
                 data-bs-target="#lightboxModal" 
                 onclick="ouvrirImage(0)">
        </div>

        <p><strong>Description :</strong><br /><?= nl2br(htmlspecialchars($article['description'])) ?></p>
        <p class="text-muted">Auteur : <?= htmlspecialchars($article['auteur']) ?></p>
        <p><strong>Catégorie :</strong> <?= htmlspecialchars($article['categorie']) ?></p>
        <p><strong>Date d'ajout :</strong> <?= htmlspecialchars($article['date_ajout']) ?></p>
    </div>

    <h3 class="mt-5 mb-3">Galerie d'images</h3>
    <?php if (!empty($gallerie_images)): ?>
        <div class="row g-3">
            <?php foreach ($gallerie_images as $index => $img): ?>
                <div class="col-6 col-md-3 text-center">
                    <img src="<?= htmlspecialchars($img['fichier']) ?>" 
                         class="img-fluid img-thumbnail"
                         alt="Image galerie"
                         style="cursor:pointer;"
                         data-bs-toggle="modal"
                         data-bs-target="#lightboxModal"
                         onclick="ouvrirImage(<?= $index+1 ?>)">
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucune image dans la galerie.</p>
    <?php endif; ?>

</div>

<!-- Modal Bootstrap pour afficher les images en plein écran -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen">
    <div class="modal-content">
        <div class="modal-header border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          &larr; Retour
        </button>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body position-relative">
        <span class="nav-arrow left" onclick="changerImage(-1)">&#10094;</span>
        <img id="imagePleinEcran" src="" class="img-fluid rounded shadow">
        <span class="nav-arrow right" onclick="changerImage(1)">&#10095;</span>
      </div>
    </div>
  </div>
</div>

<script>
let images = [
    "<?= htmlspecialchars($article['image_principale']) ?>",
    <?php foreach ($gallerie_images as $img): ?>
        "<?= htmlspecialchars($img['fichier']) ?>",
    <?php endforeach; ?>
];
let currentIndex = 0;

function ouvrirImage(index) {
    currentIndex = index;
    document.getElementById('imagePleinEcran').src = images[currentIndex];
}

function changerImage(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = images.length - 1;
    if (currentIndex >= images.length) currentIndex = 0;
    document.getElementById('imagePleinEcran').src = images[currentIndex];
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include("footer.php"); ?>
</body>
</html>
