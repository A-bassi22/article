<?php
session_start();
require_once("bd.php");
include "inc/header.php";
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    die("Vous devez être connecté !");
}

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

// --- SUPPRESSION ---
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: categorie.php?deleted=1");
    exit();
}

// --- AJOUT CATEGORIE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($nom === "") {
        $error = "Le nom de la catégorie est requis.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (:nom, :description)");
            $stmt->execute([
                ':nom' => $nom,
                ':description' => $description
            ]);
            $success = "Catégorie ajoutée avec succès !";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = (int)$_POST['edit_id'];
    $nom = trim($_POST['edit_nom'] ?? '');
    $description = trim($_POST['edit_description'] ?? '');

    if ($nom !== "") {
        $stmt = $pdo->prepare("UPDATE categories SET nom = :nom, description = :description WHERE id = :id");
        $stmt->execute([
            ':nom' => $nom,
            ':description' => $description,
            ':id' => $id
        ]);
        $success = "Catégorie modifiée avec succès !";
    } else {
        $error = "Le nom de la catégorie est requis.";
    }
}


$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content container mt-4">
    <h2 class="mb-4 text-center">Gestion des catégories</h2>

   
    <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        + Ajouter une catégorie
    </button>

    <?php if(isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if(isset($success)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    
    <table class="table table-striped table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['nom']) ?></td>
                    <td><?= htmlspecialchars($c['description']) ?></td>
                    <td>
                        <!-- Bouton Modifier -->
                        <button class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editCategoryModal<?= $c['id'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- Bouton Supprimer -->
                        <a href="?delete=<?= $c['id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cette catégorie ?');">
                           <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>

                <!-- Modal Modifier catégorie -->
                <div class="modal fade" id="editCategoryModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Modifier catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="edit_category" value="1">
                            <input type="hidden" name="edit_id" value="<?= $c['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="edit_nom" class="form-control" value="<?= htmlspecialchars($c['nom']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="edit_description" class="form-control"><?= htmlspecialchars($c['description']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm">Enregistrer les modifications</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal ajout -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="add_category" value="1">
            <div class="mb-3">
                <label class="form-label">Nom de la catégorie</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Ajouter</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
</body>
</html>
