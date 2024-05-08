<?php
session_start();

// Vérifiez si l'ID de l'utilisateur est défini dans la session
// if (isset($_SESSION['userId'])) {
//     // L'utilisateur est connecté, redirigé vers la page de base
//     header("Location: index.php");
//     exit();
// }else{
//     exit();
// }

// Inclure la connexion à la base de données
$db = include_once './include/Manage/db.php';

// Inclure la classe User
include './include/Manage/user.php';

// Créer une instance de la classe User en lui passant la connexion à la base de données
$user = new User($db);
$error = "";
// Vérifier si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if(empty($username) || empty($password)){
        $error = "Un champs est vide";
        $classError = "activeErrror";
    }
        // Appeler la méthode login de la classe User
        if ($user->login($username, $password)) {
            header('Location: index.php');
        }else {
            $error = 'Identifiants incorrects';
        }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="./include/CSS/logs.css">
    <link rel="stylesheet" href="./include/CSS/index.css">
</head>
<body>
    <div class="home-form">
    <h1>Me connecter</h1>
    <form action="" method="post">
        <label for="username">Nom d'utilisateur ou Adresse mail</label>
        <input type="text" name="username" id="username" placeholder="Nom d'utilisateur" required><br>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" placeholder="Mot de passe" required><br>
        <?php
        if ($error){ ?>
        <p class="error"><?= $error ?></p>
        <?php } ?>
        <button type="submit">Se connecter</button>
        <p>Pas encore inscrit ? <a href="register.php">S'inscrire</a></p>
    </form>
    </div>
</body>
</html>
