<?php
session_start();
require_once("bd.php");
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 

// Vérification de session
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
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: utilisateurs.php?deleted=1");
    exit();
}

// --- AJOUT UTILISATEUR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $error = "Ce nom d'utilisateur existe déjà.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password) VALUES (:username, :password)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword
                ]);
                $success = "Utilisateur ajouté avec succès !";
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// --- RECUPERATION DES UTILISATEURS ---
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
// Modification d’un utilisateur
if (isset($_POST['edit_user'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_username = trim($_POST['edit_username']);
    $edit_password = trim($_POST['edit_password'] ?? '');

    if ($edit_username === '') {
        $error = "Le nom d'utilisateur ne peut pas être vide.";
    } else {
        try {
            $pdo = getDbConnection();

            // Vérifier si le nouveau nom existe déjà pour un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username AND id != :id");
            $stmt->execute([':username' => $edit_username, ':id' => $edit_id]);
            if ($stmt->fetch()) {
                $error = "Ce nom d'utilisateur est déjà utilisé.";
            } else {
                // Si mot de passe vide, on garde l'ancien
                if ($edit_password === '') {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET username = :username WHERE id = :id");
                    $stmt->execute([':username' => $edit_username, ':id' => $edit_id]);
                } else {
                    $hashed = password_hash($edit_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET username = :username, password = :password WHERE id = :id");
                    $stmt->execute([':username' => $edit_username, ':password' => $hashed, ':id' => $edit_id]);
                }
                $success = "Utilisateur modifié avec succès !";
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}


include "inc/header.php";
?>
<div class="main-content">
    <h2 class="mb-4 text-center">Gestion des utilisateurs</h2>

    <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
         Ajouter un utilisateur
    </button>

    <?php if(isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if(isset($success)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

   
    <table class="table table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Mot de passe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($utilisateurs as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td class="text-center">**********</td>
                    <td>
                        
                        <button class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editUserModal<?= $u['id'] ?>">
                                <i class="fas fa-edit"></i>
                        </button>

                        
                        <a href="?delete=<?= $u['id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cet utilisateur ?');">
                           <i class="fas fa-trash"></i>
                        </a>
                        
                    </td>
                </tr>

            <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
               <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Ajouter un utilisateur</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="add_user" value="1">
                      <div class="mb-3">
                         <label for="username" class="form-label">Nom d'utilisateur</label>
                          <input type="text" name="username" id="username" class="form-control" required>
                      </div>
                      <div class="mb-3">
                         <label for="password" class="form-label">Mot de passe</label>
                         <input type="password" name="password" id="password" class="form-control" required>
                      </div>
                      <button type="submit" class="btn btn-success">Ajouter</button>
                    </form>
               </div>
             </div>
            </div>
          </div>
          </div>

                
                <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Modifier l'utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="edit_id" value="<?= $u['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur</label>
                                <input type="text" name="edit_username" class="form-control" value="<?= htmlspecialchars($u['username']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="edit_password" class="form-control" placeholder="Laisser vide pour garder le mot de passe actuel">
                            </div>
                            <button type="submit" name="edit_user" class="btn btn-warning">Modifier</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
 
</div>
</body>
</html>