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

    $produit_id = (int) $_POST['produit_id'];
    $type = $_POST['type'];
    $quantite = (float) $_POST['quantite'];

    // 🔎 récupérer le stock actuel
    $stmt = $pdo->prepare("SELECT quantite, nom FROM produits WHERE id = ?");
    $stmt->execute([$produit_id]);
    $produit = $stmt->fetch();

    if (!$produit) {
        $error = "Produit introuvable.";
    } elseif ($quantite <= 0) {
        $error = "Quantité invalide.";
    } elseif ($type === 'sortie' && $produit['quantite'] < $quantite) {
        $error = "Stock insuffisant pour " . htmlspecialchars($produit['nom']);
    } else {

        // 💾 insertion historique
        $pdo->prepare("
            INSERT INTO mouvements (produit_id, type, quantite)
            VALUES (?, ?, ?)
        ")->execute([$produit_id, $type, $quantite]);

        // 🔄 mise à jour stock
        if ($type === 'entree') {
            $pdo->prepare("UPDATE produits SET quantite = quantite + ? WHERE id = ?")
                ->execute([$quantite, $produit_id]);
        } else {
            $pdo->prepare("UPDATE produits SET quantite = quantite - ? WHERE id = ?")
                ->execute([$quantite, $produit_id]);
        }

        $success = "Stock mis à jour avec succès.";
    }
}

// données
$produits = $pdo->query("SELECT id, nom FROM produits ORDER BY nom")->fetchAll();

$historique = $pdo->query("
    SELECT m.type, m.quantite, m.created_at, p.nom
    FROM mouvements m
    JOIN produits p ON m.produit_id = p.id
    ORDER BY m.created_at DESC
")->fetchAll();
?>

<div class="max-w-6xl mx-auto">

<h2 class="text-3xl font-bold mb-6">📦 Gestion du stock</h2>

<!-- MESSAGES -->
<?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        <?= $success ?>
    </div>
<?php endif; ?>

<!-- FORM -->
<form method="POST" class="bg-white p-6 rounded-xl shadow mb-8 grid md:grid-cols-4 gap-4">

    <select name="produit_id" class="border rounded-lg p-2">
        <?php foreach ($produits as $p): ?>
            <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="type" class="border rounded-lg p-2">
        <option value="entree">➕ Entrée (ajout stock)</option>
        <option value="sortie">➖ Sortie (consommation)</option>
    </select>

    <input name="quantite" type="number" step="0.01" placeholder="Quantité"
        class="border rounded-lg p-2" required>

    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 font-semibold">
        Valider
    </button>

</form>

<!-- HISTORIQUE -->
<div class="bg-white rounded-xl shadow overflow-hidden">

    <div class="p-4 border-b flex justify-between">
        <h3 class="font-semibold text-lg">Historique du stock</h3>
        <span class="text-sm text-gray-500"><?= count($historique) ?> opérations</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">

            <thead class="bg-gray-100 text-sm uppercase text-gray-600">
                <tr>
                    <th class="p-3">Produit</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Quantité</th>
                    <th class="p-3">Date</th>
                </tr>
            </thead>

            <tbody class="divide-y">
            <?php foreach ($historique as $h): ?>
                <tr class="hover:bg-gray-50">

                    <td class="p-3 font-medium">
                        <?= htmlspecialchars($h['nom']) ?>
                    </td>

                    <td class="p-3">
                        <?php if ($h['type'] === 'entree'): ?>
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                ➕ Entrée
                            </span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                                ➖ Sortie
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="p-3 font-semibold">
                        <?= $h['quantite'] ?>
                    </td>

                    <td class="p-3 text-sm text-gray-500">
                        <?= $h['created_at'] ?>
                    </td>

                </tr>
            <?php endforeach; ?>

            <?php if (count($historique) === 0): ?>
                <tr>
                    <td colspan="4" class="text-center p-6 text-gray-500">
                        Aucun historique pour le moment
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</div>

<?php include '../includes/footer.php'; ?>