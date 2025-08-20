<?php
session_start();
require_once("bd.php");
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();}
include "inc/header.php";
date_default_timezone_set('Africa/Niamey');

if (!isset($_SESSION['username'])) {
    die("vous n'est pas connecter");
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>
  <div class="main-content">
    <form action="traiter_article.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <h2>Ajouter  un article</h2>
            <label class="form-label fw-bold">Titre</label>
            <input type="text" name="titre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea name="contenu" class="form-control" rows="6" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Catégorie</label>
            <select name="categorie" class="form-select" required>
                <option value="">-- Choisissez une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nom']) ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Image principale</label>
            <input type="file" name="image" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Images supplémentaires</label>
            <input type="file" name="images[]" class="form-control" multiple>
        </div>
        <div class="mb-3">
    <label for="date" class="form-label fw-bold text-dark d-flex align-items-center">
        <i class="fas fa-calendar-alt text-primary me-2"></i>
        Date et Heure d'Ajout
    </label>
    <input type="datetime-local" 
           id="date" 
           name="date" 
           class="form-control form-control-lg rounded-pill"
           value="<?php echo date('Y-m-d\TH:i'); ?>">
</div><br>

        <input type="hidden" name="auteur" value="<?= htmlspecialchars($username) ?>">

        <button type="submit" class="btn btn-secondary mb-3">
            <i class="fas fa-save me-1"></i> Enregistrer 
        </button>
    </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>