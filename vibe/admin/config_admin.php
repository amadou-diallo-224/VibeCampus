<?php
session_start();

// Fonction pour vérifier si l'utilisateur est connecté en tant qu'admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Fonction pour rediriger vers la page de connexion si non connecté
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Fonction pour vérifier les identifiants de connexion
function verifyAdminCredentials($username, $password) {
    require_once '../config.php';
    $connexion = getDBConnection();
    
    $stmt = $connexion->prepare('SELECT * FROM admin WHERE username = ? AND password = ?');
    $stmt->execute([$username, $password]);
    $admin = $stmt->fetch();
    
    return $admin !== false;
}

// Fonction pour se connecter
function adminLogin($username, $password) {
    if (verifyAdminCredentials($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

// Fonction pour se déconnecter
function adminLogout() {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    session_destroy();
} 