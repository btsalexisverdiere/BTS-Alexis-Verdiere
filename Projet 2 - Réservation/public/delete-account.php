<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /public/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['confirm_password'] ?? '';

    // Vérifier le mot de passe
    $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Supprimer les réservations puis l'utilisateur
        $pdo->prepare("DELETE FROM reservations WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

        session_destroy();
        header('Location: /public/login.php?deleted=1');
        exit;
    } else {
        $_SESSION['error'] = "Mot de passe incorrect. Suppression annulée.";
        header('Location: edit-account.php');
        exit;
    }
}

header('Location: edit-account.php');
exit;