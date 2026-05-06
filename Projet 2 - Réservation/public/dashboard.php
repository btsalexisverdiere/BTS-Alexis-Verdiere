<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /public/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Récupérer les infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les réservations
$stmt = $pdo->prepare("
    SELECT r.*, t.numero as table_numero, t.localisation, t.capacite
    FROM reservations r
    LEFT JOIN tables t ON r.id_table = t.id
    WHERE r.user_id = ?
    ORDER BY r.date_reservation DESC
");
$stmt->execute([$userId]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aujourdhui = date('Y-m-d');
$aVenir  = array_filter($reservations, fn($r) => $r['date_reservation'] >= $aujourdhui);
$passees = array_filter($reservations, fn($r) => $r['date_reservation'] < $aujourdhui);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon espace — Le Gourmet Connecté</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Hero -->
<section class="bg-gradient-to-r from-indigo-700 to-purple-700 text-white py-12 px-4">
    <div class="container mx-auto max-w-5xl">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <p class="text-indigo-200 text-sm uppercase tracking-widest mb-1">Mon espace</p>
                <h1 class="text-3xl font-extrabold">Bonjour, <?= htmlspecialchars($user['name']) ?> 👋</h1>
                <p class="text-indigo-200 mt-1 text-sm"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div class="flex gap-4">
                <a href="/public/reservation.php" class="bg-white text-indigo-700 font-semibold px-5 py-2 rounded-lg hover:bg-indigo-50 transition text-sm">
                    + Nouvelle réservation
                </a>
                <a href="edit-account.php" class="bg-indigo-600 border border-white text-white font-semibold px-5 py-2 rounded-lg hover:bg-indigo-500 transition text-sm">
                    Mon compte
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold"><?= count($reservations) ?></p>
                <p class="text-xs text-indigo-200 uppercase tracking-wider mt-1">Total</p>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold"><?= count($aVenir) ?></p>
                <p class="text-xs text-indigo-200 uppercase tracking-wider mt-1">À venir</p>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold"><?= count($passees) ?></p>
                <p class="text-xs text-indigo-200 uppercase tracking-wider mt-1">Passées</p>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold"><?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                <p class="text-xs text-indigo-200 uppercase tracking-wider mt-1">Rôle</p>
            </div>
        </div>
    </div>
</section>

<main class="container mx-auto max-w-5xl px-4 py-10">

    <!-- Réservations à venir -->
    <?php if (count($aVenir) > 0): ?>
    <h2 class="text-lg font-semibold text-gray-500 uppercase tracking-wider mb-4">À venir</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <?php foreach ($aVenir as $r): ?>
        <?php
            $date = new DateTime($r['date_reservation']);
            $mois = ['January'=>'jan','February'=>'fév','March'=>'mars','April'=>'avr',
                     'May'=>'mai','June'=>'juin','July'=>'juil','August'=>'août',
                     'September'=>'sept','October'=>'oct','November'=>'nov','December'=>'déc'];
            $moisNom = $mois[$date->format('F')];
            $statutColors = [
                'en_attente' => 'bg-yellow-100 text-yellow-700',
                'confirmee'  => 'bg-green-100 text-green-700',
                'annulee'    => 'bg-red-100 text-red-700',
            ];
            $statutColor = $statutColors[$r['statut']] ?? 'bg-gray-100 text-gray-700';
        ?>
        <div class="bg-white rounded-2xl shadow-md p-6 card-hover border-l-4 border-indigo-500">
            <div class="flex justify-between items-start mb-4">
                <div class="text-center bg-indigo-50 rounded-xl px-4 py-2">
                    <p class="text-2xl font-extrabold text-indigo-700"><?= $date->format('d') ?></p>
                    <p class="text-xs text-indigo-400 uppercase"><?= $moisNom ?> <?= $date->format('Y') ?></p>
                </div>
                <span class="badge <?= $statutColor ?>"><?= ucfirst(str_replace('_', ' ', $r['statut'])) ?></span>
            </div>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <span>🕐</span>
                    <span><?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span>👥</span>
                    <span><?= (int)$r['nb_personnes'] ?> personne<?= $r['nb_personnes'] > 1 ? 's' : '' ?></span>
                </div>
                <?php if ($r['table_numero']): ?>
                <div class="flex items-center gap-2">
                    <span>🪑</span>
                    <span>Table n°<?= $r['table_numero'] ?> — <?= htmlspecialchars($r['localisation']) ?> (<?= $r['capacite'] ?> places)</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modal-<?= $r['id'] ?>')"
                        class="flex-1 text-center bg-indigo-50 text-indigo-600 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                    Détails
                </button>
                <?php if ($r['statut'] !== 'annulee'): ?>
                <a href="cancel_reservation.php?id=<?= (int)$r['id'] ?>"
                   onclick="return confirm('Annuler cette réservation ?')"
                   class="flex-1 text-center bg-red-50 text-red-600 py-2 rounded-lg text-sm font-semibold hover:bg-red-100 transition">
                    Annuler
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-<?= $r['id'] ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Réservation #<?= $r['id'] ?></h3>
                    <button onclick="closeModal('modal-<?= $r['id'] ?>')" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Date</span>
                        <span class="font-semibold"><?= $date->format('d/m/Y') ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Heure</span>
                        <span class="font-semibold"><?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Personnes</span>
                        <span class="font-semibold"><?= (int)$r['nb_personnes'] ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Table</span>
                        <span class="font-semibold">
                            <?= $r['table_numero'] ? 'N°'.$r['table_numero'].' — '.$r['localisation'] : 'Non assignée' ?>
                        </span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Statut</span>
                        <span class="badge <?= $statutColor ?>"><?= ucfirst(str_replace('_', ' ', $r['statut'])) ?></span>
                    </div>
                    <?php if (!empty($r['message'])): ?>
                    <div class="pt-1">
                        <p class="text-gray-500 mb-1">Message</p>
                        <p class="italic text-gray-700">"<?= htmlspecialchars($r['message']) ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex gap-3 mt-6">
                    <button onclick="closeModal('modal-<?= $r['id'] ?>')"
                            class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition">
                        Fermer
                    </button>
                    <?php if ($r['statut'] !== 'annulee'): ?>
                    <a href="cancel_reservation.php?id=<?= (int)$r['id'] ?>"
                       onclick="return confirm('Annuler cette réservation ?')"
                       class="flex-1 text-center bg-red-50 text-red-600 py-2 rounded-lg font-semibold hover:bg-red-100 transition">
                        Annuler
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Historique -->
    <?php if (count($passees) > 0): ?>
    <h2 class="text-lg font-semibold text-gray-400 uppercase tracking-wider mb-4">Historique</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($passees as $r): ?>
        <?php
            $date = new DateTime($r['date_reservation']);
            $statutColors = [
                'en_attente' => 'bg-yellow-100 text-yellow-700',
                'confirmee'  => 'bg-green-100 text-green-700',
                'annulee'    => 'bg-red-100 text-red-700',
            ];
            $statutColor = $statutColors[$r['statut']] ?? 'bg-gray-100 text-gray-700';
        ?>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-gray-300 opacity-70 card-hover">
            <div class="flex justify-between items-start mb-4">
                <p class="font-bold text-gray-600"><?= $date->format('d/m/Y') ?></p>
                <span class="badge <?= $statutColor ?>"><?= ucfirst(str_replace('_', ' ', $r['statut'])) ?></span>
            </div>
            <div class="space-y-1 text-sm text-gray-500">
                <p>🕐 <?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></p>
                <p>👥 <?= (int)$r['nb_personnes'] ?> personne<?= $r['nb_personnes'] > 1 ? 's' : '' ?></p>
                <?php if ($r['table_numero']): ?>
                <p>🪑 Table n°<?= $r['table_numero'] ?></p>
                <?php endif; ?>
            </div>
            <button onclick="openModal('modal-past-<?= $r['id'] ?>')"
                    class="mt-4 w-full bg-gray-100 text-gray-600 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
                Voir détails
            </button>
        </div>

        <!-- Modal historique -->
        <div id="modal-past-<?= $r['id'] ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Réservation #<?= $r['id'] ?></h3>
                    <button onclick="closeModal('modal-past-<?= $r['id'] ?>')" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Date</span>
                        <span class="font-semibold"><?= $date->format('d/m/Y') ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Heure</span>
                        <span class="font-semibold"><?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Personnes</span>
                        <span class="font-semibold"><?= (int)$r['nb_personnes'] ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Table</span>
                        <span class="font-semibold">
                            <?= $r['table_numero'] ? 'N°'.$r['table_numero'].' — '.$r['localisation'] : 'Non assignée' ?>
                        </span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Statut</span>
                        <span class="badge <?= $statutColor ?>"><?= ucfirst(str_replace('_', ' ', $r['statut'])) ?></span>
                    </div>
                    <?php if (!empty($r['message'])): ?>
                    <div class="pt-1">
                        <p class="text-gray-500 mb-1">Message</p>
                        <p class="italic text-gray-700">"<?= htmlspecialchars($r['message']) ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>
                <button onclick="closeModal('modal-past-<?= $r['id'] ?>')"
                        class="w-full mt-6 bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition">
                    Fermer
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Aucune réservation -->
    <?php if (count($reservations) === 0): ?>
    <div class="text-center bg-white rounded-2xl shadow p-12 max-w-md mx-auto">
        <div class="text-5xl mb-4">📅</div>
        <h2 class="text-xl font-bold mb-2">Aucune réservation</h2>
        <p class="text-gray-500 mb-6">Vous n'avez encore effectué aucune réservation.</p>
        <a href="/public/reservation.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold">
            Réserver une table →
        </a>
    </div>
    <?php endif; ?>

</main>

<footer class="bg-white py-6 text-center text-gray-400 text-sm mt-10 border-t">
    <p>&copy; <?= date('Y') ?> Le Gourmet Connecté</p>
</footer>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
    document.querySelectorAll('[id^="modal-"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });
</script>
</body>
</html>