<?php
require_once '../config.php';
require_once 'config_admin.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$connexion = getDBConnection();
$db = $connexion;

try {
    // Récupérer l'image de l'événement avant de le supprimer
    $stmt = $db->prepare("SELECT image FROM evenements WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Supprimer l'événement
    $stmt = $db->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    // Si l'événement avait une image personnalisée, la supprimer
    if ($event && $event['image'] !== 'default_event.jpg') {
        $image_path = '../upload_images/' . $event['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header('Location: events.php?success=1');
} catch (PDOException $e) {
    header('Location: events.php?error=1');
}
exit(); 