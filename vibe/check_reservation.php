<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

if (!isset($_GET['matricule']) || !isset($_GET['event_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants'
    ]);
    exit;
}

$matricule = htmlspecialchars($_GET['matricule']);
$event_id = intval($_GET['event_id']);

// Vérifier si l'utilisateur existe
$user_stmt = $connexion->prepare('SELECT id, prenom_nom FROM utilisateur WHERE matricule = ?');
$user_stmt->execute([$matricule]);
$user = $user_stmt->fetch();

if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non trouvé'
    ]);
    exit;
}

// Vérifier la réservation
$reservation_stmt = $connexion->prepare('SELECT * FROM reservations WHERE evenement_id = ? AND utilisateur_id = ?');
$reservation_stmt->execute([$event_id, $user['id']]);
$reservation = $reservation_stmt->fetch();

if (!$reservation) {
    echo json_encode([
        'success' => false,
        'message' => 'Aucune réservation trouvée pour cet événement'
    ]);
    exit;
}

// Si tout est OK
echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'prenom_nom' => $user['prenom_nom']
    ],
    'reservation' => [
        'id' => $reservation['id'],
        'statut' => $reservation['statut']
    ]
]);
