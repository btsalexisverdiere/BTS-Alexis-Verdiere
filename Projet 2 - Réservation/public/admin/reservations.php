<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/reservation_functions.php';

// ───── Sécurité admin ─────
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ───── données ─────
$reservations = getAllReservations($pdo, 200, 0);
$todayCount = count(getTodayReservations($pdo));

// CSRF simple
if (empty($_SESSION['session_key'])) {
    $_SESSION['session_key'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['session_key'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Admin - Réservations</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/navbar.php'; ?>

<div class="max-w-7xl mx-auto p-6">

    <!-- HEADER -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold">📅 Gestion des réservations</h1>
        <p class="text-gray-500">Tableau administratif des réservations</p>
    </div>

    <!-- STATS -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <span class="font-semibold">Aujourd’hui :</span>
        <span class="text-indigo-600 font-bold"><?= $todayCount ?></span> réservations
    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Client</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Heure</th>
                    <th class="p-3">Pers.</th>
                    <th class="p-3">Message</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">

            <?php foreach ($reservations as $r): ?>
                <tr class="hover:bg-gray-50">

                    <td class="p-3 font-medium"><?= (int)$r['id'] ?></td>

                    <td class="p-3">
                        <div class="font-semibold">
                            <?= htmlspecialchars($r['name'] . ' ' . $r['lastname']) ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?= htmlspecialchars($r['email']) ?>
                        </div>
                    </td>

                    <td class="p-3"><?= htmlspecialchars($r['date_reservation']) ?></td>
                    <td class="p-3"><?= htmlspecialchars(substr($r['heure'],0,5)) ?></td>
                    <td class="p-3"><?= (int)$r['nb_personnes'] ?></td>

                    <td class="p-3 text-gray-600">
                        <?= nl2br(htmlspecialchars(substr($r['message'],0,60))) ?>
                    </td>

                    <td class="p-3 flex gap-2">

                        <a class="view-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded" href="view_reservation.php?id=<?= (int)$r['id'] ?>">
                            Voir
            </a>

                        <a href="modifier_reservation.php?id=<?= (int)$r['id'] ?>"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <a href="admin_cancel.php?id=<?= (int)$r['id'] ?>"
                           onclick="return confirm('Supprimer ?')"
                           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                            Suppr
                        </a>

                    </td>

                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

    </div>

</div>



</body>
</html>