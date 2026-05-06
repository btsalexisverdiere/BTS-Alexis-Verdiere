<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion Stock Restaurant</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<nav class="bg-gray-900 text-white p-4 flex justify-between items-center">

    <!-- Logo / Titre -->
    <h1 class="font-bold text-lg">Admin Stock</h1>

    <!-- Liens -->
    <div class="flex items-center space-x-6">
        <a href="https://192.168.1.2:2207/public/admin/dashboard.php">Réservations</a>
        <a href="dashboard.php" class="hover:text-gray-300">Dashboard</a>
        <a href="produits.php" class="hover:text-gray-300">Produits</a>
        <a href="mouvements.php" class="hover:text-gray-300">Gestion du stock</a>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Bouton Connexion -->
            <a href="login.php"
               class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-semibold">
                Connexion
            </a>
        <?php else: ?>
            <!-- Nom utilisateur + Déconnexion -->
            <div class="flex items-center space-x-3">
                <span class="text-sm">
                    👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
                <a href="logout.php"
                   class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">
                    Déconnexion
                </a>
            </div>
        <?php endif; ?>

    </div>
</nav>

<div class="p-6">