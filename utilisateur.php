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
// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            $pdo = getDbConnection();

            // Vérifier si le username existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $error = "Ce nom d'utilisateur existe déjà.";
            } else {
                // Hasher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insérer dans la BDD
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password) VALUES (:username, :password)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword
                ]);

                // Message de succès
                $success = "Utilisateur ajouté avec succès !";
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<?php 
include "inc/header.php";
?>

    <div class="main-content">
        <h2>Ajouter un utilisateur</h2>

        <?php if(isset($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(isset($success)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-secondary mb-3">Ajouter</button>
        </form>
    </div>
    <?php include("flooter.php"); ?>
</body>
</html>
