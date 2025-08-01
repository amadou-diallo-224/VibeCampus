<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['id'])) {
    header('location: connexion.php');
    exit;
}

// Vérifier les paramètres
if(!isset($_GET['id']) || !isset($_GET['status'])) {
    header('location: home.php');
    exit;
}

$reservation_id = intval($_GET['id']);
$new_status = $_GET['status'];

// Vérifier si le statut est valide
if(!in_array($new_status, ['confirme', 'annule'])) {
    header('location: home.php');
    exit;
}

// Récupérer les informations de la réservation
$check_reservation = $connexion->prepare('SELECT e.createur_id FROM reservations r JOIN evenements e ON r.evenement_id = e.id WHERE r.id = ?');
$check_reservation->execute([$reservation_id]);
$creator = $check_reservation->fetch();

// Vérifier si l'utilisateur est le créateur de l'événement
if(!$creator || $creator['createur_id'] != $_SESSION['id']) {
    header('location: home.php');
    exit;
}

// Mettre à jour le statut de la réservation
$update = $connexion->prepare('UPDATE reservations SET statut = ? WHERE id = ?');
if($update->execute([$new_status, $reservation_id])) {
    $_SESSION['reservation_status_message'] = "Le statut de la réservation a été mis à jour avec succès.";
} else {
    $_SESSION['reservation_status_message'] = "Erreur lors de la mise à jour du statut.";
}

// Rediriger vers la page de gestion des réservations
header('location: manage_event_reservations.php?event_id=' . $check_reservation->fetchColumn());
exit;
