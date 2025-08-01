<?php
require_once 'config.php';

try {
    $connexion = getDBConnection();

    // Supprimer la table si elle existe déjà
    $connexion->exec('DROP TABLE IF EXISTS evenements');

    // Créer la table avec la nouvelle structure
    $connexion->exec('
    CREATE TABLE evenements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        createur_id INT NOT NULL,
        titre VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        date_evenement DATETIME NOT NULL,
        lieu VARCHAR(100) NOT NULL,
        type_evenement ENUM("soiree", "formation", "autre") NOT NULL,
        image VARCHAR(255) DEFAULT "default_event.jpg",
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ');

    echo "Table 'evenements' créée avec succès!";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 