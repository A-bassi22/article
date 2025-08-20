<?php
  session_start();
  if (isset($_SESSION['username'])) {
    echo "<script>
        // Reviens à la page précédente dans l'historique
        window.history.back();
    </script>"; 
    exit();
}
$_SESSION['username'] = $username;
 if (isset($_GET['erreur'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      document.getElementById("errorMessage").classList.remove("d-none");
      document.getElementById("errorText").textContent = "Nom d'utilisateur ou mot de passe incorrect.";
    });
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #e0e0e0, #cfcfcf);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-card {
      background: #2f2f2f;
      border-radius: 1rem;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      padding: 2rem;
      width: 100%;
      max-width: 400px;
      color: #f1f1f1;
    }

    .login-card h2 {
      font-weight: 700;
      color: #ffffff;
    }

    .form-control {
      background: #3a3a3a;
      border: none;
      border-radius: 0.5rem;
      color: #fff;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.6);
    }

    .form-control:focus {
      box-shadow: 0 0 0 2px #bdbdbd;
      outline: none;
      background: #4a4a4a;
      color: #fff;
    }

    .btn-login {
      background: #444;
      border: none;
      border-radius: 0.5rem;
      padding: 0.75rem;
      font-weight: 600;
      color: #fff;
      transition: 0.3s;
    }

    .btn-login:hover {
      background: #666;
    }

    a {
      color: #bdbdbd;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-card text-center">
    <h2 class="mb-4">Connexion</h2>

    <div class="alert alert-danger py-2 d-none" id="errorMessage">
      <i class="fas fa-exclamation-triangle me-2"></i>Identifiants incorrects
    </div>

    <!-- Formulaire -->
    <form action="process_login.php" method="POST">
      <div class="mb-3 text-start">
        <label for="username" class="form-label">
          <i class="fas fa-user me-2"></i>Nom d'utilisateur
        </label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Entrez votre nom" required>
      </div>

      <div class="mb-4 text-start">
        <label for="password" class="form-label">
          <i class="fas fa-key me-2"></i>Mot de passe
        </label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required>
      </div>

      <button type="submit" class="btn btn-login w-100">
        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
      </button>
    </form>

    <div class="mt-4">
      <a href="#">Mot de passe oublié ?</a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
