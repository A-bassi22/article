<?php
session_start();
require_once("bd.php");
 $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();} 

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
    /* Style classique et sobre */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    .main-content {
        background-color: #f9f9f9;
        padding: 2rem 0;
    }
    .content {
        background: white;
        padding: 2.5rem;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
    }
    h1, h2, h3 {
        font-weight: 600;
        color: #222;
    }
    h1 {
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
    }
    h3 {
        font-size: 1.3rem;
        margin-top: 2.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eee;
    }
    p {
        margin-bottom: 1rem;
    }
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    /* Image principale */
    .image-principale {
        max-width: 300px;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        margin: 1.5rem auto;
        display: block;
    }

    /* Galerie classique */
    .galerie-item {
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .galerie-image {
        max-width: 100%;
        height: auto;
        border: 1px solid #eee;
        border-radius: 4px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .galerie-image:hover {
        border-color: #ccc;
    }
    .galerie-actions {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }
    .galerie-actions a {
        color: #007bff;
        text-decoration: none;
        margin: 0 0.5rem;
    }
    .galerie-actions a:hover {
        text-decoration: underline;
    }

    /* Lightbox sobre */
    #imagePleinEcran {
        max-width: 90%;
        max-height: 85vh;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .modal-content {
        background: white;
        border: none;
        text-align: center;
        padding: 1rem;
    }
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
    .nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2rem;
        color: #555;
        cursor: pointer;
        user-select: none;
        background: rgba(255,255,255,0.7);
        padding: 0.5rem;
        border-radius: 50%;
    }
    .nav-arrow:hover {
        background: rgba(255,255,255,0.9);
    }
    .nav-arrow.left {
        left: 30px;
    }
    .nav-arrow.right {
        right: 30px;
    }
     {
    outline: 1px solid red;
}
</style>

<?php include "inc/header.php"; ?>

<div class="main-content">
<div class="d-flex justify-content-center" style="padding: 40px 20px;">
        <div class="content w-100" style="max-width: 1100px;">
          <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Détails de l'article</h1>
            </div>
            <a href="accueil" class="btn btn-secondary mb-4">
                <i class="fas fa-arrow-left me-2"></i> Retour à la liste des articles
            </a>

            <div class="card p-4">
                <h2 class="mb-3"><?= htmlspecialchars($article['titre']) ?></h2>

                <div class="text-center">
                    <img src="<?= htmlspecialchars($article['image_principale']) ?>" 
                         alt="Image de couverture" 
                         class="image-principale"
                         data-bs-toggle="modal" 
                         data-bs-target="#lightboxModal" 
                         onclick="ouvrirImage(0)">
                </div>

                <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                <p><strong>Auteur :</strong> <?= htmlspecialchars($article['auteur']) ?></p>
                <p><strong>Catégorie :</strong> <?= htmlspecialchars($article['categorie']) ?></p>
                <p><strong>Date d'ajout :</strong> <?= htmlspecialchars($article['date_ajout']) ?></p>
              </div>

            <h3>Galerie d'images</h3>

            <?php if (!empty($gallerie_images)): ?>
                <div class="row">
                    <?php foreach ($gallerie_images as $index => $img): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3 galerie-item">
                            <img src="<?= htmlspecialchars($img['fichier']) ?>" 
                                 alt="Image galerie" 
                                 class="galerie-image"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#lightboxModal" 
                                 onclick="ouvrirImage(<?= $index + 1 ?>)">
                            <div class="galerie-actions">
                                <a href="<?= htmlspecialchars($img['fichier']) ?>" class="img-lightbox">Voir en grand</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
            
            <?php else: ?>
                <p class="text-muted">Aucune image dans la galerie.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    &larr; Retour
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body position-relative">
                <span class="nav-arrow left" onclick="changerImage(-1)">&#10094;</span>
                <img id="imagePleinEcran" src="" class="img-fluid">
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

// Optionnel : Magnific Popup si tu veux garder le comportement galerie
// $(document).ready(function() {
//     $('.img-lightbox').magnificPopup({type:'image', gallery:{enabled:true}});
// });
</script>
</body>
</html>
