<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = trim(htmlspecialchars($_POST['lastname']));
    $name = trim(htmlspecialchars($_POST['name']));
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $role = htmlspecialchars($_POST['role'] ?? 'user');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (strlen($lastname) < 2 || strlen($name) < 2) {
        $errors[] = "Le nom et le prénom doivent contenir au moins 2 caractères.";
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse e-mail est invalide.";
    }

    if (strlen($password) < 10 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au minimum 10 caractères, avec au moins une majuscule, une minuscule, un chiffre et un symbole.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les deux mots de passe ne correspondent pas.";
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        $errors[] = "L'adresse e-mail ou le nom d'utilisateur est déjà utilisé.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, name, lastname, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $name, $lastname, $email, $hashedPassword,]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $username;
        $_SESSION['session_key'] = bin2hex(random_bytes(32));

        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription | Le Gourmet Connecté</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-semibold text-center text-indigo-600 mb-6">Créer un compte</h2>

    <?php if (!empty($errors)): ?>
      <div class="bg-red-50 border border-red-300 text-red-600 rounded-lg p-4 mb-4">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <input type="text" name="lastname" placeholder="Nom" required class="border border-gray-300 rounded-lg p-2 w-full" autofocus value="<?= isset($lastname) ? htmlspecialchars($lastname) : '' ?>">
        <input type="text" name="name" placeholder="Prénom" required class="border border-gray-300 rounded-lg p-2 w-full" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
      </div>

      <input type="text" name="username" placeholder="Nom d'utilisateur" required class="border border-gray-300 rounded-lg p-2 w-full" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
      <input type="email" name="email" placeholder="Adresse e-mail" required class="border border-gray-300 rounded-lg p-2 w-full" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
      
      <input type="password" name="password" placeholder="Mot de passe" required class="border border-gray-300 rounded-lg p-2 w-full">
      <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe" required class="border border-gray-300 rounded-lg p-2 w-full">

      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-2 w-full transition">
        S'inscrire
      </button>

      <p class="text-sm text-center mt-2 text-gray-600">
        Déjà un compte ? 
        <a href="login.php" class="text-indigo-500 hover:underline">Se connecter</a>
      </p>
    </form>
  </div>
</section>

</body>
</html>
