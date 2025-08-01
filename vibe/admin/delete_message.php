<?php
require_once 'config_admin.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: messages.php');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=vibe', 'root', '');

try {
    // Récupérer les informations du message pour supprimer le fichier joint s'il existe
    $stmt = $db->prepare("SELECT file_path FROM messages WHERE msg_id = ?");
    $stmt->execute([$_GET['id']]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    // Supprimer le fichier joint s'il existe
    if ($message && $message['file_path']) {
        $file_path = '../' . $message['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Supprimer le message de la base de données
    $stmt = $db->prepare("DELETE FROM messages WHERE msg_id = ?");
    $stmt->execute([$_GET['id']]);

    // Rediriger vers la page des messages avec un message de succès
    header('Location: messages.php?success=1');
    exit();
} catch (Exception $e) {
    // En cas d'erreur, rediriger vers la page des messages avec un message d'erreur
    header('Location: messages.php?error=1');
    exit();
} 