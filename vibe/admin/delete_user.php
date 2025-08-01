<?php
require_once '../config.php';
require_once 'config_admin.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$connexion = getDBConnection();
$db = $connexion;
$stmt = $db->prepare("DELETE FROM utilisateur WHERE id = ?");
$stmt->execute([$_GET['id']]);

header('Location: users.php');
exit();