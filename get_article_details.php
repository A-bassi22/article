<?php
require_once("bd.php");

// Indiquer que la réponse sera au format JSON
header('Content-Type: application/json');

// 1. Vérifier que l'ID est fourni et valide
$article_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;

if (!$article_id) {
    // Si l'ID est invalide, renvoyer une erreur JSON
    echo json_encode(['success' => false, 'message' => 'ID d\'article invalide.']);
    exit;
}

try {
    $pdo = getDbConnection();

    // 2. Récupérer les détails de l'article (y compris le nom de l'auteur)
    $stmt = $pdo->prepare("
        SELECT a.id, a.titre, a.description, a.categorie, a.image_principale, a.date_ajout, u.nom as auteur_nom
        FROM articles a
        LEFT JOIN utilisateurs u ON a.auteur_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        // 3. Si l'article est trouvé, renvoyer les données avec succès
        echo json_encode(['success' => true, 'data' => $article]);
    } else {
        // 4. Si aucun article n'est trouvé, renvoyer une erreur
        echo json_encode(['success' => false, 'message' => 'Article non trouvé.']);
    }

} catch (PDOException $e) {
    error_log("Erreur de récupération des détails : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données.']);
}
?>
