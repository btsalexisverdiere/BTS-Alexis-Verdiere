<?php
// Paramètres de connexion à la base de données
// $host = 'localhost'; // Adresse du serveur MySQL
// $dbname = 'morpion'; // Nom de votre base de données
// $user = 'root'; // Nom d'utilisateur MySQL
// $password = ''; // Mot de passe MySQL

$host = 'slamedzmartin.mysql.db'; // Adresse du serveur MySQL
$dbname = 'slamedzmartin'; // Nom de votre base de données
$user = 'slamedzmartin'; // Nom d'utilisateur MySQL
$password = 'LvnE634z'; // Mot de passe MySQL

// Connexion à la base de données
try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

?>
