<?php

require_once('db.php');  // Inclure le fichier de connexion à la base de données

class MenuManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Fonction pour obtenir les détails d'un menu par ID
    public function getMenuDetails($id_menu) {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->bind_param("i", $id_menu);
        $stmt->execute();
        $result = $stmt->get_result();
        $menuDetails = $result->fetch_assoc();
        $stmt->close();

        return $menuDetails;
    }

    // Ajoutez d'autres fonctions pour la gestion des menus selon vos besoins
}

?>
