<?php
session_start();
  $_SESSION['username'] = $row['nom_utilisateur'];
require ("bd.php"); // On inclut la connexion


$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

try {
    $pdo = getDbConnection();

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

     if ($user) {
        // On stocke le nom d'utilisateur dans la session
        $_SESSION['username'] = $user['username'];

        // Redirection après connexion
        header("Location: accueil.php");
        exit();
    } else {
        // Échec → retour avec message d'erreur
        header("Location: connexion.php?erreur=1");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
