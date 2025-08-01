<?php

require_once 'config.php';

$connexion = getDBConnection();
$membres = $connexion->query('SELECT * FROM utilisateur ORDER BY id DESC');
 if(isset($_GET['prenom_nom']) /*AND !empty($_GET['recherche'])*/) {
    $autresUsers = ($_GET['prenom_nom']);
    $membres = $connexion->query('SELECT * FROM utilisateur WHERE * LIKE  $autresUsers ORDER BY id DESC');
 }
/*function getConversation($id, $connexion) {
    $sql = "SELECT * FROM conversations WHERE user_1=? OR user_2=? ORDER BY conversation_id DESC";

    $stmt = $connexion -> prepare($sql);
    $stmt -> execute([$id, $id]);

    if($stmt -> rowCount() > 0) {
        $conversations = $stmt -> fetchAll();
       
        $user_data = [];

        foreach($conversations as $conversation) {
            if($conversation['user_1'] == $id) {
                $sql2 = "SELECT prenom_nom, image, date
                FROM utilisateur WHERE id=?";
                $stmt2 = $connexion -> prepare($sql2);
                $stmt2 ->execute([$conversation['user_2']]);
            }else {
                $sql2 = "SELECT prenom_nom, image, date
                FROM utilisateur WHERE id=?";
                $stmt2 = $connexion -> prepare($sql2);
                $stmt2 ->execute([$conversation['user_1']]);
            }
            $allConversations = $stmt2 -> fetchAll();
            array_push($user_data, $allConversations[0]);
        }

        return $user_data;

    }else {
        $conversations = [];
        return $conversations;
    }
}*/

?>