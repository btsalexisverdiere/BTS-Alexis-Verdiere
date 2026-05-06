<?php
// modifier_reservation.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Vérification de sécurité
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Vérification de l'ID de réservation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php?error=invalid_id');
    exit();
}

$reservationId = (int)$_GET['id'];

// Récupération des données de la réservation
try {
    // Requête corrigée avec le paramètre manquant
    $stmt = $pdo->prepare("
        SELECT r.*, CONCAT(u.name, ' ', u.lastname) as username, u.email
        FROM reservations r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        header('Location: dashboard.php?error=not_found');
        exit();
    }


} catch (Exception $e) {
    error_log("Erreur lors de la récupération de la réservation: " . $e->getMessage());
    header('Location: dashboard.php?error=db_error');
    exit();
}

// Traitement du formulaire de modification
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['session_key'] ?? '')) {
        $errors[] = "Token CSRF invalide";
    } else {
        try {
            // Validation des données
            $date = $_POST['date_reservation'] ?? '';
            $heure = $_POST['heure'] ?? '';
            $nb_personnes = (int)($_POST['nb_personnes'] ?? 0);
            $message = $_POST['message'] ?? '';
            $menu_id = $_POST['menu_id'] ?? null;
            $statut = $_POST['statut'] ?? '';

            // Validation basique
            if (empty($date) || empty($heure) || $nb_personnes < 1) {
                $errors[] = "Tous les champs obligatoires ne sont pas remplis";
            } else {
                // Combinaison date + heure pour validation
                $reservationDatetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $heure);
                if (!$reservationDatetime) {
                    $errors[] = "Date ou heure invalide";
                } else {
                    // Mise à jour dans la base de données
                    $stmt = $pdo->prepare("
                        UPDATE reservations
                        SET date_reservation = ?, heure = ?, nb_personnes = ?, message = ?, menu_id = ?, statut = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $date,
                        $heure,
                        $nb_personnes,
                        $message,
                        $menu_id,
                        $statut,
                        $reservationId
                    ]);

                    $success = true;
                }
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
    }
}

// Génération du token CSRF si nécessaire
if (empty($_SESSION['session_key'])) {
    $_SESSION['session_key'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['session_key'];
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la réservation #<?= $reservationId ?></title>
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .error-message {
            color: var(--color-danger);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-success {
            background-color: var(--color-success);
            color: white;
        }

        .btn-danger {
            background-color: var(--color-danger);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-2xl font-bold text-gray-900">Modifier la réservation #<?= $reservationId ?></h1>
                <a href="dashboard.php" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Retour au dashboard
                </a>
            </div>

            <!-- Statut actuel -->
            <div class="flex items-center gap-2">
                <span class="status-badge
                    <?= $reservation['statut'] === 'confirmee' ? 'bg-green-100 text-green-800' :
                       ($reservation['statut'] === 'en_attente' ? 'bg-yellow-100 text-yellow-800' :
                       ($reservation['statut'] === 'annulee' ? 'bg-red-100 text-red-800' :
                       ($reservation['statut'] === 'terminee' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))) ?>">
                    <?= ucfirst($reservation['statut']) ?>
                </span>
                <span class="text-sm text-gray-500">
                    Créée le <?= (new DateTime($reservation['created_at']))->format('d/m/Y H:i') ?>
                </span>
            </div>
        </div>

        <!-- Messages d'erreur/succès -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erreur(s) lors de la modification</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Réservation modifiée avec succès !</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <form method="POST" class="bg-white rounded-lg shadow-sm p-6">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">

            <!-- Informations client (non modifiables) -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user mr-2 text-indigo-500"></i> Informations client
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Nom</div>
                        <div class="font-medium"><?= htmlspecialchars($reservation['username'] ?? ($reservation['name'] . ' ' . $reservation['lastname'])) ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="font-medium"><?= htmlspecialchars($reservation['email'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Téléphone</div>
                        <div class="font-medium"><?= htmlspecialchars($reservation['telephone'] ?? 'Non renseigné') ?></div>
                    </div>
                </div>
            </div>

            <!-- Détails de la réservation (modifiables) -->
            <div class="space-y-4">
                <div class="form-group">
                    <label for="date_reservation" class="form-label">Date de réservation</label>
                    <input type="date" id="date_reservation" name="date_reservation"
                           value="<?= htmlspecialchars($reservation['date_reservation']) ?>"
                           class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="heure" class="form-label">Heure</label>
                    <input type="time" id="heure" name="heure"
                           value="<?= htmlspecialchars(substr($reservation['heure'], 0, 5)) ?>"
                           class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="nb_personnes" class="form-label">Nombre de personnes</label>
                    <input type="number" id="nb_personnes" name="nb_personnes"
                           value="<?= htmlspecialchars($reservation['nb_personnes']) ?>"
                           min="1" max="12" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="menu_id" class="form-label">Menu</label>
                    <select id="menu_id" name="menu_id" class="form-select">
                        <option value="">À la carte</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?= $menu['id'] ?>"
                                <?= $reservation['menu_id'] == $menu['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($menu['nom']) ?> (<?= number_format($menu['prix'], 2) ?>€)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut" class="form-label">Statut</label>
                    <select id="statut" name="statut" class="form-select" required>
                        <option value="en_attente" <?= $reservation['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="confirmee" <?= $reservation['statut'] === 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                        <option value="annulee" <?= $reservation['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                        <option value="terminee" <?= $reservation['statut'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Message (optionnel)</label>
                    <textarea id="message" name="message" class="form-textarea" rows="3"><?= htmlspecialchars($reservation['message'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end gap-3 mt-6">
                <a href="dashboard.php" class="btn px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary px-4 py-2">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script>
        // Limiter le nombre de personnes entre 1 et 12
        document.getElementById('nb_personnes').addEventListener('input', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) value = 1;
            if (value > 12) value = 12;
            this.value = value;
        });
    </script>
</body>
</html>