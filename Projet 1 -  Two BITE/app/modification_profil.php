<?php


session_start();

if (isset($_SESSION['userId'])) {
    $id = $_SESSION['userId'];
} else {
    // L'utilisateur n'est pas connecté, redirigez-le vers la page de connexion par exemple
    header("Location: login.php");
    exit();
}

$db = include_once './include/Manage/db.php';


$result = $db->prepare("SELECT * from user WHERE id_User = ?");
$result->bind_param("i", $id);
$result->execute();
$res = $result->get_result();
$datas = $res->fetch_all(MYSQLI_ASSOC);


foreach ($datas as $data) :

    // profil entier

    if (isset($_POST['update'])) {
        if (!empty($_POST)) {
            if (
                isset($_POST["user"], $_POST["email"])
                && !empty($_POST["user"]) && !empty($_POST["email"])
            ) {
                $pseudo = strip_tags($_POST["user"]);
                $name = strip_tags($_POST['name']);
                $lastname = strip_tags($_POST['lastname']);
                $email = strip_tags($_POST["email"]);


                    $update = $db->prepare("UPDATE `user` SET `username_User` = ?, `name_User` = ?, `lastname_User` = ?,`email_User` = ? WHERE id_User = ?");

                    $update->execute(array($pseudo,$name, $lastname, $email, $id));
                    $erreurProfil = "Votre profil a été modifié(e)";
                    header("refresh:5;url=profil");
            } else {
                $erreurProfil = "formulaire incomplet";
            }
        }
    }

    if (isset($_POST['updatePass'])) {
        if (isset($_POST["lastpass"], $_POST["pass1"], $_POST["pass2"]) && !empty($_POST["lastpass"]) && !empty($_POST["pass1"]) && !empty($_POST["pass2"])) {
            $lastPass = htmlspecialchars($_POST["lastpass"]);

            if (password_verify($lastPass, $data["password_User"])) {
                $pass1 = htmlspecialchars($_POST["pass1"]);
                $pass2 = htmlspecialchars($_POST["pass2"]);
                $nbCaractere = strlen($pass2);
                if ($nbCaractere > 8) {
                    if ($pass1 == $pass2) {
                        $pass2 = password_hash($pass2, PASSWORD_DEFAULT);
                        $updatePass = $db->prepare("UPDATE `user` SET `password_User` = ? WHERE id_User = ? ");
                        $updatePass->execute(array($pass2, $id));
                        $erreurmdp = "Mot de passe modifier";
                    } else {
                        $erreurmdp = "Les 2 mots de passe ne sont pas identique !";
                    }
                } else {
                    $erreurmdp = "Le mot de passe doit contenir au moin 8 caractère";
                }
            } else {
                $erreurmdp = "Mauvais mots de passe !";
            }
        } else {
            $erreurmdp = "Il faut remplir tout le formulaire !";
        }
    }


  include('./include/includes/NavBar.html');

?>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./include/CSS/profil.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
  </symbol>
  <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
  </symbol>
</svg>
    </head>
<div class="dir">
    <div class="containe">

        <h2> Modifie Ton Profil </h2>



        <?php 
    if(isset($erreur))
    {
        ?>
<div class="alert alert-danger">
<?php
               echo '<font color= "red">'.$erreur."</font>";
?>
</div>
<?php
}
?>

        <form method="post">

            <div class="form-floating mb-3 mt-3">
                <input class="form-control" type="text" name="user" placeholder="Nom d'utilisateur" value="<?= $data["username_User"] ?>">
                <label>Nom d'utilisateur</label>
            </div>

            <div class="form-floating mb-3 mt-3">
                <input class="form-control" type="text" name="lastname" placeholder="Nom" value="<?= $data["lastname_User"] ?>">
                <label>Nom </label>
            </div>

            <div class="form-floating mb-3 mt-3">
                <input class="form-control" type="text" name="name" placeholder="Prénom" value="<?= $data["name_User"] ?>">
                <label>Prénom</label>
            </div>

            <div class="form-floating mb-3 mt-3">
                <input class="form-control" type="email" name="email" placeholder="Email" value="<?= $data["email_User"] ?>">
                <label>Email :</label>
            </div>

            <button type="submit" name="update"  class="btn btn-secondary">Modifier le profil</button>

            <?php
        if (isset($erreurProfil)) {
            if ($erreurProfil == "Votre profil a été modifié(e)"){
                ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
  <div>
    <?= '<font color= "green">' . $erreurProfil . "</font>"; ?>
  </div>
</div>
<?php
            }else{
                ?>
<div class="alert alert-danger d-flex align-items-center" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
  <div>
  <?= '<font color= "red">' . $erreurProfil . "</font>"; ?>
  </div>
</div>
<?php
            }
        
        }
        ?>
        <?php endforeach; ?>
        
    </div>
    <div class="mdp">
        
        <label>Modifier votre mot de passe</label>
        <form method="post">

        <div class="form-floating mb-3 mt-3">
            <input class="form-control" type="password" name="lastpass" placeholder="Dernier mot de passe...">
                <label>Mot de passe actuel</label>
            </div>
            
            <div class="form-floating mb-3 mt-3">
                <input class="form-control" type="password" name="pass1" placeholder="nouveau mot de passe...">
                <label>Nouveau mot de passe</label>
            </div>
            
            <div class="form-floating mb-3 mt-3">
            <input class="form-control" type="password" name="pass2" placeholder="Confirmer le nouveau mot de passe...">
                <label>Valider nouveau mot de passe</label>
            </div>

            <button style="margin-bottom: 2%;" type="submit" name="updatePass"  class="btn btn-secondary">Modifier</button>

        </form>
        <?php
        if (isset($erreurmdp)) {
            if ($erreurmdp == "Mot de passe modifier"){
                ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
  <div>
    <?= '<font color= "green">' . $erreurmdp . "</font>"; ?>
  </div>
</div>
<?php
            }else{
                ?>
<div class="alert alert-danger d-flex align-items-center" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
  <div>
  <?= '<font color= "red">' . $erreurmdp . "</font>"; ?>
  </div>
</div>
<?php
            }
        
        }
        ?>

</div>
<button onclick='window.location.href = "src/includes/deleteProfil.php?id=<?= $data["id_User"] ?> "' type="button" class="btn btn-outline-danger">Supprimer ton profil !</button>
</div>
    