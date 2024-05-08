<?php
include 'connect.php';

// vérification de l'envoie du formulaire 

if (!empty($_POST)){
    // username = ce que le formulaire renvoie 

    if(isset($_POST["name"],$_POST["lastname"],$_POST["login"], $_POST["password"])
        && !empty($_POST["name"]) && !empty($_POST["lastname"]) && !empty($_POST["login"]) && !empty($_POST["password"]) )
    {
        // formulaire complet 

        // protection des données 

        $prenom = strip_tags($_POST["name"]);
        $nom = strip_tags($_POST["lastname"]);

        $login = strip_tags($_POST['login']);
     

                $userExists = $bdd->prepare("SELECT * FROM `user` WHERE login = ?");
                
                $count = $userExists->rowCount();
                    if($count == 0){
                        $password = strip_tags($_POST['password']);
                        $nbCaractere = strlen($password);
                            if($nbCaractere > 8){
                                    $password = password_hash($password, PASSWORD_DEFAULT); 
                                    $createUser = $bdd->prepare("INSERT INTO `user_Morpion`(`login`, `password`, `nom`, `prenom`) VALUES (?,?,?,?)");
                                    $createUser->execute(array($login, $password, $nom, $prenom ));
                                    header("Location: connexion");
                            }else{
                                $erreur = "Le mot de passe doit contenir 8 caractères au minimum !";
                            }
                    }else{
                        $erreur = "Email et/ou nom d'utilisateur déja utilisé.";
                        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>

    <!-- CSS only -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>
<body>
    <div class="log">
        <h2>Inscription</h2>
    <form method="POST" >

    <div class="row">
    <div class="form-floating mb-3 mt-3 col">
  <input type="text" class="form-control" id="nom" placeholder="Enter votre prénom" name="name">
  <label >Prénom</label>
</div>

<div class="form-floating mt-3 mb-3 col">
  <input type="text" class="form-control" id="lastname" placeholder="Enter votre nom" name="lastname">
  <label>Nom</label>
</div>

    </div>
    
    <div class="form-floating mb-3 mt-3">
  <input type="text" class="form-control" placeholder="Enter nom d'utilisateur" name="login">
  <label for="login">Nom d'utilisateur</label>
</div>

<div class="form-floating mb-3 mt-3">
    <input class="form-control" type="password" id="psw" name="password" 
        title="Doit contenir au moins un chiffre, une lettre majuscule et minuscule, et au moins 8 caractères ou plus" 
        required/>
        <label for="password">Mot de passe</label>
</div>

<div id="message">
            <p id="letter" class="invalid">Lettre <b>minuscule</b></p>
            <p id="capital" class="invalid">Lettre <b>majuscule</b></p>
            <p id="number" class="invalid">Un <b>Chiffre</b></p>
            <p id="length" class="invalid">Minimum <b>8 caractères</b></p>
        </div>

<button type="submit" class="btn btn-outline-dark">S'inscrire</button>

<?php  if(isset($erreur)){ ?>
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <strong> <?= $erreur; ?> </strong>
  </div>
<?php } ?>

    </form>

</div>

</body>

<script src="validation.js"></script>

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

.body{
    margin: 3%;
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
    margin-top: 7%;
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


