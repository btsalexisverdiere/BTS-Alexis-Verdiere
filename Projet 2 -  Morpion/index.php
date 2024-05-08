<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 
    <title>Morpion</title>
    <script src="script.js"></script>
</head>
<body>
    
   <!--Partie PHP insertion du nom et prenom-->
       <?php session_start(); ?>
       <p> Bonjour, <?= isset($_SESSION['user']['prenom']) ? $_SESSION['user']['prenom'] : 'Utilisateur' ?> <?= isset($_SESSION['user']['nom']) ? $_SESSION['user']['nom'] : '' ?> </p>

    
    <!--Partie connexion a la page index-->

    <div class="commencerpartie">

    <button class="button-64" role="button" onclick="window.location.href = 'morpion'"><span class="text">Commencer la partie</span></button>
</p>
    </div>

</body>

<style>
    p{
        text-align: center;
        margin-top: 5%;
        font-size: xx-large;
    }

    .commencerpartie{
       justify-content: center;
       display: flex;
       align-items: center;
       width: 100%;
       margin-top: 25%;
      
    }



       
.button-64 {
  align-items: center;
  background-image: linear-gradient(144deg,#AF40FF, #5B42F3 50%,#00DDEB);
  border: 0;
  border-radius: 100px;
  box-shadow: rgba(151, 65, 252, 0.2) 0 15px 30px -5px;
  box-sizing: border-box;
  color: #FFFFFF;
  display: flex;
  font-family: Phantomsans, sans-serif;
  font-size: 20px;
  justify-content: center;
  line-height: 1em;
  max-width: 100%;
  min-width: 140px;
  padding: 3px;
  text-decoration: none;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  white-space: nowrap;
  cursor: pointer;

}

.button-64:active,
.button-64:hover {
  outline: 0;
}

.button-64 span {
  background-color: rgb(5, 6, 45);
  padding: 16px 24px;
  border-radius: 100px;
  width: 100%;
  height: 100%;
  transition: 300ms;
}

.button-64:hover span {
  background: none;
}

@media (min-width: 768px) {
  .button-64 {
    font-size: 24px;
    min-width: 196px;
  }
}

</style>
</html>

