<?php

// $host = "localhost"; 
// $dbname = "stock"; 
// $username = "root";
// $password = "cUZ28k4T=hs3_#";

$host = "alexisjbts.mysql.db"; 
$dbname = "alexisjbts"; 
$username = "alexisjbts";
$password = "EO9VY9Jd7uui8RxfOV";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
