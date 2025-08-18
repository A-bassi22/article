<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord WordPress</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f0f0f1;
            color: #1d2327;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background-color: #1d2327;
            color: #f0f0f1;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
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
            background-color: #2271b1;
        }

        /* Main content */
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9f9;
            font-weight: 500;
        }

        /* Checkbox */
        input[type="checkbox"] {
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #646970;
            font-size: 13px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 8px 16px;
            margin-right: 5px;
            background-color: #f6f7f7;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
        }

        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }

        /* Button */
        .button {
            background-color: #2271b1;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #135e96;
        }

        /* Hidden content */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>Articles</h2>
        <ul>
            <li class="active" data-section="tous-articles">Tous les articles</li>
            <li data-section="ajouter-article">Ajouter un article</li>
        </ul>

        <h2>Commentaires</h2>
        <ul>
            <li data-section="commentaires">Voir les commentaires</li>
        </ul>

        <h2>Modèles</h2>
        <ul>
            <li data-section="profil">Profil</li>
            <li data-section="outils">Outils</li>
            <li data-section="menu">Régler le menu</li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Tableau de bord</h1>
            <div>
                <span id="screen-options">Options de l'écran ▼</span>
                <span id="help" style="margin-left: 15px;">Aide ▼</span>
            </div>
        </div>

        <!-- Articles Section -->
        <div id="tous-articles" class="content-section active">
            <div class="tabs">
                <div class="tab active" data-tab="tous">Tous (0)</div>
                <div class="tab" data-tab="corbeille">Corbeille (1)</div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <select id="category-filter">
                        <option>Non classé</option>
                        <option>Catégorie 1</option>
                        <option>Catégorie 2</option>
                    </select>
                </div>
                <div>
                    <input type="text" id="search-filter" placeholder="Filtrer">
                    <button class="button" id="filter-button">Filtrer</button>
                </div>
            </div>

            <table id="articles-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Titre</th>
                        <th>Auteur/autrice</th>
                        <th>Catégories</th>
                        <th>Étiquettes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox" class="article-checkbox"></td>
                        <td>Aucune publication trouvée.</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Ajouter un article Section -->
        <div id="ajouter-article" class="content-section">
            <h2>Ajouter un nouvel article</h2>
            <form id="add-article-form">
                <div style="margin-bottom: 15px;">
                    <label for="article-title" style="display: block; margin-bottom: 5px;">Titre</label>
                    <input type="text" id="article-title" style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="article-content" style="display: block; margin-bottom: 5px;">Contenu</label>
                    <textarea id="article-content" style="width: 100%; height: 200px; padding: 8px;"></textarea>
                </div>
                <button type="submit" class="button">Publier</button>
            </form>
        </div>

        <!-- Other sections (empty for now) -->
        <div id="commentaires" class="content-section">
            <h2>Commentaires</h2>
            <p>Aucun commentaire pour le moment.</p>
        </div>

        <div id="profil" class="content-section">
            <h2>Profil</h2>
            <p>Paramètres du profil utilisateur.</p>
        </div>

        <div id="outils" class="content-section">
            <h2>Outils</h2>
            <p>Outils disponibles.</p>
        </div>

        <div id="menu" class="content-section">
            <h2>Menu</h2>
            <p>Configuration du menu.</p>
        </div>

        <div class="footer">
            <p>Bonjour, bassi</p>
            <p>Merci de faire de WordPress votre outil de création de contenu.</p>
            <p>Version 6.8.2</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation dans la sidebar
            const menuItems = document.querySelectorAll('.sidebar li');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all items
                    menuItems.forEach(i => i.classList.remove('active'));
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Hide all content sections
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show the selected section
                    const sectionId = this.getAttribute('data-section');
                    document.getElementById(sectionId).classList.add('active');
                });
            });

            // Gestion des onglets
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Ici vous pourriez ajouter la logique pour filtrer les articles
                    // en fonction de l'onglet sélectionné (Tous/Corbeille)
                });
            });

            // Case à cocher "Tout sélectionner"
            const selectAllCheckbox = document.getElementById('select-all');
            const articleCheckboxes = document.querySelectorAll('.article-checkbox');
            
            selectAllCheckbox.addEventListener('change', function() {
                articleCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Gestion du formulaire d'ajout d'article
            const addArticleForm = document.getElementById('add-article-form');
            if (addArticleForm) {
                addArticleForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const title = document.getElementById('article-title').value;
                    const content = document.getElementById('article-content').value;
                    
                    // Ici vous pourriez ajouter la logique pour envoyer les données au serveur
                    alert(`Article "${title}" ajouté avec succès!`);
                    
                    // Réinitialiser le formulaire
                    this.reset();
                });
            }

            // Options de l'écran et aide (simulation)
            document.getElementById('screen-options').addEventListener('click', function() {
                alert('Options de l\'écran cliquées');
            });

            document.getElementById('help').addEventListener('click', function() {
                alert('Aide cliquée');
            });

            // Filtre des articles
            document.getElementById('filter-button').addEventListener('click', function() {
                const category = document.getElementById('category-filter').value;
                const searchTerm = document.getElementById('search-filter').value.toLowerCase();
                
                // Ici vous pourriez ajouter la logique pour filtrer les articles
                alert(`Filtrage par catégorie: ${category}, recherche: ${searchTerm}`);
            });
        });
    </script>
</body>
</html>