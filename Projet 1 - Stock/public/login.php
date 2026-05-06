<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

// 🔁 Redirection après connexion
$redirect = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(htmlspecialchars($_POST['email']));
    $password = $_POST['password'];

    // 🧩 Vérifications basiques
    if (empty($email) || empty($password)) {
        $errors[] = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse e-mail est invalide.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Connexion réussie
            session_regenerate_id(true); // Sécurisation de la session

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['session_key'] = bin2hex(random_bytes(32)); // jeton unique

            header("Location: $redirect");
            exit();
        } else {
            $errors[] = "Adresse e-mail ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion | Le Gourmet Connecté</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

<?php include __DIR__ . '/../includes/header.php'; ?>


<section class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-semibold text-center text-indigo-600 mb-6">Connexion</h2>

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
      <input 
        type="email" 
        name="email" 
        placeholder="Adresse e-mail" 
        required 
        class="border border-gray-300 rounded-lg p-2 w-full"
        autofocus
        value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
      >
      <input 
        type="password" 
        name="password" 
        placeholder="Mot de passe" 
        required 
        class="border border-gray-300 rounded-lg p-2 w-full"
      >

      <button 
        type="submit" 
        class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-2 w-full transition"
      >
        Se connecter
      </button>

      <p class="text-sm text-center mt-2 text-gray-600">
        Pas encore de compte ?
        <a href="register.php" class="text-indigo-500 hover:underline">S'inscrire</a>
      </p>
    </form>
  </div>
</section>

</body>
</html>
