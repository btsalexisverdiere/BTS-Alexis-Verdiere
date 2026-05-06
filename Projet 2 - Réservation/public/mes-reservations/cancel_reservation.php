<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: mes_reservations.php');
    exit();
}

$resId = (int)$_GET['id'];

// S'assurer que la réservation appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT user_id FROM reservations WHERE id = :id");
$stmt->execute([':id' => $resId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Réservation introuvable.');
}

if ($row['user_id'] != $_SESSION['user_id']) {
    die('Action non autorisée.');
}

// Supprimer
$del = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
$del->execute([':id' => $resId]);

header('Location: /');
exit();
