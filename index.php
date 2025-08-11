
<?php if (isset($_GET['erreur'])): ?>
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
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">

            <h2 class="text-center mb-4 fw-bold text-dark">
              <i class="fas fa-lock text-primary me-2"></i>
              Connexion
            </h2>

            <!-- Message d'erreur (affiché dynamiquement) -->
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 d-none" id="errorMessage">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <span id="errorText">Nom d'utilisateur ou mot de passe incorrect</span>
            </div>

            <!-- Formulaire de connexion -->
            <form action="process_login.php" method="POST">
              <div class="mb-3">
                <label for="username" class="form-label">
                  <i class="fas fa-user text-primary me-2"></i>Nom d'utilisateur
                </label>
                <input type="text" class="form-control form-control-lg" id="username" name="username" required>
              </div>

              <div class="mb-4">
                <label for="password" class="form-label">
                  <i class="fas fa-key text-primary me-2"></i>Mot de passe
                </label>
                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-secondary btn-lg">
                  <i class="fas fa-sign-in-alt me-2"></i>Se connecter 
                </button>
              </div>
            </form>

            <div class="text-center mt-4">
              <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
