<?php
session_start();


require_once "./connect.php";

if(isset($_SESSION["user"])){
    header('Location: index.php');
}

try {
    if(!empty($_POST)){
        if(isset($_POST["login"], $_POST["password"]) && !empty($_POST["login"]) && !empty($_POST["password"])){
            $login = $_POST["login"];
            
            $sql = "SELECT * FROM `user_Morpion` WHERE login = :login";
            $query = $bdd->prepare($sql);
            $query->execute(array(':login' => $login));
    
            $user = $query->fetch();
    
            if(!$user || !password_verify($_POST["password"], $user["password"])){
                $erreur = "Email ou mot de passe incorrect";
            } else {
                $_SESSION["user"] = [
                    "id" => $user["id_user"],
                    "prenom" => $user["prenom"],
                    "nom" => $user["nom"],
                ];
                // redirection vers la page
                header("Location: index.php");
                exit(); // Terminer le script après la redirection
            }
        } else {
            $erreur = "Erreur de traitement";
        }
    }
} catch(Exception $e) {
    die($e->getMessage()); 
}



?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion-accueil</title>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="log">
<h2>Bienvenue</h2>
<form method="post">
  
<div class="row">
<div class="col">
    <div class="form-floating mb-3 mt-3">
    <input class="form-control" type="text" value="<?php  if(isset($_POST['login'])){ echo $_POST['login'];} ?>" required name="login" placeholder="Entrez votre utilisateur">
    <label for="login">login</label>
    </div>
    </div>
    <div class="col">
    <div class="form-floating mb-3 mt-3">
    <input  class="form-control" minlength="8" type="password" placeholder="Mot de passe" name="password" id="pass" required >
    <label for="password">Votre mot de passe</label>
    </div>
    </div>

  </div>
  <?php  if(isset($erreur)){ ?>
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <strong> <?= $erreur; ?> </strong>
  </div>
<?php } ?>
    <button class="btn btn-outline-success" type="submit" name="connexion">Connexion</button>
    <button onclick="window.location.href = 'inscription'" class="btn btn-outline-dark" type="button">S'inscrire</button>


</form>
</div>

</body>
</html>

<style>

*, ::before, ::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

::-webkit-scrollbar {
    width: 0px;
}

h1{
    text-align: center;
    text-transform: uppercase;
    font-size: 100px;
}

h2{
    text-align: center;
    font-size: 70px;
    margin: 5%;
    font-family: 'Arvo', serif;
}

h3{
    font-size: 60px;
}

p{
    margin: 5% !important;
    margin-bottom: 0 !important;
    font-family: 'Red Hat Display', sans-serif;
    font-size: 16px;
}

a{
    text-decoration: none !important;
}

.marg{
    margin-left: 32%;
    display: flex;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.log{
    width: 65%;
    margin: auto;
    padding: auto;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    border-radius: 11px;
    margin-top: 13%;
    padding: 2%;
}

label{
  text-align: left;
}

.button{
    margin-top: 2%;
}

/* validation */

#message {
    display: none;
    background-color:transparent;
    color: #fff;
    position: relative;
  }

  #message p {
    font-size: 14px;
    margin: 0!important;
  }

  .valid {
    color: #6cff98;
  }

  .valid:before {
    position: relative;
    left: 0;
    content: "✔  ";
  }

  .invalid {
    color: red;
  }

  .invalid:before {
    position: relative;
    left: 0;
    content: "✖  ";
  }
</style>
