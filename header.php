<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("bd.php");
$username = $_SESSION['username'] ?? null;
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f0f0f1;
            color: #1d2327;
        }
        .topbar {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            height: 50px;
            background: #23282d;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }
        .sidebar {
            width: 220px;
            background-color: #23282d;
            color: #f0f0f1;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        .sidebar h2 {
            padding: 0 20px;
            font-size: 16px;
            margin-bottom: 10px;
            color: #f0f0f1;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            padding: 8px 20px;
        }
        .sidebar li:hover {
            background-color: #2c3338;
            cursor: pointer;
        }
        .sidebar li.active {
            background-color: #2c3338;
        }
        .main-content {
            margin-left: 220px;
            padding: 70px 20px 20px 20px;
            width: calc(100% - 220px);
        }
        .image-center {
    display: flex;        
    justify-content: center;
    align-items: center;  
    height: 100px;      
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        
         <div class="image-center">
    <img src="imag.jpg" style="width:  120px; height: 90px">
</div>
        <ul>
    <li>
        <a href="accueil" style="color:inherit; text-decoration:none;">
            <i class="fas fa-home"></i> Accueil
        </a>
    </li>
    <li>
        <a href="ajout" style="color:inherit; text-decoration:none;">
            <i class="fas fa-sign"></i> Ajouter un article
        </a>
    </li>
    <li>
        <a href="categorie" style="color:inherit; text-decoration:none;">
            <i class="fas fa-tasks"></i> Catégorie
        </a>
    </li>
</ul>
        <ul>
            <li>
                <a href ="utilisateur" style ="color:inherit; text-decoration:none;">
                <i class= "fas fa-user"></I> Utilisateur
                </a>
             </li>   
        </ul>
         <ul>
            <li>
        <a href="logout" style="color:white; text-decoration:none;">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</li>

        </ul>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div></div>
        <div>
            Bienvenue <strong> <?= htmlspecialchars($_SESSION['username']) ?></strong> 
        </div>
    </div>

