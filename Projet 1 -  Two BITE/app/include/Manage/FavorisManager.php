<?php
class FavorisManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function ajouterFavori($id_user, $id_menu)
    {
        // Assurez-vous que l'id_user et l'id_menu sont valides
        if ($id_user <= 0 || $id_menu <= 0) {
            return false;
        }
    
        // Ajouter le favori
        $query = "INSERT INTO favoris_" . $id_user . " (`id_menu`) VALUES (?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id_menu);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    

    public function supprimerFavori($id_user, $id_menu)
    {
        $query = "DELETE FROM favoris_" . $id_user . " WHERE (`id_menu`) = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id_menu);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getFavoris($id_user) {
        $query = "SELECT id_menu FROM favoris_" . $id_user;
        $result = $this->db->query($query);
    
        $favoris = [];
        while ($row = $result->fetch_assoc()) {
            $favoris[] = $row['id_menu'];
        }
    
        return $favoris;
    }
    
    // Vous pouvez ajouter d'autres méthodes selon vos besoins
}
?>
