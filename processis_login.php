<?php
require ("bd.php"); // On inclut la connexion

// Récupération des valeurs du formulaire
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

try {
    $pdo = getDbConnection();

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe
    if ($user && hash('sha256', $password) === $user['password']) {
        // Connexion réussie → redirection vers ajout.php
        header("Location: ajout.php");
        exit;
    } else {
        // Échec → retour avec message d'erreur
        header("Location: connexion.php?erreur=1");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
