<?php
session_start();

// Inclure la connexion à la base de données
$db = include_once './include/Manage/db.php';

include './include/Manage/user.php';

$user = new User($db);

//post : lorsqu'il doit envoyer des données au serveur
// GET : demande au serveur un élément d'information

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strip_tags($_POST['username']);
    $name = strip_tags($_POST['name']);
    $lastname = strip_tags($_POST['lastname']);
    $email = strip_tags($_POST['email']);
    $email2 = strip_tags($_POST['email']);
    $password = strip_tags($_POST['password']);
    $password2 = strip_tags($_POST['password2']);

    $user->register($username,$name, $lastname, $email, $email2, $password, $password2);
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="./include/CSS/logs.css">
    <link rel="stylesheet" href="./include/CSS/index.css">
</head>
<body>
<div class="home-form">
    <h1>Inscription</h1>
    <form action="" method="post">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" name="username" placeholder="Choisir un Nom d'utilisateur" required>
        <label for="name">Prénom</label>
        <input type="text" name="name" placeholder="Votre Prénom" required>
        <label for="lastname">Nom</label>
        <input type="text" name="lastname" placeholder="Votre Nom" required>
        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Votre Email" required>
        <input type="email" name="email2" placeholder="Confirmer votre Email" required>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" placeholder="Votre mot de passe" required>
        <input type="password" name="password2" placeholder="Confirmer le mot de passe" required>
        <button type="submit">S'inscrire</button>
        <p>Vous avez déjà un compte ?<a href="login.php">Se connecter</a></p>
    </form>
</div>
</body>
</html>
