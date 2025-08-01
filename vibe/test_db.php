<?php
require_once 'config.php';

try {
    $connexion = getDBConnection();
    echo "Connexion à la base de données réussie !";
    
    // Tester la connexion en exécutant une requête simple
    $result = $connexion->query('SELECT 1');
    if ($result) {
        echo "<br>La base de données est accessible et fonctionnelle.";
    }
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
