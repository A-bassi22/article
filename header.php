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
    $lien_site = $param['lien_site'] ?? "#"; 
} catch (Exception $e) {
    $lien_site = "#"; 
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT logo FROM parametres WHERE id = 1");
    $param = $stmt->fetch(PDO::FETCH_ASSOC);
    $logo = $param['logo'] ?? 'images/default-logo.png'; 
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
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($favicon) ?>" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Jost', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #fafafa;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 2px 0 15px rgba(0,0,0,0.05);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .logo {
            padding: 0 24px 24px;
            border-bottom: 1px solid #eee;
            margin-bottom: 24px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 700;
            color: #02c2fe;
        }

        .nav-item {
            padding: 0 24px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            background-color: #f0f9ff;
            color: #02c2fe;
        }

        .nav-link.active {
            background-color: #e6f7ff;
            color: #02c2fe;
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
        }

        .main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            padding-top: 20px; /* Petit espace en haut, ajustable */
            transition: margin-left 0.3s ease;
        }

        .content {
            background: white;
            border-radius: 16px;
            margin: 0 32px 32px; /* Plus de marge en haut ! */
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            position: relative;
            width: calc(100% - 64px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main {
                margin-left: 0;
                padding-top: 20px;
            }
            
            .content {
                margin: 0 24px 24px;
                padding: 24px;
            }
        }

        @media (max-width: 768px) {
            .content {
                margin: 0 16px 16px;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .content {
                margin: 0 12px 12px;
                padding: 16px;
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        @media (max-width: 1024px) {
            .sidebar-overlay.active {
                display: block;
            }
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 101;
            cursor: pointer;
            font-size: 20px;
        }

        @media (max-width: 1024px) {
            .mobile-toggle {
                display: block;
            }
        }

        .btn-add {
            background-color: #02c2fe;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(2, 194, 254, 0.2);
        }

        .btn-add:hover {
            background-color: #02a8d6;
            transform: translateY(-2px);
        }
    </style>    
</head>
<body>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(event.target) && 
                event.target !== mobileToggle && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (window.innerWidth > 1024) {
                sidebar.classList.add('active');
                overlay.classList.remove('active');
            } else {
                sidebar.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.remove('active');
            }

            document.querySelector('.mobile-toggle').addEventListener('click', toggleSidebar);
        });
    </script>
    
    <button class="mobile-toggle">☰</button>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
             <a class="navbar-brand" href="#">
                <img src="<?= htmlspecialchars($logo) ?>" alt="Logo du site" style="height:50px;">
             </a>
        </div>

        <nav>
            <div class="nav-item">
                <a href="accueil" class="nav-link <?= $current_page === 'accueil' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>Accueil
                </a>
            </div>

            <div class="nav-item">
                <a href="<?= htmlspecialchars($lien_site) ?>" class="nav-link" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-globe"></i>Visiter le site
                </a>
            </div>
            <div class="nav-item">
                <a href="categorie" class="nav-link <?= $current_page === 'categorie' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i>Catégories
                </a>
            </div>

            <div class="nav-item">
                <a href="utilisateurs" class="nav-link <?= $current_page === 'utilisateurs' ? 'active' : '' ?> ">
                    <i class="fas fa-user"></i>Utilisateurs
                </a>
            </div>

            <div class="nav-item">
                <a href="paramètres" class="nav-link <?= $current_page === 'paramètres' ? 'active' : '' ?>">
                    <i class="fas fa-gear"></i>Paramètres
                </a>
            </div>

            <div class="nav-item">
                <a href="logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </aside>

    <!-- Contenu principal (sans header) -->
    <main class="main">
        <!-- Le contenu de la page (comme utilisateurs.php) sera injecté ici -->

        <script>
            $(document).ready(function() {
                $('.img-lightbox').magnificPopup({type:'image', gallery:{enabled:true}});
            });
        </script>