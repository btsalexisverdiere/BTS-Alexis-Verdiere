<?php
session_start();
include 'connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user"])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le gagnant de la partie
    $winner = isset($_POST['winner']) ? $_POST['winner'] : '';

    // Vérifier si le gagnant est défini
    if (!empty($winner)) {
        $fk_id_user = $_SESSION["user"]["id"];

        try {
            // Mettre à jour le nombre de victoires pour le gagnant
            $sql = "UPDATE partie_Morpion SET nb_victoire = nb_victoire + 1 WHERE fk_id_user = :fk_id_user AND couleur = :couleur";
            $stmt = $bdd->prepare($sql);
            $stmt->execute([':fk_id_user' => $fk_id_user, ':couleur' => $winner]);

            // Vérifier si aucune ligne n'a été affectée par la mise à jour
            if ($stmt->rowCount() == 0) {
                // Si aucune ligne n'a été affectée, insérer une nouvelle partie
                $sql = "INSERT INTO partie_Morpion (fk_id_user, couleur, nb_victoire, date_partie) VALUES (:fk_id_user, :couleur, 1, NOW())";
                $stmt = $bdd->prepare($sql);
                $stmt->execute([':fk_id_user' => $fk_id_user, ':couleur' => $winner]);
            }
        } catch(PDOException $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }
}

if (isset($_SESSION["user"])) {
    $fk_id_user = $_SESSION["user"]["id"];
    // Récupérer les scores vert et rouge de l'utilisateur connecté
    $sql = "SELECT SUM(nb_victoire) as total_victoire, couleur FROM partie WHERE fk_id_user = :fk_id_user GROUP BY couleur";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':fk_id_user' => $_SESSION["user"]["id"]]);
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Définir les variables pour les scores vert et rouge
    $scoreGreen = 0;
    $scoreRed = 0;

    // Parcourir les résultats et affecter les scores aux variables correspondantes
    foreach ($scores as $score) {
        if ($score['couleur'] == 'green') {
            $scoreGreen = $score['total_victoire'];
        } elseif ($score['couleur'] == 'red') {
            $scoreRed = $score['total_victoire'];
        }
    }
} else {
    // Si l'utilisateur n'est pas connecté, définir les scores vert et rouge à 0
    $scoreGreen = 0;
    $scoreRed = 0;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu de Morpion</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        table {
            border-collapse: collapse;
            width: 300px;
            height: 300px;
        }
        td {
            border: 1px solid black;
            width: 100px;
            height: 100px;
        }
        button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Jeu de Morpion</h1>
    <table id="tableau">
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <p>Score vert : <span id="scoreGreen"><?= $scoreGreen ?></span></p>
    <p>Score rouge : <span id="scoreRed"><?= $scoreRed ?></span></p>
    <button id="nouvellePartie">Nouvelle partie</button>
    <script src="script.js"></script>

    <button onclick="window.location.href = 'deconnexion.php'">Se déconnecter</button>
</body>
</html>
