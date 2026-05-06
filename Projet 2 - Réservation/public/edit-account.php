<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /public/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$success = '';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Modification des infos ────────────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'update_info') {
        $name     = htmlspecialchars(trim($_POST['name'] ?? ''));
        $lastname = htmlspecialchars(trim($_POST['lastname'] ?? ''));
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

        if (empty($name))     $errors['name']     = "Le prénom est obligatoire.";
        if (empty($lastname)) $errors['lastname']  = "Le nom est obligatoire.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email invalide.";

        // Vérifier si l'email est déjà pris par un autre utilisateur
        if (empty($errors['email'])) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $userId]);
            if ($check->fetch()) $errors['email'] = "Cet email est déjà utilisé.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, lastname = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $lastname, $email, $userId]);
            $success = "✅ Vos informations ont été mises à jour.";
            // Rafraîchir les données
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // ── Modification du mot de passe ─────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['mot_de_passe'])) {
            $errors['current_password'] = "Mot de passe actuel incorrect.";
        }
        if (strlen($new) < 8) {
            $errors['new_password'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }
        if ($new !== $confirm) {
            $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
        }

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            $success = "✅ Mot de passe mis à jour avec succès.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte — Le Gourmet Connecté</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="bg-gradient-to-r from-indigo-700 to-purple-700 text-white py-12 px-4">
    <div class="container mx-auto max-w-2xl">
        <a href="dashboard.php" class="text-indigo-200 text-sm hover:text-white transition">← Retour au dashboard</a>
        <h1 class="text-3xl font-extrabold mt-2">Mon compte</h1>
        <p class="text-indigo-200 mt-1">Gérez vos informations personnelles</p>
    </div>
</section>

<main class="container mx-auto max-w-2xl px-4 py-10 space-y-6">

    <!-- Message succès/erreur -->
    <?php if ($success): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-green-700 font-medium"><?= $success ?></p>
    </div>
    <?php endif; ?>

    <!-- Infos personnelles -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-bold mb-5 text-gray-800">Informations personnelles</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_info">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" name="name"
                           value="<?= htmlspecialchars($user['name']) ?>"
                           class="w-full px-3 py-2 border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['name'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= $errors['name'] ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="lastname"
                           value="<?= htmlspecialchars($user['lastname']) ?>"
                           class="w-full px-3 py-2 border <?= isset($errors['lastname']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php if (isset($errors['lastname'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= $errors['lastname'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       class="w-full px-3 py-2 border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php if (isset($errors['email'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= $errors['email'] ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Membre depuis</label>
                <input type="text" value="<?= date('d/m/Y', strtotime($user['created_at'])) ?>" disabled
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Sauvegarder les modifications
            </button>
        </form>
    </div>

    <!-- Mot de passe -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-bold mb-5 text-gray-800">Changer le mot de passe</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_password">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                <input type="password" name="current_password"
                       class="w-full px-3 py-2 border <?= isset($errors['current_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php if (isset($errors['current_password'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= $errors['current_password'] ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                <input type="password" name="new_password"
                       class="w-full px-3 py-2 border <?= isset($errors['new_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php if (isset($errors['new_password'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= $errors['new_password'] ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1">Minimum 8 caractères</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password"
                       class="w-full px-3 py-2 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php if (isset($errors['confirm_password'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= $errors['confirm_password'] ?></p>
                <?php endif; ?>
            </div>
            <button type="submit"
                    class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold hover:bg-gray-900 transition">
                Mettre à jour le mot de passe
            </button>
        </form>
    </div>

    <!-- Supprimer le compte -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-red-100">
        <h2 class="text-lg font-bold mb-2 text-red-600">Zone dangereuse</h2>
        <p class="text-sm text-gray-500 mb-4">La suppression de votre compte est irréversible. Toutes vos réservations seront également supprimées.</p>
        <button onclick="document.getElementById('modal-delete').classList.remove('hidden')"
                class="bg-red-50 text-red-600 border border-red-200 px-5 py-2 rounded-lg font-semibold hover:bg-red-100 transition text-sm">
            Supprimer mon compte
        </button>
    </div>

</main>

<!-- Modal suppression compte -->
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">⚠️</div>
            <h3 class="text-xl font-bold text-gray-800">Supprimer mon compte</h3>
            <p class="text-gray-500 text-sm mt-2">Cette action est irréversible. Toutes vos données seront supprimées.</p>
        </div>
        <form method="POST" action="delete-account.php">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmez votre mot de passe</label>
                <input type="password" name="confirm_password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="flex gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-delete').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                    Supprimer définitivement
                </button>
            </div>
        </form>
    </div>
</div>

<footer class="bg-white py-6 text-center text-gray-400 text-sm mt-4 border-t">
    <p>&copy; <?= date('Y') ?> Le Gourmet Connecté</p>
</footer>

<script>
    document.getElementById('modal-delete').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
</body>
</html>