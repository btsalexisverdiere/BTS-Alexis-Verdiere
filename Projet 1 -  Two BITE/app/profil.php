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


$result = $db->prepare("SELECT * FROM user WHERE id_User = ?");
$result->bind_param("i", $id); // Liage des paramètres
$result->execute(); // Exécution de la requête
$datas = $result->get_result()->fetch_all(MYSQLI_ASSOC); // Récupération des résultats



?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./include/CSS/style.css">
            <link rel="stylesheet" href="./include/CSS/navbar.css">
            <link rel="stylesheet" href="./include/CSS/menu.css">
            <script src="https://cdn.tailwindcss.com"></script>
</head>

<?php include('./include/includes/NavBar.html'); ?>

<body class="bg-gray-100">
    <?php foreach ($datas as $data) : ?>
        <div class="min-h-screen flex flex-col justify-center items-center">
        <div class="bg-white p-8 rounded-md shadow-lg">
            <!-- Nom d'utilisateur -->
            <h1 class="text-2xl font-semibold text-gray-800 mb-2"><?= $data['username_User']; ?></h1>
            <!-- Informations supplémentaires -->
            <div class="justify-center items-center gap-4 mb-6">
                <!-- Date de création du profil -->
                <p><strong><?= $data["name_User"] ?> <?= $data["lastname_User"] ?></strong></p>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a8 8 0 0 0-8 8c0 4.418 3.582 8 8 8s8-3.582 8-8a8 8 0 0 0-8-8zm0 14a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm0-10a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                    <span class="text-gray-600">Profil créé le <?=date("d/m/Y", strtotime($data['created_at'])); ?></span>
                </div>
            </div>
            <!-- Boutons d'action -->
            <div class="flex justify-center items-center gap-4">
                <!-- Bouton Modifier le profil -->
                <button onclick="window.location.href = 'modification_profil'" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">Modifier le profil</button>
            </div>
        </div>
    <?php endforeach; ?>


</body>

</html>
