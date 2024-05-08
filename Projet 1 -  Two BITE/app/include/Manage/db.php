<?php

$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur de la base de données
$password = ""; // Mot de passe de la base de données
$dbname = "menu"; // Nom de la base de données


// $servername = "slamedzmartin.mysql.db"; // Adresse du serveur MySQL
// $username = "slamedzmartin"; // Nom d'utilisateur de la base de données
// $password = "LvnE634z"; // Mot de passe de la base de données
// $dbname = "slamedzmartin"; // Nom de la base de données
// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

return $conn;

?>
