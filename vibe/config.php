<?php
// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'vibe');
define('DB_USER', 'root');
define('DB_PASS', '');

// Fonction pour établir la connexion à la base de données
function getDBConnection() {
    try {
        $connexion = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connexion;
    } catch(PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
?> 