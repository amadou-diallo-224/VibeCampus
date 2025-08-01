<?php
try {
    $connexion = new PDO('mysql:host=127.0.0.1;dbname=vibe','root','');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lire le contenu du fichier SQL
    $sql = file_get_contents('events_table.sql');

    // Exécuter le script SQL
    $connexion->exec($sql);

    echo "Table 'evenements' créée avec succès et données de test insérées!";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 