<?php
// Inclure les fichiers nécessaires
require_once('../Manage/db.php');  // Connexion à la base de données
require_once('../Manage/FavorisManager.php');  // Classe FavorisManager

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Obtenez l'ID de l'utilisateur à partir de la session
$id_user = $_SESSION['userId'];

// Vérifier si l'ID du menu est fourni
if (!isset($_POST['id_menu'])) {
    echo json_encode(['success' => false, 'message' => 'ID du menu non fourni']);
    exit();
}

// Obtenez l'ID du menu à supprimer
$id_menu = $_POST['id_menu'];

// Créez une instance de FavorisManager
$favorisManager = new FavorisManager($conn);

// Supprimer le menu des favoris
$favorisManager->supprimerFavori($id_user, $id_menu);

echo json_encode(['success' => true, 'message' => 'Menu supprimé des favoris avec succès']);
?>
