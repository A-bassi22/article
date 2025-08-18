<?php
session_start();
require_once("bd.php");
include("header.php");

if (!isset($_SESSION['username'])) {
    die("Vous devez être connecté !");
}
$auteur = $_SESSION['username'];

// Vérifier si le formulaire est soumis
if (isset($_POST['submit'])) {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);

    if ($nom == "") {
        $error = "Le nom de la catégorie est requis.";
    } else {
        try {
            $pdo = getDbConnection(); // fonction qui retourne l'objet PDO

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <h2>Ajouter une catégorie</h2>

        <?php if(isset($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(isset($success)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la catégorie</label>
                <input type="text" name="nom" id="nom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-secondry">Ajouter</button>
        </form> 
    </div>
    <?php include("footer.php"); ?>
</body>
</html>
