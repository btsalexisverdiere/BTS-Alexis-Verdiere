<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php
require_once '../config/db.php';
include '../includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-gray-900 to-gray-700 text-white p-10 rounded-lg shadow mb-10">
    <h1 class="text-4xl font-bold mb-4">
        📦 Gestion de Stock du Restaurant
    </h1>
    <p class="text-lg text-gray-200">
        Suivez vos produits, gérez vos entrées/sorties et optimisez votre stock en temps réel.
    </p>

    <div class="mt-6 flex gap-4">
        <a href="produits.php" class="bg-blue-600 hover:bg-blue-700 px-5 py-3 rounded-lg font-semibold">
            Voir les produits
        </a>
        <a href="mouvements.php" class="bg-green-600 hover:bg-green-700 px-5 py-3 rounded-lg font-semibold">
            Gérer le stock
        </a>
    </div>
</section>

<!-- FEATURES -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-bold mb-2">📊 Suivi en temps réel</h2>
        <p class="text-gray-600">
            Visualisez vos stocks et leur évolution instantanément.
        </p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-bold mb-2">⚠️ Alertes intelligentes</h2>
        <p class="text-gray-600">
            Recevez des alertes lorsque les stocks sont faibles.
        </p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-bold mb-2">📦 Gestion simplifiée</h2>
        <p class="text-gray-600">
            Ajoutez, modifiez et suivez vos produits facilement.
        </p>
    </div>

</section>

<!-- STATS -->
<?php
$totalProduits = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$alertes = $pdo->query("SELECT COUNT(*) FROM produits WHERE quantite <= seuil_alerte")->fetchColumn();
?>

<section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-500">Produits en stock</h3>
        <p class="text-3xl font-bold"><?= $totalProduits ?></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-500">Produits en alerte</h3>
        <p class="text-3xl font-bold text-red-600"><?= $alertes ?></p>
    </div>

</section>

<!-- CTA -->
<section class="bg-gray-900 text-white p-8 rounded-lg text-center shadow">
    <h2 class="text-2xl font-bold mb-3">Optimisez votre gestion dès maintenant</h2>
    <p class="mb-5 text-gray-300">
        Accédez au dashboard pour gérer votre stock efficacement.
    </p>

    <a href="dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-lg font-semibold">
        Accéder au dashboard
    </a>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>