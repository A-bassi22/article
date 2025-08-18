<?php
/**
 * Configuration de la base de données et fonctions utiles
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hsbeyyyy_backend_bd');
define('DB_USER', 'hsbeyyyy_backend_user');
define('DB_PASS', '>2!H<Nk`4M');
define('DB_CHARSET', 'utf8mb4');

/**
 * Connexion à la base de données via PDO
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        throw new PDOException("Impossible de se connecter à la base de données.");
    }
}

function gererUploadImage(array $fichier): string {
    // Validation basique
    if (!isset($fichier['tmp_name'], $fichier['type'], $fichier['size'], $fichier['error'])) {
        throw new Exception("Fichier uploadé invalide ou manquant.");
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors de l'upload du fichier. Code erreur : " . $fichier['error']);
    }

    $dossier_upload = __DIR__ . '/uploads/';
    if (!file_exists($dossier_upload)) {
        mkdir($dossier_upload, 0755, true);
    }

    $types_autorises = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($fichier['type'], $types_autorises)) {
        throw new Exception("Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WebP.");
    }

    if ($fichier['size'] > 5 * 1024 * 1024) { // 5MB max
        throw new Exception("Le fichier est trop volumineux. Taille maximale : 5 MB.");
    }

    $info_image = getimagesize($fichier['tmp_name']);
    if ($info_image === false) {
        throw new Exception("Le fichier n'est pas une image valide.");
    }

    $extension = $types_autorises[$fichier['type']];
    $nom_fichier = uniqid('img_' . date('Ymd_His') . '_') . '.' . $extension;
    $chemin_complet = $dossier_upload . $nom_fichier;

    if (!move_uploaded_file($fichier['tmp_name'], $chemin_complet)) {
        throw new Exception("Erreur lors de l'upload de l'image.");
    }

    // Retourner chemin relatif pour affichage / base
    return '/uploads/' . $nom_fichier;
}

/**
 * Insérer un article dans la base
 */
function insererArticle(PDO $pdo, array $donnees): int {
    $sql = "INSERT INTO articles (titre, description, categorie, image_principale,auteur, date_ajout) 
            VALUES (:titre, :description, :categorie, :image_principale,auteur, :date_ajout)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($donnees);
    return (int)$pdo->lastInsertId();
}

/**
 * Insérer les images supplémentaires liées à un article
 */
function insererImagesSupplementaires(PDO $pdo, int $article_id, array $chemins_images): void {
    if (empty($chemins_images)) {
        return;
    }
    $sql = "INSERT INTO images (article_id, fichier) VALUES (:article_id, :fichier)";
    $stmt = $pdo->prepare($sql);
    foreach ($chemins_images as $chemin) {
        $stmt->execute([
            ':article_id' => $article_id,
            ':fichier' => $chemin
        ]);
    }
}
