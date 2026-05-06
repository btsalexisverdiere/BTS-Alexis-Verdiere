<?php
// actions.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// sécurité
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Accès refusé";
    exit();
}

$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !isset($_SESSION['session_key']) || !hash_equals($_SESSION['session_key'], $csrf)) {
    http_response_code(400);
    echo "Token CSRF invalide.";
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'delete_reservation':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo "ID invalide"; exit(); }
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: dashboard.php?msg=deleted');
        exit();
    case 'fetch_reservation':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT r.*, u.username, u.name, u.lastname, u.email FROM reservations r LEFT JOIN users u ON r.user_id=u.id WHERE r.id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) { echo "Introuvable"; exit(); }
        // affichage HTML minimal (retourné dans modal)
        echo "<h3 class='text-xl font-bold mb-2'>Réservation #".(int)$r['id']."</h3>";
        echo "<p><strong>Client :</strong> ".htmlspecialchars($r['username'].' ('.$r['name'].' '.$r['lastname'].')')."</p>";
        echo "<p><strong>Email :</strong> ".htmlspecialchars($r['email'])."</p>";
        echo "<p><strong>Date :</strong> ".htmlspecialchars($r['date_reservation'])." ".htmlspecialchars(substr($r['heure'],0,5))."</p>";
        echo "<p><strong>Personnes :</strong> ".(int)$r['nb_personnes']."</p>";
        echo "<p class='mt-2'><strong>Message :</strong><br>".nl2br(htmlspecialchars($r['message']))."</p>";
        echo "<div class='mt-4 flex gap-2'> <a class='bg-green-500 px-3 py-1 rounded text-white' href='view_reservation.php?id=".(int)$r['id']."'>Voir la réservation</a><a class='bg-yellow-500 px-3 py-1 rounded text-white' href='modifier_reservation.php?id=".(int)$r['id']."'>Modifier</a>";
        echo "<form method='post' action='actions.php' onsubmit=\"return confirm('Supprimer ?');\">";
        echo "<input type='hidden' name='action' value='delete_reservation'><input type='hidden' name='id' value='".(int)$r['id']."'>";
        echo "<input type='hidden' name='csrf' value='".htmlspecialchars($csrf)."'>";
        echo "<button class='bg-red-600 px-3 py-1 rounded text-white' type='submit'>Supprimer</button></form></div>";
        exit();
    case 'export_csv':
        // redirige vers export_csv.php qui vérifiera le CSRF et le rôle
        header('Location: export_csv.php?csrf=' . urlencode($csrf));
        exit();
    default:
        echo "Action inconnue";
        exit();
}




// Vérification de la sécurité
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Accès refusé";
    exit();
}

// Vérification CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf'])) {
    if (!isset($_SESSION['session_key']) || $_POST['csrf'] !== $_SESSION['session_key']) {
        http_response_code(403);
        echo "<div class='p-4 text-red-500'>Token CSRF invalide</div>";
        exit();
    }

    // Action pour récupérer une réservation
    if ($_POST['action'] === 'fetch_reservation' && isset($_POST['id'])) {
        try {
            $reservationId = (int)$_POST['id'];
            $stmt = $pdo->prepare("
                SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email, u.telephone
                FROM reservations r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$reservationId]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                http_response_code(404);
                echo "<div class='p-4 text-center text-red-500'>Réservation non trouvée</div>";
                exit();
            }

            // Déterminer la classe CSS pour le statut
            $statusClass = '';
            $statusText = '';
            switch($reservation['statut']) {
                case 'confirmee':
                    $statusClass = 'bg-green-100 text-green-800';
                    $statusText = 'Confirmée';
                    break;
                case 'en_attente':
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusText = 'En attente';
                    break;
                case 'annulee':
                    $statusClass = 'bg-red-100 text-red-800';
                    $statusText = 'Annulée';
                    break;
                case 'terminee':
                    $statusClass = 'bg-blue-100 text-blue-800';
                    $statusText = 'Terminée';
                    break;
            }

            // Formater la date et l'heure
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $reservation['date_reservation'] . ' ' . $reservation['heure']);
            $formattedDate = $dateTime->format('d/m/Y');
            $formattedTime = $dateTime->format('H:i');

            // Récupérer le menu si disponible
            $menuName = '';
            if ($reservation['menu_id']) {
                $menuStmt = $pdo->prepare("SELECT nom, prix FROM menus WHERE id = ?");
                $menuStmt->execute([$reservation['menu_id']]);
                $menu = $menuStmt->fetch(PDO::FETCH_ASSOC);
                $menuName = $menu ? htmlspecialchars($menu['nom']) . ' (' . number_format($menu['prix'], 2) . '€)' : 'Menu #' . $reservation['menu_id'];
            }
            ?>
            <div class="space-y-6">
                <!-- En-tête -->
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Réservation #<?= $reservation['id'] ?></h2>
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?> mt-1">
                                <?= $statusText ?>
                            </span>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <div>Créée le <?= (new DateTime($reservation['created_at']))->format('d/m/Y H:i') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Informations client -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-indigo-500"></i> Informations client
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Nom complet</div>
                            <div class="font-medium"><?= htmlspecialchars($reservation['username'] ?? ($reservation['name'] . ' ' . $reservation['lastname'])) ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Email</div>
                            <div class="font-medium"><?= htmlspecialchars($reservation['email']) ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Téléphone</div>
                            <div class="font-medium"><?= htmlspecialchars($reservation['telephone'] ?? 'Non renseigné') ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">ID utilisateur</div>
                            <div class="font-medium"><?= $reservation['user_id'] ? $reservation['user_id'] : 'Invité' ?></div>
                        </div>
                    </div>
                </div>

                <!-- Détails de la réservation -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i> Détails de la réservation
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Date</div>
                            <div class="font-medium"><?= $formattedDate ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Heure</div>
                            <div class="font-medium"><?= $formattedTime ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Nombre de personnes</div>
                            <div class="font-medium"><?= (int)$reservation['nb_personnes'] ?></div>
                        </div>
                        <?php if ($reservation['menu_id']): ?>
                        <div>
                            <div class="text-sm text-gray-500">Menu</div>
                            <div class="font-medium"><?= $menuName ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Message du client -->
                <?php if (!empty($reservation['message'])): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-comment-alt mr-2 text-indigo-500"></i> Message du client
                    </h3>
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                        <?= nl2br(htmlspecialchars($reservation['message'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="modifier_reservation.php?id=<?= $reservation['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </a>
                    <form method="post" action="actions.php" class="inline" onsubmit="return confirm('Voulez-vous vraiment supprimer cette réservation ?');">
                        <input type="hidden" name="action" value="delete_reservation">
                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['session_key']) ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
            <?php
        } catch (Exception $e) {
            http_response_code(500);
            echo "<div class='p-4 text-center text-red-500'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        exit();
    }
}

// Autres actions (suppression, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_reservation' && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$_POST['id']]);

            // Redirection avec message de succès
            $_SESSION['success_message'] = "Réservation supprimée avec succès";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
}

// Si aucune action valide n'est spécifiée
http_response_code(400);
echo "<div class='p-4 text-center text-red-500'>Action invalide</div>";
?>