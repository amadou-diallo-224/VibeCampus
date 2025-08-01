<?php

function getUser($prenom_nom, $connexion) {
    $sql = "SELECT * FROM utilisateur WHERE prenom_nom=?";
    $stmt = $connexion -> prepare ($sql);
    $stmt -> execute([$prenom_nom]);

    if ($stmt -> rowCount() === 1 ) {
        $user = $stmt->fetch();
        return $user;
    }else {
        $user = [];
        return $user;
    }
}

?>