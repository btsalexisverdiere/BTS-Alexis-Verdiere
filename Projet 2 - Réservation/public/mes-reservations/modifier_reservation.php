<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// ───── Sécurité ADMIN ─────
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ───── Vérif ID ─────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID invalide");
}

// ───── Récupération réservation ─────
$stmt = $pdo->prepare("
    SELECT r.*, u.name, u.lastname, u.email
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Réservation introuvable");
}

// ───── UPDATE ─────
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date = $_POST['date_reservation'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $nb = (int)($_POST['nb_personnes'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    // validation simple
    if ($date === '' || $heure === '' || $nb <= 0) {
        $error = "Veuillez remplir tous les champs correctement.";
    } else {

        $update = $pdo->prepare("
            UPDATE reservations
            SET date_reservation = ?,
                heure = ?,
                nb_personnes = ?,
                message = ?
            WHERE id = ?
        ");

        $update->execute([$date, $heure, $nb, $message, $id]);

        $success = "Réservation mise à jour avec succès.";

        // refresh data
        $stmt->execute([$id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier réservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

 <?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded-xl shadow">

    <h1 class="text-2xl font-bold mb-6">Modifier la réservation #<?= $reservation['id'] ?></h1>

    <!-- INFOS CLIENT -->
    <div class="mb-4 p-4 bg-gray-100 rounded">
        <p><strong>Client :</strong> <?= htmlspecialchars($reservation['name'] . ' ' . $reservation['lastname']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($reservation['email']) ?></p>
    </div>

    <!-- MESSAGE SUCCESS / ERROR -->
    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" class="space-y-4">

        <div>
            <label class="block font-medium">Date</label>
            <input type="date"
                   name="date_reservation"
                   value="<?= htmlspecialchars($reservation['date_reservation']) ?>"
                   class="w-full border p-2 rounded"
                   required>
        </div>

        <div>
            <label class="block font-medium">Heure</label>
            <input type="time"
                   name="heure"
                   value="<?= htmlspecialchars(substr($reservation['heure'],0,5)) ?>"
                   class="w-full border p-2 rounded"
                   required>
        </div>

        <div>
            <label class="block font-medium">Nombre de personnes</label>
            <input type="number"
                   name="nb_personnes"
                   value="<?= (int)$reservation['nb_personnes'] ?>"
                   class="w-full border p-2 rounded"
                   required>
        </div>

        <div>
            <label class="block font-medium">Message</label>
            <textarea name="message"
                      class="w-full border p-2 rounded"
                      rows="4"><?= htmlspecialchars($reservation['message']) ?></textarea>
        </div>

        <div class="flex justify-between items-center pt-4">

            <a href="reservations.php"
               class="text-gray-600 hover:underline">
                ← Retour
            </a>

            <button type="submit"
                    class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">
                Enregistrer
            </button>

        </div>

    </form>

</div>

</body>
</html>