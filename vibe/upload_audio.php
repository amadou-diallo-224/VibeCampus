<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

if(!isset($_SESSION['prenom_nom'])) {
  header('location: connexion.php');
  exit;
}

if(isset($_POST['btn_msg']) && isset($_FILES['audio'])) {
  $audio = $_FILES['audio'];
  $getid = $_GET['user_id'];
  $session_id = $_SESSION['id'];

  // Vérifier si c'est bien un fichier audio
  if($audio['type'] === 'audio/wav') {
    $audio_name = time() . '_' . $audio['name'];
    $audio_path = 'upload_chat/' . $audio_name;

    // Créer le dossier s'il n'existe pas
    if(!is_dir('upload_chat')) {
      mkdir('upload_chat', 0777, true);
    }

    // Déplacer le fichier
    if(move_uploaded_file($audio['tmp_name'], $audio_path)) {
      // Insérer le message dans la base de données
      $inserermessage = $connexion->prepare('INSERT INTO messages(id_destinataire, id_auteur, msg, file_path, file_type) 
                                     VALUES (?,?,?,?,?)');
      $inserermessage->execute(array($getid, $session_id, 'Message vocal', $audio_path, 'audio/wav'));
    }
  }
}

header('location: chat.php?user_id=' . $getid);
exit;
?> 