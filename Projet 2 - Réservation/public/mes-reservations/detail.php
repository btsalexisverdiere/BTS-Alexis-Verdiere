<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Vérification de l'ID de réservation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=invalid_id');
    exit();
}

$reservationId = (int)$_GET['id'];

// Récupération des données de la réservation
try {
    // Ajout du téléphone dans la requête
    $stmt = $pdo->prepare("
        SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email
        FROM reservations r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        header('Location: index.php?error=not_found');
        exit();
    }

} catch (Exception $e) {
    error_log("Erreur lors de la récupération de la réservation: " . $e->getMessage());
    header('Location: index.php?error=db_error');
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
    default:
        $statusClass = 'bg-gray-100 text-gray-800';
        $statusText = ucfirst($reservation['statut']);
}

// Formater la date et l'heure
$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $reservation['date_reservation'] . ' ' . $reservation['heure']);
$formattedDate = $dateTime->format('d/m/Y');
$formattedTime = $dateTime->format('H:i');
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la réservation #<?= $reservation['id'] ?> - Le Gourmet Connecté</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #4f46e5;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .section-title {
            font-size: 1.125rem;
            line-height: 1.75rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #6b7280;
            display: block;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 500;
            color: #111827;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Réservation #<?= $reservation['id'] ?></h1>
                <a href="index.php" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Retour au dashboard
                </a>
            </div>

            <!-- Statut -->
            <div class="flex items-center gap-2 mb-6">
                <span class="status-badge <?= $statusClass ?>">
                    <?= $statusText ?>
                </span>
                <span class="text-sm text-gray-500">
                    Créée le <?= (new DateTime($reservation['created_at']))->format('d/m/Y H:i') ?>
                </span>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="card">
            <!-- Informations client -->
            <div class="mb-8">
                <h2 class="section-title">
                    <i class="fas fa-user mr-2 text-indigo-500"></i>
                    Informations client
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nom complet</div>
                        <div class="info-value"><?= htmlspecialchars($reservation['username'] ?? ($reservation['name'] . ' ' . $reservation['lastname'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($reservation['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><?= htmlspecialchars($reservation['telephone'] ?? 'Non renseigné') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID utilisateur</div>
                        <div class="info-value"><?= $reservation['user_id'] ? $reservation['user_id'] : 'Invité' ?></div>
                    </div>
                </div>
            </div>

            <!-- Détails de la réservation -->
            <div class="mb-8">
                <h2 class="section-title">
                    <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>
                    Détails de la réservation
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?= $formattedDate ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Heure</div>
                        <div class="info-value"><?= $formattedTime ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nombre de personnes</div>
                        <div class="info-value"><?= (int)$reservation['nb_personnes'] ?></div>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <?php if (!empty($reservation['message'])): ?>
            <div class="mb-8">
                <h2 class="section-title">
                    <i class="fas fa-comment-alt mr-2 text-indigo-500"></i>
                    Message du client
                </h2>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="whitespace-pre-line"><?= nl2br(htmlspecialchars($reservation['message'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="action-buttons">
                <a href="modifier_reservation.php?id=<?= $reservation['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i> Modifier
                </a>
                <?php if ($reservation['statut'] !== 'annulee'): ?>
                    <form method="post" action="actions.php" onsubmit="return confirm('Voulez-vous vraiment annuler cette réservation ?');">
                        <input type="hidden" name="action" value="cancel_reservation">
                        <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['session_key'] ?? '') ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-white mt-auto py-6 text-center text-gray-500 text-sm">
        <p>&copy; <?= date('Y') ?> Le Gourmet Connecté — Restaurant gastonomique moderne</p>
    </footer>
</body>
</html>