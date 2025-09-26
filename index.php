<?php
session_start();
require("bd.php");

if (isset($_SESSION['username'])) {
    header("Location: accueil");
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    ;
     $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
     $recaptcha_valid = false;

     if ($recaptcha_response) {
         $ch = curl_init();

         curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
             'secret'   => $recaptcha_secret,
             'response' => $recaptcha_response,
             'remoteip' => $_SERVER['REMOTE_ADDR']
         ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $verify = curl_exec($ch);
         curl_close($ch);

         if ($verify !== false) {
             $captcha_success = json_decode($verify, true);
             if (isset($captcha_success['success']) && $captcha_success['success'] === true) {
                 $recaptcha_valid = true;
             }
         }
     }

     if (!$recaptcha_valid) {
         $erreur = "Veuillez valider le reCAPTCHA.";
     } else {
         try {
             $pdo = getDbConnection();
             $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
             $stmt->execute([$username]);
             $user = $stmt->fetch(PDO::FETCH_ASSOC);

             if ($user && password_verify($password, $user['password'])) {
                 $_SESSION['username'] = $user['username'];
                 header("Location: accueil");
                 exit();
             } else {
                 $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
             }
         } catch (PDOException $e) {
             error_log("DB error: " . $e->getMessage());
             $erreur = "Erreur serveur, réessayez plus tard.";
         }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="alternate" type="application/rss+xml" title="Flux RSS - Mon Site" href="rss.php">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap');

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #f5f5f5, #eaeaea);
      font-family: 'Jost', sans-serif;
      margin: 0;
    }

    .login-card {
      background: #ffffff;
      border-radius: 1rem;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      color: #000000;
      text-align: center;
    }

    .login-card h2 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #111;
    }

    .form-control {
      background: #ffffff;
      border: 1px solid #ddd;
      border-radius: 0.5rem;
      padding: 0.75rem;
      color: #000;
      transition: all 0.3s ease;
      width: 100%;
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    .form-control::placeholder {
      color: #888;
    }

    .form-control:focus {
      border-color: #02c2fe;
      box-shadow: 0 0 0 3px rgba(2, 194, 254, 0.25);
      outline: none;
    }

    .btn-login {
      background: #02c2fe;
      border: none;
      border-radius: 0.5rem;
      padding: 0.8rem;
      font-weight: 600;
      color: #fff;
      width: 100%;
      cursor: pointer;
      font-size: 1rem;
    }

    .btn-login:hover {
      background: #02a8d6;
    }

    a {
      color: #02c2fe;
      text-decoration: none;
      font-weight: 500;
      display: inline-block;
      margin-top: 1rem;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-card text-center">
    <h2 class="mb-4">Connexion</h2>

    <?php if (!empty($erreur)): ?>
      <div class="alert alert-danger py-2">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($erreur) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST">
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
        <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe">
      </div>
      <div class="g-recaptcha" data-sitekey=""></div>
      <br>
      <button type="submit" class="btn btn-login w-100">
        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
      </button>
    </form>
    <div class="mt-4">
      <a href="#">Mot de passe oublié ?</a>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>