<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/reservation_functions.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$reservations = $userId ? getUserReservations($pdo, $userId) : [];

// Séparer les réservations à venir et passées
$aujourdhui = date('Y-m-d');
$aVenir = array_filter($reservations, fn($r) => $r['date_reservation'] >= $aujourdhui);
$passees = array_filter($reservations, fn($r) => $r['date_reservation'] < $aujourdhui);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes réservations - Le Comptoir des Saveurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Hero -->
    <section class="bg-gradient-to-r from-indigo-700 to-purple-700 text-white py-16 px-4">
        <div class="container mx-auto">
            <h1 class="text-4xl font-extrabold mb-2">🍽️ Mes Réservations</h1>
            <p class="text-indigo-200 text-lg">Gérez vos réservations au Comptoir des Saveurs</p>
            <?php if ($userId && count($reservations) > 0): ?>
            <div class="flex gap-6 mt-6">
                <div class="bg-white bg-opacity-10 rounded-xl px-6 py-3 text-center">
                    <p class="text-2xl font-bold"><?= count($aVenir) ?></p>
                    <p class="text-xs text-indigo-200 uppercase tracking-wider">À venir</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl px-6 py-3 text-center">
                    <p class="text-2xl font-bold"><?= count($passees) ?></p>
                    <p class="text-xs text-indigo-200 uppercase tracking-wider">Passées</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl px-6 py-3 text-center">
                    <p class="text-2xl font-bold"><?= count($reservations) ?></p>
                    <p class="text-xs text-indigo-200 uppercase tracking-wider">Total</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="flex-1 container mx-auto px-4 py-10">

        <!-- Non connecté -->
        <?php if (!$userId): ?>
        <div class="text-center bg-white shadow-md rounded-2xl p-10 max-w-md mx-auto">
            <div class="text-5xl mb-4">🔒</div>
            <h2 class="text-xl font-bold mb-2">Connexion requise</h2>
            <p class="text-gray-500 mb-6">Connectez-vous pour consulter et gérer vos réservations.</p>
            <div class="flex justify-center gap-4">
                <a href="../login.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold">Connexion</a>
                <a href="../register.php" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-semibold">Inscription</a>
            </div>
        </div>

        <!-- Aucune réservation -->
        <?php elseif (count($reservations) === 0): ?>
        <div class="text-center bg-white shadow-md rounded-2xl p-10 max-w-md mx-auto">
            <div class="text-5xl mb-4">📅</div>
            <h2 class="text-xl font-bold mb-2">Aucune réservation</h2>
            <p class="text-gray-500 mb-6">Vous n'avez encore effectué aucune réservation.</p>
            <a href="../reservation.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold">
                Réserver une table →
            </a>
        </div>

        <!-- Liste des réservations -->
        <?php else: ?>

        <!-- Bouton nouvelle réservation -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-700">Vos réservations</h2>
            <a href="../reservation.php" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">
                + Nouvelle réservation
            </a>
        </div>

        <!-- À venir -->
        <?php if (count($aVenir) > 0): ?>
        <h3 class="text-lg font-semibold text-gray-500 uppercase tracking-wider mb-4">À venir</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <?php foreach ($aVenir as $r): ?>
            <?php
                $date = new DateTime($r['date_reservation']);
                $jours = ['Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi',
                          'Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi'];
                $mois = ['January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril',
                         'May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août',
                         'September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre'];
                $jourNom = $jours[$date->format('l')];
                $moisNom = $mois[$date->format('F')];
                $dateFormatee = $jourNom . ' ' . $date->format('d') . ' ' . $moisNom . ' ' . $date->format('Y');
            ?>
            <div class="bg-white rounded-2xl shadow-md p-6 card-hover border-l-4 border-indigo-500">
                <div class="flex justify-between items-start mb-4">
                    <span class="badge bg-indigo-100 text-indigo-700">À venir</span>
                    <span class="text-2xl">🗓️</span>
                </div>
                <p class="font-bold text-lg mb-1"><?= $dateFormatee ?></p>
                <div class="flex gap-4 text-sm text-gray-500 mb-3">
                    <span>🕐 <?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></span>
                    <span>👥 <?= (int)$r['nb_personnes'] ?> personne<?= $r['nb_personnes'] > 1 ? 's' : '' ?></span>
                </div>
                <?php if (!empty($r['message'])): ?>
                <p class="text-sm text-gray-400 italic mb-4 border-t pt-3">"<?= htmlspecialchars($r['message']) ?>"</p>
                <?php endif; ?>
                <div class="flex gap-3 mt-2">
                    <a href="detail.php?id=<?= (int)$r['id'] ?>"
                       class="flex-1 text-center bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
                        Détails
                    </a>
                    <a href="cancel_reservation.php?id=<?= (int)$r['id'] ?>"
                       class="flex-1 text-center bg-red-50 text-red-600 px-3 py-2 rounded-lg hover:bg-red-100 transition text-sm font-semibold"
                       onclick="return confirm('Voulez-vous vraiment annuler cette réservation ?');">
                        Annuler
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Passées -->
        <?php if (count($passees) > 0): ?>
        <h3 class="text-lg font-semibold text-gray-400 uppercase tracking-wider mb-4">Historique</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($passees as $r): ?>
            <?php
                $date = new DateTime($r['date_reservation']);
                $dateFormatee = $date->format('d/m/Y');
            ?>
            <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-gray-300 opacity-70">
                <div class="flex justify-between items-start mb-4">
                    <span class="badge bg-gray-100 text-gray-500">Passée</span>
                    <span class="text-2xl">✅</span>
                </div>
                <p class="font-bold text-lg mb-1"><?= $dateFormatee ?></p>
                <div class="flex gap-4 text-sm text-gray-400 mb-3">
                    <span>🕐 <?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></span>
                    <span>👥 <?= (int)$r['nb_personnes'] ?> personne<?= $r['nb_personnes'] > 1 ? 's' : '' ?></span>
                </div>
                <a href="detail.php?id=<?= (int)$r['id'] ?>"
                   class="block text-center bg-gray-100 text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-semibold mt-2">
                    Voir les détails
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </main>

    <footer class="bg-gray-900 text-gray-400 text-center py-6 mt-10">
        <p>&copy; 2025 Le Comptoir des Saveurs — Projet BTS SIO SLAM</p>
    </footer>
</body>
</html>