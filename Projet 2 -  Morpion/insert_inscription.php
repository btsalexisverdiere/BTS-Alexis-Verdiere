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

        $name = strip_tags($_POST["name"]);
        $lastname = strip_tags($_POST["lastname"]);

        $email = strip_tags($_POST['login']);
     

            if(filter_var($email, FILTER_VALIDATE_EMAIL)){

                $userExists = $bdd->prepare("SELECT * FROM `user` WHERE login = ?");
                
                $count = $userExists->rowCount();
                    if($count == 0){
                        $password = strip_tags($_POST['password']);
                        $nbCaractere = strlen($password);
                            if($nbCaractere > 8){
                                    $password = password_hash($pass, PASSWORD_DEFAULT); 
                                    $createUser = $bdd->prepare("INSERT INTO `user`(`name_User`, `lastname_User`, `login_User`, `password_User`,) VALUES (?,?,?,?)");
                                    $createUser->execute(array($name, $lastname, $login, $password ));
                                    var_dump($createUser);
                                    header("Location: connexion");
                            }else{
                                $erreur = "Le mot de passe doit contenir 8 caractères au minimum !";
                            }
                    }else{
                        $erreur = "Email et/ou nom d'utilisateur déja utilisé.";
                        }
            }else{
                $erreur = "Ce n'est pas une adresse mail valide ! ";
            }
    }
}

?>