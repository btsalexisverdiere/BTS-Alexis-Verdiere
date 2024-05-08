<?php
session_start();

//Vérifiez si l'ID de l'utilisateur est défini dans la session
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    include './include/Manage/user.php';
    include './include/Manage/FavorisManager.php';
} else {
    // L'utilisateur n'est pas connecté, redirigez-le vers la page de connexion par exemple
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Page d'accueil</title>
    <link rel="stylesheet" href="./include/CSS/index.css">
    <link rel="stylesheet" href="./include/CSS/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<?php include('./include/includes/navbar.html'); ?>

    <div class="home-search">
    <form method="GET">
        <label for="recette">Rechercher un menu</label>
        <br>
        <div class="flex flex-col items-center">
        <input type="text" name="recette" id="recette" placeholder="Rechercher....">
        <button class="px-6 py-2 rounded-lg bg-gray-800 text-white" type="submit">Rechercher</button>
        </div>
    </form>
    </div>

    <?php

if (empty($_GET['recette'])) {
    ?>
    <p id="emptySearch">Effectue une recherche pour trouver une bonne recette <span id="coeur">	&#128523;</span> </p>
    <?php
} elseif (!empty($_GET['recette'])) {
       // principale : 66d055d8e2394a4c9dac91c1b30b8be0
        //secours : 365477f7c21b4073846c6861571d4c3c
        //secours 2 : 9a727f334569449a8285ceec2a85c2c4
        //secours 3 : 0d41dd2d8cbc4cbb8c2bd145aee65af1
        // secours 4 : ad0878602caf4b1a8bb9714d5944f372
        $api_key = 'ad0878602caf4b1a8bb9714d5944f372';
    $recette = $_GET['recette'];
    // L'URL de l'API Spoonacular avec une recherche et un tri aléatoire
    $url = "https://api.spoonacular.com/recipes/complexSearch?apiKey=" . $api_key . "&query=" . $recette . "&number=1";

    // Effectuer la requête à l'API
    $raw = file_get_contents($url);

    // Décoder la réponse JSON
    $data = json_decode($raw, true);

 // Vérifier si la requête a réussi
if ($data && isset($data['results'])) {
    ?>
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
        <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
            <?php
            // Boucle à travers chaque recette
            foreach ($data['results'] as $recetteData) {
                // Récupérer l'ID de la recette
                $recetteId = $recetteData['id'];

                // Effectuer une nouvelle requête à l'API pour obtenir les détails de la recette
                $recetteDetailsUrl = "https://api.spoonacular.com/recipes/{$recetteId}/information?apiKey={$api_key}";

                // Effectuer la requête
                $rawRecetteDetails = file_get_contents($recetteDetailsUrl);

                // Décoder la réponse JSON
                $recetteDetails = json_decode($rawRecetteDetails, true);

                // Vérifier si le temps de préparation est disponible
                $tempsPreparation = isset($recetteDetails['readyInMinutes']) ? $recetteDetails['readyInMinutes'] : "Non disponible";

                
                // Appel de la méthode getFavoris pour obtenir la liste des favoris de l'utilisateur
                $favorisManager = new FavorisManager($conn); // Spécifiez votre connexion à la base de données ici
                $favorisUtilisateur = $favorisManager->getFavoris($userId);

                // Vérifier si le menu actuel est déjà un favori pour cet utilisateur
                $estFavori = in_array($recetteId, $favorisUtilisateur);

                ?>
                <a href="./menu.php?id=<?= $recetteId; ?>" class="group relative">
                    <div class="bg-white rounded-lg overflow-hidden shadow-md ">
                        <img src="<?= $recetteData['image'] ?>"  alt="" class="w-full h-72 object-cover object-center w-full group-hover:opacity-75">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= $recetteData['title'] ?></h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">Temps de préparation <?= $tempsPreparation ?> minutes</p>
                        </div>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>
  <?php
  } else {
        // Gérer le cas où la requête n'a pas réussi
        echo 'Erreur lors de la récupération des données de la recette aléatoire.';
    }
}

?>

<?php
// Exemple de requête de recherche de produits
// $search_query = "pizza";
// $api_url = "https://world.openfoodfacts.org/cgi/search.pl?search_terms=$search_query&search_simple=1&action=process&json=1";

// $response = file_get_contents($api_url);
// $data = json_decode($response, true);

// // Affichage des résultats de la recherche
// if ($data && isset($data['products'])) {
//     $products = $data['products'];
//     foreach ($products as $product) {
//         echo "Nom du produit : " . $product['product_name'] . "<br>";
//         echo "Marque : " . $product['brands'] . "<br>";
//         echo "Catégorie : " . $product['categories'] . "<br>";
//         echo "<hr>";
//     }
// } else {
//     echo "Aucun résultat trouvé.";
// }
?>


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function ajouterFavori(id_menu) {
    var favoriButton = document.querySelector('.favori-button');
    var coeur = document.getElementById('coeur');

    // Utilisez AJAX pour appeler le script PHP qui ajoute ou supprime le menu des favoris
    $.ajax({
        url: './include/fonction/ajouterFavori.php', // Le script PHP pour ajouter ou supprimer un favori
        type: 'POST',
        data: { id_menu: id_menu },
        success: function(response) {
            // Changez l'icône du cœur en fonction de l'état du favori
            var isFavori = favoriButton.getAttribute('data-favori') === 'true';
            favoriButton.setAttribute('data-favori', isFavori ? 'false' : 'true');
            coeur.classList.toggle('filled', !isFavori);
        },
        error: function(error) {
            console.error('Erreur lors de l\'ajout ou de la suppression des favoris :', error);
        }
    });
}
</script>
</body>