<?php
session_start();

// Déconnexion de l'utilisateur
if (isset($_SESSION["user"])) {
    // Supprimer toutes les variables de session
    $_SESSION = array();

    // Détruire la session
    session_destroy();

    // Redirection vers la page de connexion ou une autre page
    header("Location: connexion.php");
    exit();
} else {
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion.php");
    exit();
}
?>
