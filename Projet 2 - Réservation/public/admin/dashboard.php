<?php
// dashboard.php — Admin Dashboard
session_start();
require_once __DIR__ . '/../../config/db.php';

// ───── Sécurité ─────
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// CSRF
if (empty($_SESSION['session_key'])) {
    $_SESSION['session_key'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['session_key'];

// ───── Paramètres ─────
$search = trim($_GET['q'] ?? '');
$filter_date = $_GET['date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// ───── Fonctions SQL directement dans la page ─────

// Compter le nombre total de réservations (avec filtres)
function countReservations($pdo, $search = '', $filter_date = '') {
    $sql = "SELECT COUNT(*) FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE (u.name LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search OR r.message LIKE :search)
            AND (:filter_date = '' OR r.date_reservation = :filter_date)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':filter_date', $filter_date);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

// Récupérer les réservations du jour
function getTodayReservations($pdo) {
    $today = date('Y-m-d');
    $sql = "SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.date_reservation = :today
            ORDER BY r.heure";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':today', $today);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les réservations à venir
function getNextReservations($pdo) {
    $sql = "SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE (r.date_reservation > CURDATE() OR (r.date_reservation = CURDATE() AND r.heure >= CURTIME()))
            AND r.statut != 'annulee'
            ORDER BY r.date_reservation, r.heure
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer toutes les réservations (avec pagination et filtres)
function getAllReservations($pdo, $search = '', $filter_date = '', $offset = 0, $perPage = 15) {
    $sql = "SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE (u.name LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search OR r.message LIKE :search)
            AND (:filter_date = '' OR r.date_reservation = :filter_date)
            ORDER BY r.date_reservation DESC, r.heure DESC
            LIMIT :offset, :perPage";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':filter_date', $filter_date);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ───── Statistiques ─────
$cUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$cReservations = (int)$pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$cnbUpcoming = (int)$pdo->query("
    SELECT COUNT(*) FROM reservations
    WHERE date_reservation > CURDATE()
       OR (date_reservation = CURDATE() AND heure >= CURTIME())
")->fetchColumn();
$cTables = (int)$pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();


// ───── Réservations ─────
$total = countReservations($pdo, $search, $filter_date);
$pages = max(1, (int)ceil($total / $perPage));
$nextreservations = getNextReservations($pdo);
$todayreservation = getTodayReservations($pdo);
$allreservations = getAllReservations($pdo, $search, $filter_date, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin • Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <style>
        :root {
            --color-primary: #4f46e5;
            --color-primary-light: #818cf8;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #3b82f6;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .status-badge {
            @apply inline-block px-2 py-1 text-xs font-medium rounded-full;
        }

        .status-confirmed { @apply bg-green-100 text-green-800; }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-cancelled { @apply bg-red-100 text-red-800; }
        .status-completed { @apply bg-blue-100 text-blue-800; }

        .table-responsive {
            overflow-x: auto;
        }

        .table-responsive table {
            min-width: 100%;
        }

        .table-responsive th,
        .table-responsive td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .table-responsive th {
            font-weight: 600;
            color: #6b7280;
            text-align: left;
        }

        .action-buttons {
            @apply flex gap-2;
        }

        .btn {
            @apply px-3 py-1 rounded text-sm font-medium transition-colors duration-200;
        }

        .btn-primary {
            @apply bg-indigo-600 hover:bg-indigo-700 text-white;
        }

        .btn-success {
            @apply bg-green-600 hover:bg-green-700 text-white;
        }

        .btn-warning {
            @apply bg-yellow-500 hover:bg-yellow-600 text-white;
        }

        .btn-danger {
            @apply bg-red-600 hover:bg-red-700 text-white;
        }

        .btn-info {
            @apply bg-blue-500 hover:bg-blue-600 text-white;
        }

        .card {
            @apply bg-white rounded-lg shadow-sm border border-gray-100 p-4;
        }

        .modal-overlay {
            @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4;
        }

        .modal-content {
            @apply bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto relative;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen">
<?php include __DIR__ . '/navbar.php'; ?>

<main class="container mx-auto px-4 py-8">
    <!-- ───── Header ───── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Administrateur</h1>
            <p class="text-gray-500 text-sm">Gestion des réservations et statistiques</p>
        </div>
        <form method="get" class="flex flex-wrap gap-2">
            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Rechercher utilisateur / email"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300"
            >
            <input
                type="date"
                name="date"
                value="<?= htmlspecialchars($filter_date) ?>"
                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300"
            >
            <button class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Filtrer
            </button>
        </form>
    </div>

    <!-- ───── Stats ───── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <?php
        $stats = [
            ['Réservations', $cReservations, 'fas fa-calendar-check', 'text-indigo-600'],
            ['À venir', $cnbUpcoming, 'fas fa-calendar-day', 'text-green-600'],
            ['Utilisateurs', $cUsers, 'fas fa-users', 'text-blue-600'],
            ['Tables', $cTables, 'fas fa-chair', 'text-purple-600'],

        ];
        foreach ($stats as [$label, $value, $icon, $color]):
        ?>
            <div class="card">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-indigo-50 <?=$color?>">
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500"><?= $label ?></div>
                        <div class="text-2xl font-bold text-gray-900"><?= $value ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ───── Réservations du jour ───── -->
    <div class="card mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Réservations du jour (<span class="text-indigo-600"><?= count($todayreservation) ?></span>)</h2>
        </div>

        <div class="table-responsive">
            <table>
                <thead class="bg-gray-50">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Pers.</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todayreservation as $tr):
                        $statutClass = '';
                        switch($tr['statut']) {
                            case 'confirmee': $statutClass = 'status-confirmed'; break;
                            case 'en_attente': $statutClass = 'status-pending'; break;
                            case 'annulee': $statutClass = 'status-cancelled'; break;
                            case 'terminee': $statutClass = 'status-completed'; break;
                        }
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="font-medium"><?= (int)$tr['id'] ?></td>
                            <td>
                                <div class="font-medium"><?= htmlspecialchars($tr['username'] ?? ($tr['name'].' '.$tr['lastname'])) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($tr['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($tr['date_reservation']) ?></td>
                            <td><?= htmlspecialchars(substr($tr['heure'], 0, 5)) ?></td>
                            <td><?= (int)$tr['nb_personnes'] ?></td>
                            <td><?= nl2br(htmlspecialchars(substr($tr['message'], 0, 80))) ?></td>
                            <td><span class="status-badge <?= $statutClass ?>"><?= ucfirst($tr['statut']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="view-btn btn btn-info" data-id="<?= (int)$tr['id'] ?>">
                                        <i class="fas fa-eye mr-1"></i> Voir
                                    </button>
                                    <a href="modifier_reservation.php?id=<?= (int)$tr['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit mr-1"></i> Éditer
                                    </a>
                                    <form method="post" action="actions.php" onsubmit="return confirm('Supprimer cette réservation ?');">
                                        <input type="hidden" name="action" value="delete_reservation">
                                        <input type="hidden" name="id" value="<?= (int)$tr['id'] ?>">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash mr-1"></i> Suppr
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($todayreservation)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">Aucune réservation aujourd'hui</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ───── Réservations à venir ───── -->
    <div class="card mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Réservations à venir (<span class="text-indigo-600"><?= count($nextreservations) ?></span>)</h2>
        </div>

        <div class="table-responsive">
            <table>
                <thead class="bg-gray-50">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Pers.</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nextreservations as $r):
                        $statutClass = '';
                        switch($r['statut']) {
                            case 'confirmee': $statutClass = 'status-confirmed'; break;
                            case 'en_attente': $statutClass = 'status-pending'; break;
                            case 'annulee': $statutClass = 'status-cancelled'; break;
                            case 'terminee': $statutClass = 'status-completed'; break;
                        }
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="font-medium"><?= (int)$r['id'] ?></td>
                            <td>
                                <div class="font-medium"><?= htmlspecialchars($r['username'] ?? ($r['name'].' '.$r['lastname'])) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($r['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($r['date_reservation']) ?></td>
                            <td><?= htmlspecialchars(substr($r['heure'], 0, 5)) ?></td>
                            <td><?= (int)$r['nb_personnes'] ?></td>
                            <td><?= nl2br(htmlspecialchars(substr($r['message'], 0, 80))) ?></td>
                            <td><span class="status-badge <?= $statutClass ?>"><?= ucfirst($r['statut']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="view-btn btn btn-info" data-id="<?= (int)$r['id'] ?>">
                                        <i class="fas fa-eye mr-1"></i> Voir
                                    </button>
                                    <a href="modifier_reservation.php?id=<?= (int)$r['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit mr-1"></i> Éditer
                                    </a>
                                    <form method="post" action="actions.php" onsubmit="return confirm('Supprimer cette réservation ?');">
                                        <input type="hidden" name="action" value="delete_reservation">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash mr-1"></i> Suppr
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($nextreservations)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">Aucune réservation à venir</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ───── Toutes les réservations (accordéon) ───── -->
    <div class="card">
        <details class="mb-4">
            <summary class="cursor-pointer font-semibold bg-gray-100 p-3 rounded-lg flex justify-between items-center">
                <span>Toutes les réservations (<span class="text-indigo-600"><?= $total ?></span>)</span>
                <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </summary>
            <div class="mt-3">
                <div class="table-responsive">
                    <table>
                        <thead class="bg-gray-50">
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Pers.</th>
                                <th>Message</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allreservations as $rvall):
                                $statutClass = '';
                                switch($rvall['statut']) {
                                    case 'confirmee': $statutClass = 'status-confirmed'; break;
                                    case 'en_attente': $statutClass = 'status-pending'; break;
                                    case 'annulee': $statutClass = 'status-cancelled'; break;
                                    case 'terminee': $statutClass = 'status-completed'; break;
                                }
                            ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="font-medium"><?= (int)$rvall['id'] ?></td>
                                    <td>
                                        <div class="font-medium"><?= htmlspecialchars($rvall['username'] ?? ($rvall['name'].' '.$rvall['lastname'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($rvall['email']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($rvall['date_reservation']) ?></td>
                                    <td><?= htmlspecialchars(substr($rvall['heure'], 0, 5)) ?></td>
                                    <td><?= (int)$rvall['nb_personnes'] ?></td>
                                    <td><?= nl2br(htmlspecialchars(substr($rvall['message'], 0, 80))) ?></td>
                                    <td><span class="status-badge <?= $statutClass ?>"><?= ucfirst($rvall['statut']) ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="view-btn btn btn-info" data-id="<?= (int)$rvall['id'] ?>">
                                                <i class="fas fa-eye mr-1"></i> Voir
                                            </button>
                                            <a href="modifier_reservation.php?id=<?= (int)$rvall['id'] ?>" class="btn btn-warning">
                                                <i class="fas fa-edit mr-1"></i> Éditer
                                            </a>
                                            <form method="post" action="actions.php" onsubmit="return confirm('Supprimer cette réservation ?');">
                                                <input type="hidden" name="action" value="delete_reservation">
                                                <input type="hidden" name="id" value="<?= (int)$rvall['id'] ?>">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash mr-1"></i> Suppr
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allreservations)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-gray-500">Aucune réservation trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                    <div class="mt-6 flex justify-center">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                            <?php for ($p = 1; $p <= $pages; $p++): ?>
                                <a
                                    href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                                    class="px-4 py-2 text-sm font-medium border <?= $p === $page ? 'bg-indigo-600 text-white border-indigo-600' : 'text-gray-700 bg-white hover:bg-gray-50 border-gray-300' ?> rounded-l-lg <?php if ($p === 1) echo 'rounded-l-lg'; if ($p === $pages) echo 'rounded-r-lg'; ?>"
                                >
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </details>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'accordéon
    document.querySelectorAll('details').forEach(detail => {
        detail.addEventListener('toggle', function() {
            const icon = this.querySelector('i.fa-chevron-down');
            if (this.open) {
                icon.classList.add('rotate-180');
            } else {
                icon.classList.remove('rotate-180');
            }
        });
    });

    // Gestion de la modale
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const modal = document.getElementById('modal');
            const content = document.getElementById('modal-content');

            modal.classList.remove('hidden');
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i></div>';

            try {
                const res = await fetch('actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'fetch_reservation',
                        id: id,
                        csrf: '<?= htmlspecialchars($csrf) ?>'
                    })
                });

                if (!res.ok) throw new Error('Erreur serveur');

                content.innerHTML = await res.text();
            } catch (error) {
                content.innerHTML = `<div class="text-red-500">Erreur lors du chargement: ${error.message}</div>`;
            }
        });
    });

    // Fermeture de la modale
    document.getElementById('modal-close').onclick = () =>
        document.getElementById('modal').classList.add('hidden');

    window.onclick = e => {
        if (e.target.id === 'modal')
            document.getElementById('modal').classList.add('hidden');
    };
});
</script>

<!-- ───── Modal ───── -->
<div id="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative">
        <!-- Bouton de fermeture -->
        <button id="modal-close" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 transition">
            <i class="fas fa-times text-lg"></i>
        </button>

        <!-- Contenu de la modale (chargé dynamiquement) -->
        <div id="modal-content" class="p-6">
            <!-- Contenu chargé via AJAX -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons "Voir"
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const reservationId = this.dataset.id;
            const modal = document.getElementById('modal');
            const content = document.getElementById('modal-content');

            // Afficher la modale avec un loader
            modal.classList.remove('hidden');
            content.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                    <p class="mt-4 text-gray-600">Chargement en cours...</p>
                </div>
            `;

            try {
                // Requête AJAX pour récupérer les détails de la réservation
                const response = await fetch('actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'fetch_reservation',
                        id: reservationId,
                        csrf: '<?= htmlspecialchars($csrf) ?>'
                    })
                });

                if (!response.ok) {
                    throw new Error(`Erreur serveur: ${response.status}`);
                }

                content.innerHTML = await response.text();
            } catch (error) {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-2"></i>
                        <p class="text-red-500 mb-1">Erreur lors du chargement</p>
                        <p class="text-gray-500 text-sm">${error.message}</p>
                    </div>
                `;
                console.error('Erreur:', error);
            }
        });
    });

    // Fermeture de la modale
    document.getElementById('modal-close').addEventListener('click', function() {
        document.getElementById('modal').classList.add('hidden');
    });

    // Fermeture en cliquant à l'extérieur
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});
</script>

</body>
</html>