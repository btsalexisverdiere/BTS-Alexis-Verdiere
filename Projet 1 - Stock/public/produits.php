<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ./login.php');
    exit();
}
require_once '../config/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $prix = $_POST['prix'];
    $quantite = $_POST['quantite'];
    $seuil = $_POST['seuil'];

    // 🔎 Vérifier si le produit existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE nom = ?");
    $stmt->execute([$nom]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $error = "Ce produit existe déjà.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO produits (nom, prix_unitaire, quantite, seuil_alerte)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $prix, $quantite, $seuil]);

        $success = "Produit ajouté avec succès.";
    }
}

$produits = $pdo->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll();
?><div class="max-w-7xl mx-auto">

<h2 class="text-3xl font-bold mb-6">📦 Gestion des produits</h2>

<?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- FORMULAIRE -->
<form method="POST" class="bg-white p-6 rounded-xl shadow mb-8 grid md:grid-cols-5 gap-4">

    <input name="nom" placeholder="Nom du produit"
        class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>

    <input name="prix" type="number" step="0.01" placeholder="Prix (€)"
        class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>

    <input name="quantite" type="number" step="0.01" placeholder="Quantité"
        class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>

    <input name="seuil" type="number" step="1" placeholder="Seuil alerte"
        class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>

    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 font-semibold transition">
        + Ajouter
    </button>

</form>

<!-- TABLE -->
<div class="bg-white rounded-xl shadow overflow-hidden">

    <div class="p-4 border-b flex justify-between items-center">
        <h3 class="font-semibold text-lg">Liste des produits</h3>
        <span class="text-sm text-gray-500"><?= count($produits) ?> produits</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">

            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="p-3">Produit</th>
                    <th class="p-3">Prix</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Statut</th>
                </tr>
            </thead>

            <tbody class="divide-y">

            <?php foreach ($produits as $p): ?>
                <?php 
                    $low = $p['quantite'] <= $p['seuil_alerte'];
                ?>
                <tr class="hover:bg-gray-50 transition">

                    <!-- NOM -->
                    <td class="p-3 font-medium">
                        <?= htmlspecialchars($p['nom']) ?>
                    </td>

                    <!-- PRIX -->
                    <td class="p-3">
                        <span class="font-semibold text-gray-700">
                            <?= number_format($p['prix_unitaire'], 2) ?> €
                        </span>
                    </td>

                    <!-- STOCK -->
                    <td class="p-3">
                        <div class="flex items-center gap-2">

                            <span class="font-semibold">
                                <?= $p['quantite'] ?>
                            </span>

                            <!-- barre visuelle -->
                            <div class="w-24 h-2 bg-gray-200 rounded">
                                <div class="h-2 rounded 
                                    <?= $low ? 'bg-red-500' : 'bg-green-500' ?>"
                                    style="width: <?= min(100, ($p['quantite'] / max(1,$p['seuil_alerte']*2)) * 100) ?>%">
                                </div>
                            </div>

                        </div>
                    </td>
                        <td class="p-3">
                            <?php if ($p['quantite'] == 0): ?>
                                <span class="bg-red-200 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ❌ Stock épuisé
                                </span>

                            <?php elseif ($p['quantite'] <= $p['seuil_alerte']): ?>
                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    ⚠ Stock faible
                                </span>

                            <?php else: ?>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    ✔ Disponible
                                </span>
                            <?php endif; ?>
                        </td>

                </tr>
            <?php endforeach; ?>

            <?php if (count($produits) === 0): ?>
                <tr>
                    <td colspan="4" class="text-center p-6 text-gray-500">
                        Aucun produit pour le moment
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</div>