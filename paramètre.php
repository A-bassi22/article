<?php
session_start();
require_once("bd.php");
include "inc/header.php";

if (!isset($_SESSION['username'])) {
    die("Vous devez être connecté !");
}

try {
    $pdo = getDbConnection();

   
    $stmt = $pdo->query("SELECT * FROM parametres WHERE id = 1");
    $param = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$param) {
        
        $pdo->exec("INSERT INTO parametres (id) VALUES (1)");
        $param = [
            'logo' => null,
            'favicon' => null,
            'lien_site' => ''
        ];
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $lien_site = trim($_POST['lien_site'] ?? '');

        // Upload logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['logo']['tmp_name'];
    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $logoPath = "uploads/logo_" . time() . "." . $ext; 
    move_uploaded_file($tmpName, $logoPath);
} else {
    $logoPath = $param['logo']; 
}

        
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['favicon']['tmp_name'];
            $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            $faviconPath = "uploads/favicon_" . time() . "." . $ext;
            move_uploaded_file($tmpName, $faviconPath);
        } else {
            $faviconPath = $param['favicon'];
        }

   
        $stmt = $pdo->prepare("UPDATE parametres SET logo = :logo, favicon = :favicon, lien_site = :lien_site WHERE id = 1");
        $stmt->execute([
            ':logo' => $logoPath,
            ':favicon' => $faviconPath,
            ':lien_site' => $lien_site
        ]);

        $param['logo'] = $logoPath;
        $param['favicon'] = $faviconPath;
        $param['lien_site'] = $lien_site;

        $success = "Paramètres mis à jour avec succès !";
    }

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<div class="main-content">
    <h2 class="mb-4 text-center">Paramètres du site</h2>

    <?php if(isset($success)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">

        <!-- Logo -->
        <div class="mb-3">
            <label class="form-label">Logo actuel :</label><br>
            <?php if(!empty($param['logo']) && file_exists($param['logo'])): ?>
                <img src="<?= htmlspecialchars($param['logo']) ?>?v=<?= time() ?>" style="max-height:100px;">
            <?php else: ?>
                <p>Aucun logo</p>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Changer le logo :</label>
            <input type="file" name="logo" accept="image/*" class="form-control">
        </div>

        <!-- Favicon -->
        <div class="mb-3">
            <label class="form-label">Icône du site actuelle :</label><br>
            <?php if(!empty($param['favicon']) && file_exists($param['favicon'])): ?>
                <img src="<?= htmlspecialchars($param['favicon']) ?>?v=<?= time() ?>" style="max-height:50px;">
            <?php else: ?>
                <p>Aucune icône</p>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Changer l’icône :</label>
            <input type="file" name="favicon" accept="image/*" class="form-control">
        </div>

        
        <div class="mb-3">
            <label class="form-label">Lien du site :</label>
            <input type="text" name="lien_site" class="form-control" value="<?= htmlspecialchars($param['lien_site']) ?>">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-secondary">Sauvegarder les paramètres</button>
        </div>

    </form>
</div>

<?php include("footer.php"); ?>
