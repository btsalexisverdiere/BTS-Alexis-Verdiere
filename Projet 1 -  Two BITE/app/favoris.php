<?php
require_once('./include/Manage/db.php');
require_once('./include/Manage/FavorisManager.php');

session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['userId'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion
    exit();
}

// Obtenez l'ID de l'utilisateur à partir de la session
$id_user = $_SESSION['userId'];

// Créez une instance de FavorisManager
$favorisManager = new FavorisManager($conn);

// Afficher les favoris de l'utilisateur
$favoris = $favorisManager->getFavoris($id_user);


?>

<style>
    .favori-container {
        border: 2px black solid;
        border-radius: 10px;
        padding: 10px;
        margin: 10px;
        max-width: 20vw;
    }

    .favori-title {
        font-size: x-large;
    }

    .favori-image {
        width: 100%;
        border-radius: 8px;
    }

    .favori-button {
        /* Ajoutez ici les styles souhaités pour le bouton */
    }

    #coeur {
    color: red;
}

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./include/CSS/style.css">
    <link rel="stylesheet" href="./include/CSS/navbar.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoris</title>
</head>
<body>
<?php include('./include/includes/NavBar.html'); ?>
    <div class="flex flex-row flex-wrap p-20 gap-8">
    <?php
    if (empty($favoris)) {
        echo '<p>Aucun favori trouvé.</p>';
    } else {
        // Construire une liste d'ID de recettes
        $recipeIds = implode(',', $favoris);

        // Faites un appel à l'API "informationBulk" pour obtenir les détails de toutes les recettes
        // principale : 66d055d8e2394a4c9dac91c1b30b8be0
        //secours : 365477f7c21b4073846c6861571d4c3c
        //secours 2 : 9a727f334569449a8285ceec2a85c2c4
        //secours 3 : 0d41dd2d8cbc4cbb8c2bd145aee65af1
        $api_key = '0d41dd2d8cbc4cbb8c2bd145aee65af1';
        $url = "https://api.spoonacular.com/recipes/informationBulk?apiKey=" . $api_key . "&ids=" . $recipeIds;
        $raw = file_get_contents($url);
        $data = json_decode($raw, true);

        foreach ($data as $recipe) { ?>
           <div class="flex flex-col items-center bg-white rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="<?= $recipe['image'] ?>" alt="<?= $recipe['title'] ?>">
                <div class="flex flex-col justify-between p-4 leading-normal">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= $recipe['title'] ?></h5>
                    <!-- <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.</p> -->

                    <button class="favori-button bg-white z-50" onclick="supprimerFavori(<?= $recipe['id'] ?>)">
                    <span id="coeur">&#10084;</span> Supprimer des favoris
                    </button>
                    <br>
                    <button class="favori-button bg-white" onclick="window.location.href = 'menu.php?id=<?= $recipe['id'] ?>'">
                        Recette
                    </button>
                </div>
        </div>
                    <?php 

    }
}


    ?>
</div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        function supprimerFavori(id_menu) {
            // Utilisez Ajax pour appeler le script PHP qui supprime le menu des favoris
            $.post('./include/fonction/supprimerFavori.php', { id_menu: id_menu }, function(data) {
                // Rechargez la page après la suppression réussie
                if (data.success) {
                    location.reload();
                }
            }, 'json');
        }
    </script>

</body>
</html>
