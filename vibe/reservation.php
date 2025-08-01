<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

if(isset($_GET['event_id']) && isset($_SESSION['id'])) {
    $event_id = intval($_GET['event_id']);
    $user_id = $_SESSION['id'];

    // Vérifier si l'utilisateur n'a pas déjà réservé cet événement
    $check_reservation = $connexion->prepare('SELECT * FROM reservations WHERE evenement_id = ? AND utilisateur_id = ?');
    $check_reservation->execute([$event_id, $user_id]);

    if($check_reservation->rowCount() > 0) {
        $reservation = $check_reservation->fetch();
        if($reservation['statut'] === 'en_attente') {
            $_SESSION['reservation_message'] = "Votre réservation est déjà en attente pour cet événement.";
        } else {
            $_SESSION['reservation_message'] = "Vous avez déjà réservé cet événement.";
        }
    } else {
        // Insérer la nouvelle réservation
        $insert_reservation = $connexion->prepare('INSERT INTO reservations (evenement_id, utilisateur_id) VALUES (?, ?)');
        if($insert_reservation->execute([$event_id, $user_id])) {
            $_SESSION['reservation_message'] = "Réservation effectuée avec succès !";
        } else {
            $_SESSION['reservation_message'] = "Erreur lors de la réservation.";
        }
    }
}

header('location: home.php');
exit;
