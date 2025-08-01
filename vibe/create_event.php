<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

if(!isset($_SESSION['prenom_nom'])) {
  header('location: connexion.php');
  exit;
}

if(isset($_POST['titre'])) {
  $errors = [];
  
  // Validation des données
  if(empty($_POST['titre'])) {
    $errors[] = "Le titre est requis";
  }
  if(empty($_POST['description'])) {
    $errors[] = "La description est requise";
  }
  if(empty($_POST['date_evenement'])) {
    $errors[] = "La date est requise";
  }
  if(empty($_POST['lieu'])) {
    $errors[] = "Le lieu est requis";
  }
  if(empty($_POST['type_evenement'])) {
    $errors[] = "Le type d'événement est requis";
  }
  if(empty($_POST['contact_info'])) {
    $errors[] = "Les informations de contact sont requises";
  }

  // Validation de l'URL si c'est un lien
  if($_POST['contact_type'] === 'link') {
    $url = trim($_POST['contact_info']);
    // Vérifier si c'est une URL valide
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
      $errors[] = "L'URL de réservation n'est pas valide. Veuillez entrer une URL complète (par exemple : https://exemple.com)";
    }
    // Si l'URL n'a pas de protocole, on l'ajoute
    if (!preg_match('#^https?://#i', $url)) {
      $url = 'http://' . $url;
    }
    $_POST['contact_info'] = $url;
  }

  // Traitement de l'image
  $image_name = 'default_event.jpg';
  if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if(in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
      $upload_dir = 'upload_images/';
      if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }
      $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
      $image_name = uniqid() . '.' . $file_extension;
      $upload_path = $upload_dir . $image_name;

      if(!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $errors[] = "Erreur lors du téléchargement de l'image";
      }
    } else {
      $errors[] = "Format d'image non supporté ou fichier trop volumineux (max 5MB)";
    }
  }

  if(empty($errors)) {
    // Formater le contact_info
    $contact_info = $_POST['contact_type'] . ':' . $_POST['contact_info'];
    
    // Insertion de l'événement
    $insert = $connexion->prepare('
      INSERT INTO evenements (createur_id, titre, description, date_evenement, lieu, type_evenement, contact_info, image)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $insert->execute([
      $_SESSION['id'],
      $_POST['titre'],
      $_POST['description'],
      $_POST['date_evenement'],
      $_POST['lieu'],
      $_POST['type_evenement'],
      $contact_info,
      $image_name
    ]);
    
    header('location: home.php?event_created=1');
    exit;
  }
}

header('location: home.php');
exit;
?> 