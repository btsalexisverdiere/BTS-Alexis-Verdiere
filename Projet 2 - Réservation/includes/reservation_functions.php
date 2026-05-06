<?php
// require_once __DIR__ . '/../config/session_config.php'; // si tu utilises session check
// require_once __DIR__ . '/../config/db.php';

function getUserReservations(PDO $pdo, int $userId): array {
    try {
        $stmt = $pdo->prepare("
            SELECT id, date_reservation, heure, nb_personnes, message, created_at
            FROM reservations
            WHERE user_id = :user_id
            ORDER BY date_reservation DESC, heure DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // log error en prod ; pour dev on peut renvoyer l'exception message
        return [];
    }
}

function getUpcomingReservationsForUser(PDO $pdo, int $userId): array {
    try {
        $stmt = $pdo->prepare("
            SELECT id, date_reservation, heure, nb_personnes, message, created_at
            FROM reservations
            WHERE user_id = :user_id
              AND (date_reservation > CURDATE() OR (date_reservation = CURDATE() AND heure >= CURTIME()))
            ORDER BY date_reservation ASC, heure ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getReservationsByDate(PDO $pdo, string $date): array {
    try {
        $stmt = $pdo->prepare("
            SELECT r.id, r.date_reservation, r.heure, r.nb_personnes, r.message, r.created_at,
                   u.id AS user_id, u.username, u.name, u.lastname, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.date_reservation = :date
            ORDER BY r.heure ASC
        ");
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getAllReservations(PDO $pdo, int $limit = 100, int $offset = 0): array {
    try {
        $stmt = $pdo->prepare("
            SELECT r.id, r.date_reservation, r.heure, r.nb_personnes, r.message, r.created_at,
                   u.id AS user_id, u.username, u.name, u.lastname, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.date_reservation DESC, r.heure DESC
            LIMIT :limit OFFSET :offset
        ");
        // bindValue with explicit type for LIMIT/OFFSET
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


function getTodayReservations(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("
            SELECT r.id, r.date_reservation, r.heure, r.nb_personnes, r.message, r.created_at,
                   u.id AS user_id, u.username, u.name, u.lastname, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.date_reservation = CURDATE()
            ORDER BY r.heure ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getNextReservations(PDO $pdo): array{
    try{
        $stmt = $pdo->prepare("SELECT r.id, r.date_reservation, r.heure, r.nb_personnes, r.message, r.created_at,
                   u.id AS user_id, u.username, u.name, u.lastname, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.date_reservation > CURDATE() OR (date_reservation = CURDATE() AND heure >= CURTIME())
            ORDER BY r.date_reservation DESC, r.heure DESC ");
        $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}



function countReservations(PDO $pdo, string $search = '', string $date = ''): int {
    $sql = "SELECT COUNT(*) FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (u.username LIKE :q OR u.name LIKE :q OR u.lastname LIKE :q OR u.email LIKE :q)";
        $params[':q'] = "%$search%";
    }

    if ($date !== '') {
        $sql .= " AND r.date_reservation = :date";
        $params[':date'] = $date;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getReservationsFiltered(PDO $pdo, string $search, string $date, int $limit, int $offset): array {
    $sql = "SELECT r.*, u.username, u.name, u.lastname, u.email
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (u.username LIKE :q OR u.name LIKE :q OR u.lastname LIKE :q OR u.email LIKE :q)";
        $params[':q'] = "%$search%";
    }

    if ($date !== '') {
        $sql .= " AND r.date_reservation = :date";
        $params[':date'] = $date;
    }

    $sql .= " ORDER BY r.date_reservation DESC, r.heure DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
