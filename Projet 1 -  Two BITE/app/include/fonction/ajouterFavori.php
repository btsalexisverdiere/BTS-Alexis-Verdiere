<?php
session_start();
require_once('../Manage/db.php');
require_once('../Manage/FavorisManager.php');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['userId'])) {
    echo 'Vous devez être connecté pour ajouter aux favoris.';
    exit();
}

// Vérifiez si l'ID du menu est passé
if (!isset($_POST['id_menu'])) {
    echo 'ID du menu manquant.';
    exit();
}

// Créez une instance de FavorisManager (assurez-vous de spécifier le chemin correct)
$favorisManager = new FavorisManager($conn); // Spécifiez votre connexion à la base de données ici

// Ajoutez le menu aux favoris
$id_menu = $_POST['id_menu'];
$favorisManager->ajouterFavori($_SESSION['userId'], $id_menu);

echo 'Le menu a été ajouté aux favoris avec succès.';
?>
