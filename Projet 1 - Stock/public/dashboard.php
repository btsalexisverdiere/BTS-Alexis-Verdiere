<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ./login.php');
    exit();
}

require_once '../config/db.php';
include '../includes/header.php';

// --- Stats ---
$totalProduits = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$valeurStock = $pdo->query("SELECT SUM(prix_unitaire * quantite) FROM produits")->fetchColumn();
$alertes = $pdo->query("SELECT COUNT(*) FROM produits WHERE quantite <= seuil_alerte")->fetchColumn();

// --- Produits en alerte ---
$lowStock = $pdo->query("
    SELECT nom, quantite, seuil_alerte 
    FROM produits 
    WHERE quantite <= seuil_alerte 
    ORDER BY quantite ASC 
    LIMIT 5
")->fetchAll();

// --- Derniers mouvements ---
$lastMovements = $pdo->query("
    SELECT m.type, m.quantite, m.created_at, p.nom
    FROM mouvements m
    JOIN produits p ON m.produit_id = p.id
    ORDER BY m.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<h2 class="text-3xl font-bold mb-6">📊 Dashboard</h2>

<!-- STATS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <h3 class="text-gray-500">Produits</h3>
        <p class="text-4xl font-bold"><?= $totalProduits ?></p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <h3 class="text-gray-500">Valeur du stock</h3>
        <p class="text-4xl font-bold text-green-600">
            <?= number_format($valeurStock, 2) ?> €
        </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <h3 class="text-gray-500">Alertes</h3>
        <p class="text-4xl font-bold text-red-600"><?= $alertes ?></p>
    </div>

</div>

<!-- CONTENU -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- ALERTES -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-lg font-bold mb-4 text-red-600">⚠️ Stock faible</h3>

        <?php if (count($lowStock) === 0): ?>
            <p class="text-gray-500">Aucun problème de stock 👍</p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach ($lowStock as $p): ?>
                    <li class="flex justify-between border-b pb-2">
                        <span><?= htmlspecialchars($p['nom']) ?></span>
                        <span class="text-red-600 font-semibold">
                            <?= $p['quantite'] ?> / <?= $p['seuil_alerte'] ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>

    <!-- DERNIERS MOUVEMENTS -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-lg font-bold mb-4">📦 Dernière modifications</h3>

        <?php if (count($lastMovements) === 0): ?>
            <p class="text-gray-500">Aucune modification récente</p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach ($lastMovements as $m): ?>
                    <li class="flex justify-between border-b pb-2">
                        <div>
                            <span class="font-medium"><?= htmlspecialchars($m['nom']) ?></span>
                            <span class="text-sm text-gray-500 block">
                                <?= $m['created_at'] ?>
                            </span>
                        </div>

                        <span class="
                            px-2 py-1 rounded text-white text-sm
                            <?= $m['type'] === 'entree' ? 'bg-green-500' : 'bg-red-500' ?>
                        ">
                            <?= $m['type'] === 'entree' ? '+' : '-' ?>
                            <?= $m['quantite'] ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>

</div>

<!-- ACTIONS RAPIDES -->
<div class="mt-8 bg-gray-900 text-white p-6 rounded-xl shadow flex flex-col md:flex-row justify-between items-center gap-4">

    <div>
        <h3 class="text-xl font-bold">Actions rapides</h3>
        <p class="text-gray-300 text-sm">Gérer rapidement votre stock</p>
    </div>

    <div class="flex gap-3">
        <a href="produits.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">
            Produits
        </a>
        <a href="mouvements.php" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">
            Gestion du stock
        </a>
    </div>

</div>

<?php include '../includes/footer.php'; ?>