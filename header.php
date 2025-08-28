<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("bd.php");
$username = $_SESSION['username'] ?? null;
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT lien_site FROM parametres WHERE id = 1");
    $param = $stmt->fetch(PDO::FETCH_ASSOC);

    $lien_site = $param['lien_site'] ?? "#"; // valeur par défaut si vide
} catch (Exception $e) {
    $lien_site = "#"; // en cas d’erreur DB
}

try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT logo FROM parametres WHERE id = 1");
    $param = $stmt->fetch(PDO::FETCH_ASSOC);
    $logo = $param['logo'] ?? 'images/default-logo.png'; // logo par défaut
} catch (Exception $e) {
    $logo = 'images/default-logo.png';
}
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT favicon FROM parametres WHERE id = 1");
    $param = $stmt->fetch(PDO::FETCH_ASSOC);
    $favicon = $param['favicon'] ?? 'images/default-favicon.ico';
} catch (Exception $e) {
    $favicon = 'images/default-favicon.ico';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($favicon) ?>" type="image/png">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
    
    @import url('https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Jost', sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        background-color: #f5f6f7;
        color: #000000;
    }

    .topbar {
        position: fixed;
        top: 0;
        left: 220px;
        right: 0;
        height: 60px;
        background: #ffffff; 
        color: #000000; 
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 25px;
        z-index: 1000;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .topbar h1, 
    .topbar span {
        font-size: 18px;
        margin: 0;
    }

    
    .sidebar {
        width: 220px;
        background-color: #ffffff; 
        color: #000000; 
        padding: 20px 0;
        height: 100vh;
        position: fixed;
        overflow-y: auto;
        box-shadow: 2px 0 8px rgba(0,0,0,0.08);
    }

    .sidebar .navbar-brand img {
        display: block;
        margin: 0 auto 15px auto;
        max-height: 70px;
        border-radius: 8px;
    }

    .sidebar h2 {
        padding: 0 20px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        font-weight: 600;
        color: #444444; 
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        padding: 12px 20px;
        transition: all 0.3s ease;
        border-radius: 6px;
        margin: 3px 10px;
    }

    .sidebar li:hover {
        background-color: #f0f0f0;
        cursor: pointer;
    }

    .sidebar li.active {
        background-color: #e6e6e6; 
        font-weight: 600;
    }

    .sidebar a {
        color: #000000; 
        text-decoration: none;
        font-size: 15px;
        display: block;
    }

    .sidebar a i {
        margin-right: 8px;
    }

    
    .main-content {
        margin-left: 220px;
        padding: 80px 25px 25px 25px;
        width: calc(100% - 220px);
        background: #ffffff;
        min-height: 100vh;
        font-weight: 400;
        color: #000000; 
    }
</style>

</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a class="navbar-brand" href="#">
            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo du site" style="height:50px;">
        </a><br>
        <ul>
            <li><a href="accueil"><i class="fas fa-home"></i><strong>accueil</strong></a></li>
            <li>
                  <a href="<?= htmlspecialchars($lien_site) ?>" target="_blank">
                   <i class="fas fa-globe"></i><strong> Visiter le site</strong>
                   </a>
            </li>
            <li><a href="ajout"><i class="fas fa-plus"></i><strong> Ajouter un article</strong></a></li>
            <li><a href="categorie"><i class="fas fa-tasks"></i><strong> Catégories</strong></a></li>
            <li><a href="utilisateurs"><i class="fas fa-user"></i><strong> Utilisateurs</strong></a></li>
            <li><a href="paramètres"><i class="fas fa-gear"></i><strong> Paramètres</strong></a></li>
            <li><a href="logout"><i class="fas fa-sign-out-alt"></i><strong> Déconnexion</strong></a></li>
        </ul>
    </div>
    <!-- Topbar -->
    <div class="topbar">
        <div></div>
        <div>
            Bienvenue <strong> <?= htmlspecialchars($_SESSION['username']) ?></strong> 
        </div>
    </div>

