<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Initialisation des variables
$success = '';
$errors = [];
$userData = [
    'name' => '',
    'lastname' => '',
    'email' => ''
];
$formSubmitted = false;
$minDate = date('Y-m-d');

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT `name`, `lastname`, `email` FROM `users` WHERE `id` = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC) ?: $userData;
        $id = $_SESSION['user_id'];
    } catch (PDOException $e) {
        error_log("Erreur base de données: " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors du chargement de vos données.";
    }
} else {
    $id = 4; // ID de l'utilisateur invité
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    $errors = [];

    // Récupérer les valeurs par défaut pour les utilisateurs connectés
        $lastname = isset($_SESSION['user_id']) ?
           htmlspecialchars($userData['lastname'], ENT_QUOTES) :
           (isset($_POST['lastname']) ? htmlspecialchars(trim($_POST['lastname']), ENT_QUOTES) : '');

        $name = isset($_SESSION['user_id']) ?
        htmlspecialchars($userData['name'], ENT_QUOTES) :
        (isset($_POST['name']) ? htmlspecialchars(trim($_POST['name']), ENT_QUOTES) : '');

        $email = isset($_SESSION['user_id']) ?
         htmlspecialchars($userData['email'], ENT_QUOTES) :
         (isset($_POST['email']) ? htmlspecialchars(trim($_POST['email']), ENT_QUOTES) : '');

    // Validation des champs obligatoires
    $requiredFields = [
        'lastname' => $lastname,
        'name' => $name,
        'email' => $email,
        'date_reservation' => $_POST['date_reservation'] ?? '',
        'heure' => $_POST['heure'] ?? '',
        'nb_personnes' => $_POST['nb_personnes'] ?? ''
    ]; 

    foreach ($requiredFields as $field => $value) {
        if (empty($value)) {
            if ($field === 'lastname' && isset($_SESSION['user_id'])) {
                // Ne pas marquer comme erreur si l'utilisateur est connecté
                continue;
            }
            if ($field === 'name' && isset($_SESSION['user_id'])) {
                // Ne pas marquer comme erreur si l'utilisateur est connecté
                continue;
            }
            if ($field === 'email' && isset($_SESSION['user_id'])) {
                // Ne pas marquer comme erreur si l'utilisateur est connecté
                continue;
            }
            $requiredFieldsLabels = [
                'lastname' => 'Nom',
                'name' => 'Prénom',
                'email' => 'Email',
                'date_reservation' => 'Date',
                'heure' => 'Heure',
                'nb_personnes' => 'Nombre de personnes'
            ];
            $errors[$field] = $requiredFieldsLabels[$field] . " est obligatoire.";
        }
    }

    // Validation spécifique pour l'email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'email n'est pas valide.";
    }

    // Validation date et heure
    if (empty($errors['date_reservation']) && empty($errors['heure'])) {
        try {
            $date = $_POST['date_reservation'];
            $time = $_POST['heure'];

            // Ajouter les secondes pour le format complet
            if (strpos($time, ':') === false) {
                $time .= ':00';
            }

            // Combiner date et heure pour validation
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
            if (!$dateTime) {
                $errors['date_reservation'] = "Format de date/heure invalide.";
            } else {
                $now = new DateTime();
                if ($dateTime < $now) {
                    $errors['date_reservation'] = "La réservation doit être dans le futur.";
                }

                // Vérifier les plages horaires (11h-14h et 19h-22h)
                $hour = (int)$dateTime->format('H');
                if (!((($hour >= 11 && $hour < 14) || ($hour >= 19 && $hour < 22)))) {
                    $errors['date_reservation'] = "Veuillez sélectionner un créneau horaire valide (11h-14h ou 19h-22h).";
                }
            }
        } catch (Exception $e) {
            $errors['date_reservation'] = "Date ou heure invalide.";
        }
    }

    // Validation pour le nombre de personnes
    if (!empty($_POST['nb_personnes'])) {
        $nbPersonnes = (int)$_POST['nb_personnes'];
        if ($nbPersonnes < 1 || $nbPersonnes > 12) {
            $errors['nb_personnes'] = "Le nombre de personnes doit être entre 1 et 12.";
        }
    } else {
        $errors['nb_personnes'] = "Le nombre de personnes est obligatoire.";
    }

    // Si pas d'erreurs, insérer en base
if (empty($errors)) {
    try {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $_POST['date_reservation'] . ' ' . $_POST['heure']);
        $formattedDate = $dateTime->format('Y-m-d');
        $formattedTime = $dateTime->format('H:i:s');

        // ── Recherche automatique d'une table disponible ──────────────────
        $stmtTable = $pdo->prepare("
            SELECT t.id 
            FROM tables t
            WHERE t.capacite >= :nb_personnes
            AND t.statut = 'libre'
            AND t.id NOT IN (
                SELECT r.id_table 
                FROM reservations r
                WHERE r.date_reservation = :date
                AND r.heure = :heure
                AND r.statut != 'annulee'
                AND r.id_table IS NOT NULL
            )
            ORDER BY t.capacite ASC
            LIMIT 1
        ");

        $stmtTable->execute([
            ':nb_personnes' => (int)$_POST['nb_personnes'],
            ':date'         => $formattedDate,
            ':heure'        => $formattedTime,
        ]);

        $table = $stmtTable->fetch(PDO::FETCH_ASSOC);

        if (!$table) {
            $errors['database'] = "Aucune table disponible pour ce créneau avec " . (int)$_POST['nb_personnes'] . " personne(s). Veuillez choisir un autre horaire.";
        } else {
            $id_table = $table['id'];

            // ── INSERT avec id_table ──────────────────────────────────────
            $stmt = $pdo->prepare("
                INSERT INTO reservations
                (user_id, lastname, name, email, date_reservation, heure, nb_personnes, message, id_table, statut, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
            ");

            $stmt->execute([
                $id,
                isset($_SESSION['user_id']) ? $userData['lastname'] : htmlspecialchars($_POST['lastname']),
                isset($_SESSION['user_id']) ? $userData['name']     : htmlspecialchars($_POST['name']),
                isset($_SESSION['user_id']) ? $userData['email']    : filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                $formattedDate,
                $formattedTime,
                (int)$_POST['nb_personnes'],
                htmlspecialchars($_POST['message'] ?? ''),
                $id_table
            ]);

            $success = "✅ Votre réservation a été enregistrée avec succès !";

            // ── Email de confirmation ─────────────────────────────────────
            $subject = "Confirmation de votre réservation - Le Gourmet Connecté";
            $message = "Bonjour " . (isset($_SESSION['user_id']) ? $userData['name'] : htmlspecialchars($_POST['name'])) . ",\n\n" .
                       "Votre réservation pour le " . $dateTime->format('d/m/Y') .
                       " à " . $dateTime->format('H:i') .
                       " pour " . (int)$_POST['nb_personnes'] . " personne(s) a été enregistrée.\n\n" .
                       "Merci de votre confiance !\nLe Gourmet Connecté";

            $headers = "From: reservation@le-gourmet-connecte.fr\r\n" .
                       "Reply-To: reservation@le-gourmet-connecte.fr\r\n";

            mail(isset($_SESSION['user_id']) ? $userData['email'] : $_POST['email'], $subject, $message, $headers);

            sleep(2);
            header('Location: /public/mes-reservations/detail.php?id=' . $pdo->lastInsertId());
            exit;
        }

    } catch (Exception $e) {
        error_log("Erreur réservation: " . $e->getMessage());
        $errors['database'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
}
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver une table - Le Gourmet Connecté</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #4f46e5;
            --color-primary-dark: #4338ca;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .card {
            @apply bg-white rounded-lg shadow-sm border border-gray-100 p-6;
        }

        .form-group {
            @apply mb-6;
        }

        .form-label {
            @apply block text-sm font-medium text-gray-700 mb-2;
        }

        .form-input, .form-select, .form-textarea {
            @apply w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                   focus:border-indigo-500 transition;
        }

        .form-input:disabled {
            @apply bg-gray-100 cursor-not-allowed;
        }

        .error-message {
            @apply mt-1 text-xs text-red-600;
        }

        .has-error .form-input,
        .has-error .form-select,
        .has-error .form-textarea {
            border-color: #ef4444;
        }

        .btn {
            @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
        }

        .btn-primary {
            @apply bg-indigo-600 hover:bg-indigo-700 text-white;
        }

        .status-badge {
            @apply inline-block px-2 py-1 text-xs font-medium rounded-full;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- En-tête -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Réserver une table</h1>
            <p class="text-gray-600 mt-2">Choisissez votre date et heure</p>
        </div>

        <!-- Messages de succès/erreur -->
        <?php if ($success && $formSubmitted): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-700"><?= $success ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors) && $formSubmitted): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erreurs dans le formulaire</h3>
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
        <?php endif; ?>

        <!-- Formulaire de réservation -->
        <form method="POST" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <!-- Nom et Prénom -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="<?= (isset($errors['lastname']) && $formSubmitted) ? 'has-error' : '' ?>">
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                    <input
                        type="text"
                        id="lastname"
                        name="lastname"
                        value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($userData['lastname']) : htmlspecialchars($_POST['lastname'] ?? '') ?>"
                        <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>
                        class="w-full px-3 py-2 border <?= (isset($errors['lastname']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Dupont"
                        required
                    >
                    <?php if (isset($errors['lastname']) && $formSubmitted && !isset($_SESSION['user_id'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= $errors['lastname'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="<?= (isset($errors['name']) && $formSubmitted) ? 'has-error' : '' ?>">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($userData['name']) : htmlspecialchars($_POST['name'] ?? '') ?>"
                        <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>
                        class="w-full px-3 py-2 border <?= (isset($errors['name']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Jean"
                        required
                    >
                    <?php if (isset($errors['name']) && $formSubmitted && !isset($_SESSION['user_id'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= $errors['name'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-6 <?= (isset($errors['email']) && $formSubmitted) ? 'has-error' : '' ?>">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($userData['email']) : htmlspecialchars($_POST['email'] ?? '') ?>"
                    <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>
                    class="w-full px-3 py-2 border <?= (isset($errors['email']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="jean.dupont@example.com"
                    required
                >
                <?php if (isset($errors['email']) && $formSubmitted && !isset($_SESSION['user_id'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= $errors['email'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Date et Heure -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="<?= (isset($errors['date_reservation']) && $formSubmitted) ? 'has-error' : '' ?>">
                    <label for="date_reservation" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input
                        type="date"
                        id="date_reservation"
                        name="date_reservation"
                        min="<?= $minDate ?>"
                        max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                        value="<?= htmlspecialchars($_POST['date_reservation'] ?? '') ?>"
                        class="w-full px-3 py-2 border <?= (isset($errors['date_reservation']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required
                    >
                    <?php if (isset($errors['date_reservation']) && $formSubmitted): ?>
                        <p class="mt-1 text-xs text-red-600"><?= $errors['date_reservation'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="<?= (isset($errors['heure']) && $formSubmitted) ? 'has-error' : '' ?>">
                    <label for="heure" class="block text-sm font-medium text-gray-700 mb-2">Heure *</label>
                    <select
                        id="heure"
                        name="heure"
                        class="w-full px-3 py-2 border <?= (isset($errors['heure']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required
                    >
                        <option value="">Sélectionnez une heure</option>
                        <!-- Créneaux du midi -->
                        <?php for ($h = 11; $h < 14; $h++): ?>
                            <?php for ($m = 0; $m < 60; $m += 30): ?>
                                <option value="<?= sprintf('%02d:%02d:00', $h, $m) ?>"
                                    <?= (isset($_POST['heure']) && $_POST['heure'] === sprintf('%02d:%02d:00', $h, $m)) ? 'selected' : '' ?>>
                                    <?= sprintf('%02d:%02d', $h, $m) ?>
                                </option>
                            <?php endfor; ?>
                        <?php endfor; ?>

                        <option disabled>———</option>

                        <!-- Créneaux du soir -->
                        <?php for ($h = 19; $h < 22; $h++): ?>
                            <?php for ($m = 0; $m < 60; $m += 30): ?>
                                <option value="<?= sprintf('%02d:%02d:00', $h, $m) ?>"
                                    <?= (isset($_POST['heure']) && $_POST['heure'] === sprintf('%02d:%02d:00', $h, $m)) ? 'selected' : '' ?>>
                                    <?= sprintf('%02d:%02d', $h, $m) ?>
                                </option>
                            <?php endfor; ?>
                        <?php endfor; ?>
                    </select>
                    <?php if (isset($errors['heure']) && $formSubmitted): ?>
                        <p class="mt-1 text-xs text-red-600"><?= $errors['heure'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nombre de personnes -->
            <div class="mb-6 <?= (isset($errors['nb_personnes']) && $formSubmitted) ? 'has-error' : '' ?>">
                <label for="nb_personnes" class="block text-sm font-medium text-gray-700 mb-2">Nombre de personnes *</label>
                <input
                    type="number"
                    id="nb_personnes"
                    name="nb_personnes"
                    min="1"
                    max="12"
                    value="<?= htmlspecialchars($_POST['nb_personnes'] ?? '2') ?>"
                    class="w-full px-3 py-2 border <?= (isset($errors['nb_personnes']) && $formSubmitted) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                >
                <?php if (isset($errors['nb_personnes']) && $formSubmitted): ?>
                    <p class="mt-1 text-xs text-red-600"><?= $errors['nb_personnes'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Message optionnel -->
            <div class="mb-6">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message (optionnel)</label>
                <textarea
                    id="message"
                    name="message"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Allergies, préférences, etc."
                ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Bouton de soumission -->
            <div class="pt-4">
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg transition-colors duration-200 font-medium"
                >
                    Confirmer la réservation
                </button>
            </div>
        </form>

        <div class="text-center mt-6 text-gray-600">
            <p>Vous avez déjà une réservation ? <a href="mes-reservations/" class="text-indigo-600 hover:text-indigo-700 font-medium">Voir mes réservations</a></p>
        </div>
    </div>

    <footer class="bg-white mt-auto py-6 text-center text-gray-500 text-sm">
        <p>&copy; <?= date('Y') ?> Le Gourmet Connecté — Restaurant gastonomique moderne</p>
    </footer>

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