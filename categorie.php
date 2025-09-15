<?php
session_start();
require_once("bd.php");
include "inc/header.php";
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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

<div class="main-content">
    <div class="d-flex justify-content-center" style="padding: 40px 20px;">
        <div class="content shadow rounded-3 bg-white p-4 w-100" style="max-width: 1100px;">
          <div class="text-center mb-4">
                <h1 class="h3 fw-bold">les catègories</h1>
            </div>
            <div class="mb-4 text-end">
              <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                 + Ajouter une catégorie
              </button>
           </div>
    <?php if(isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if(isset($success)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
                <table class="users-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); margin-top: 24px;">
            <thead>
        <tr style="background: linear-gradient(135deg, #02c2fe, #02a8d6); color: white;">
            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">ID</th>
            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Nom</th>
            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $c): ?>
                <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.2s ease;">
                    <td class="text-center" style="padding: 16px 20px; color: #334155; font-size: 15px;" class="user-id"><?= htmlspecialchars($c['id']) ?></td>
                    <td class="text-center" style="padding: 16px 20px; color: #334155; font-size: 15px;" class="user-name"><?= htmlspecialchars($c['nom']) ?></td>
                    <td class="text-center"><!-- Bouton Modifier -->
                        <button class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editCategoryModal<?= $c['id'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <!-- Bouton Supprimer -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteCatModal<?= $c['id'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
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
                            <button type="submit" class="btn btn-warning btn-sm">Enregistrer les modifications</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

                 <div class="modal fade" id="confirmDeleteCatModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                         <div class="modal-header">
                             <h5 class="modal-title">Confirmation</h5>
                             <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                         </div>
                         <div class="modal-body">
                         Voulez-vous vraiment supprimer cette catégorie ?
                         </div>
                         <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                              <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger">Supprimer</a>
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
            <button type="submit" class="btn-add"">Ajouter</button>
        </form>
      </div>
    </div>
  </div>
</div>
 </div>
</div>
</body>
</html>
