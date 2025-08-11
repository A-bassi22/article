<?php
include("bd.php");

try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];  // pour éviter les erreurs en cas de souci
    error_log("Erreur récupération catégories : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Nouvel Article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
             <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden" >
                    <div class="card-body p-5">
                        <h1 class="display-5 fw-bold text-dark mb-5 text-center position-relative">
                            <i class="fas fa-plus-circle text-primary me-2"></i>
                            Ajouter un Nouvel Article
                            <span class="position-absolute bottom-0 start-50 translate-middle-x" style="width: 80px; height: 3px; background-color: #0d6efd; border-radius: 2px;"></span>
                        </h1>

                        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                            <div class="alert alert-success">L'article a été ajouté avec succès.</div>
                        <?php endif; ?>

                        <div class="alert alert-info d-flex align-items-center p-3 mb-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>Remplissez le formulaire ci-dessous pour ajouter un nouvel article.</span>
                        </div>

                        <form id="articleForm" action="traiter_article.php" method="POST" enctype="multipart/form-data">

                            <div class="mb-3">
                                <label for="titre" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-pencil text-primary me-2"></i>
                                    Titre de l'article <span class="text-danger ms-1">*</span>
                                </label>
                                <input type="text" id="titre" name="titre" class="form-control form-control-lg rounded-pill" required maxlength="255"
                                    placeholder="Entrez le titre de votre article">
                            </div><br>

                            <div class="mb-3">
                                <label for="contenu" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-edit text-primary me-2"></i>
                                    Description <span class="text-danger ms-1">*</span>
                                </label>
                                <textarea id="contenu" name="contenu" class="form-control form-control-lg rounded-3" required rows="8" placeholder="Rédigez une description de votre article ici..."></textarea>
                            </div><br>

                            <div class="mb-3">
                                <label for="categorie" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-tags text-primary me-2"></i>
                                    Catégorie d'Articles <span class="text-danger ms-1">*</span>
                                </label>
                                <select id="categorie" name="categorie" class="form-select form-select-lg rounded-pill" required placeholder="Sélectionnez une catégorie">
                                    <option value="">Sélectionnez une catégorie</option>

                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['nom']) ?>">
                                            <?= htmlspecialchars($category['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>


                                </select>



                            </div><br>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-image text-primary me-2"></i>
                                    Image de couverture <span class="text-danger ms-1">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="file" id="image" name="image" class="form-control rounded-pill" required accept="image/*">
                                </div>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Formats acceptés : JPG, PNG, GIF, WebP (max 5 MB)
                                </small>
                            </div><br>

                            <div class="mb-3">
                                <label for="images" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-images text-primary me-2"></i>
                                    Images supplémentaires (max 10)
                                </label>
                                <div class="input-group">
                                    <input type="file" id="images" name="images[]" class="form-control rounded-pill" accept="image/*" multiple>
                                </div>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Vous pouvez sélectionner jusqu'à 10 images.Taille max : 5 MB/image
                                </small>
                            </div><br>

                            <div class="mb-3">
                                <label for="date" class="form-label fw-bold text-dark d-flex align-items-center">
                                    <i class="fas fa-date text-primary me-2"></i>
                                    Date d'Ajout <span class="text-danger ms-1">*</span>
                                </label>
                                <input type="date" id="date" name="date" class="form-control form-control-lg rounded-pill" required
                                    placeholder="Ajouter une date">
                            </div><br>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Ajouter l'Article</i>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-5 pt-4 border-top">
                            <a href="galerie.php" class="btn btn-outline-primary btn-lg rounded-pill">
                                <i class="fas fa-list me-2"></i>
                                Voir tous les articles disponible
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
