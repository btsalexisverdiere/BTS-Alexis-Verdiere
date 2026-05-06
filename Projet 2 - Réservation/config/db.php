<?php

$host = "localhost"; // IP de ton NAS
$dbname = "gestion_table"; // nom de ta base MySQL
$username = "root"; // ou ton utilisateur MySQL
$password = "cUZ28k4T=hs3_#";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
