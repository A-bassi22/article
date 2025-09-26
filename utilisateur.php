<?php
session_start();

require_once("bd.php");
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']); 

if (!isset($_SESSION['username'])) {
    header("Location: login");
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
    header("Location: utilisateurs.php?success=delete");
    exit();
}

// --- AJOUT UTILISATEUR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $mail     = trim($_POST['mail'] ?? '');

    if ($username === "" || $password === "" || $mail === "") {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($password) > 128) {
        $error = "Le mot de passe est trop long (max. 128 caractères).";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $error = "Ce nom d'utilisateur existe déjà.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, mail) VALUES (:username, :password, :mail)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':mail'     => $mail
                ]);
                header("Location: utilisateurs.php?success=add");
                exit();
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// --- RECUPERATION DES UTILISATEURS ---
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- MODIFICATION D’UN UTILISATEUR ---
if (isset($_POST['edit_user'])) {
    $edit_id       = intval($_POST['edit_id']);
    $edit_username = trim($_POST['edit_username']);
    $edit_password = trim($_POST['edit_password'] ?? '');
    $edit_mail     = trim($_POST['edit_mail'] ?? '');

    if ($edit_username === '' || $edit_mail === '') {
        $error = "Le nom d'utilisateur et l'email ne peuvent pas être vides.";
    } elseif (!filter_var($edit_mail, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif ($edit_password !== '' && strlen($edit_password) > 128) {
        $error = "Le mot de passe est trop long (max. 128 caractères).";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username AND id != :id");
            $stmt->execute([':username' => $edit_username, ':id' => $edit_id]);

            if ($stmt->fetch()) {
                $error = "Ce nom d'utilisateur est déjà utilisé.";
            } else {
                if ($edit_password === '') {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET username = :username, mail = :mail WHERE id = :id");
                    $stmt->execute([':username' => $edit_username, ':mail' => $edit_mail, ':id' => $edit_id]);
                } else {
                    $hashed = password_hash($edit_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET username = :username, password = :password, mail = :mail WHERE id = :id");
                    $stmt->execute([':username' => $edit_username, ':password' => $hashed, ':mail' => $edit_mail, ':id' => $edit_id]);
                }
                header("Location: utilisateurs.php?success=edit");
                exit();
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

include "inc/header.php";
?>
<!-- Contenu principal -->
<div class="main-content">
    <div class="d-flex justify-content-center" style="padding: 40px 20px;">
        <div class="content shadow rounded-3 bg-white p-4 w-100" style="max-width: 1100px;">
            <div class="mb-4">
                <h1 class="h3 fw-bold">Gestion des utilusateurs</h1>
            </div>
            <!-- Bouton ajout utilisateur -->
            <div class="mb-4 text-end">
                <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>Ajouter
                </button>
            </div>

            <!-- Messages de succès/erreur -->
            <?php if(isset($error)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <?php
                $messages = [
                    'add' => "Utilisateur ajouté avec succès !",
                    'edit' => "Utilisateur mis à jour avec succès !",
                    'delete' => "Utilisateur supprimé avec succès !"
                ];
                $type = $_GET['success'];
                if (isset($messages[$type])):
                ?>
                    <div class="alert alert-success"><?= htmlspecialchars($messages[$type]) ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tableau utilisateurs -->
            <div class="table-responsive">
                <table class="users-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); margin-top: 24px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #02c2fe, #02a8d6); color: white;">
                            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">ID</th>
                            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Nom</th>
                            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Email</th>
                            <th class="text-center" style="padding: 16px 20px; text-align: left; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1; 
                        foreach($utilisateurs as $u): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.2s ease;">
                                <td class="text-center" style="padding: 16px 20px; color: #334155; font-size: 15px;" class="user-id"><?= $counter++ ?></td>
                                <td class="text-center" style="padding: 16px 20px; color: #334155; font-size: 15px;" class="user-name"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($u['mail']) ?></td>
                                <td class="text-center">        
                                    <!-- Bouton modifier -->
                                    <button class="btn btn-warning btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal<?= $u['id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Bouton supprimer -->
                                    <button class="btn btn-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmDeleteUserModal<?= $u['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal modification -->
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
                                                    <input type="text" name="edit_username" class="form-control" 
                                                           value="<?= htmlspecialchars($u['username']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="edit_mail" class="form-control" 
                                                           value="<?= htmlspecialchars($u['mail']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Mot de passe</label>
                                                    <input type="password" name="edit_password" class="form-control" 
                                                           placeholder="Laisser vide pour garder le mot de passe actuel">
                                                </div>
                                                <button type="submit" name="edit_user" class="btn btn-warning">Modifier</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal suppression -->
                            <div class="modal fade" id="confirmDeleteUserModal<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Voulez-vous vraiment supprimer l’utilisateur <strong><?= htmlspecialchars($u['username']) ?></strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger">Supprimer</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal ajout utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
       <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter un utilisateur</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <form method="POST">
                  <input type="hidden" name="add_user" value="1">
                  <div class="mb-3">
                      <label for="username" class="form-label">Nom d'utilisateur</label>
                      <input type="text" name="username" id="username" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label for="mail" class="form-label">Email</label>
                      <input type="email" name="mail" id="mail" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label for="password" class="form-label">Mot de passe</label>
                      <input type="password" name="password" id="password" class="form-control" required minlength="6">
                  </div>
                  <button type="submit" class="btn-add">Ajouter</button>
              </form>
          </div>
       </div>
    </div>
</div>