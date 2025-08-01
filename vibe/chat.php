<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();
if(!isset($_SESSION['prenom_nom'])) {
  header('location: connexion.php');
}

if(isset($_GET['user_id']) AND !empty($_GET['user_id'])) {
  $getid = $_GET['user_id'];
  $session_id = $_SESSION['id'];
  
  // Récupérer les informations de l'utilisateur avec qui on discute
  $recupUser = $connexion->prepare('SELECT * FROM utilisateur WHERE id = ?');
  $recupUser->execute(array($getid));
  
  if($recupUser->rowCount() > 0) {
    $userInfo = $recupUser->fetch();
    
    if(isset($_POST['btn_msg'])) {
      $message = $_POST['msg'];
      
      // Vérifier si c'est un message vocal
      if(isset($_POST['is_voice']) && $_POST['is_voice'] == '1') {
        $message = ''; // Ne pas envoyer le message texte pour un message vocal
      }
      
      // Gérer l'envoi de fichiers
      $file_path = '';
      $file_type = null;
      if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'audio/mpeg', 'audio/wav'];
        if(in_array($_FILES['file']['type'], $allowed)) {
          $file_name = time() . '_' . $_FILES['file']['name'];
          $file_path = 'upload_chat/' . $file_name;
          $file_type = $_FILES['file']['type'];
          if(!is_dir('upload_chat')) {
            mkdir('upload_chat', 0777, true);
          }
          move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
        }
      }
      
      // Vérifier le nombre total de messages
      $countMessages = $connexion->prepare('SELECT COUNT(*) as total FROM messages WHERE (id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?)');
      $countMessages->execute([$session_id, $getid, $getid, $session_id]);
      $totalMessages = $countMessages->fetch()['total'];

      // Si on atteint ou dépasse 50 messages, supprimer les plus anciens
      if($totalMessages >= 50) {
        // Récupérer les fichiers des messages à supprimer
        $getOldFiles = $connexion->prepare('
          SELECT file_path 
          FROM messages 
          WHERE ((id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?))
          AND date < (
            SELECT date FROM (
              SELECT date FROM messages 
              WHERE (id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?)
              ORDER BY date DESC 
              LIMIT 49, 1
            ) AS temp
          )
          AND file_path IS NOT NULL
        ');
        $getOldFiles->execute([$session_id, $getid, $getid, $session_id, $session_id, $getid, $getid, $session_id]);
        
        // Supprimer les fichiers physiques
        while($file = $getOldFiles->fetch()) {
          if(file_exists($file['file_path'])) {
            unlink($file['file_path']);
          }
        }

        // Supprimer les messages de la base de données
        $deleteOldMessages = $connexion->prepare('
          DELETE FROM messages 
          WHERE ((id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?))
          AND date < (
            SELECT date FROM (
              SELECT date FROM messages 
              WHERE (id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?)
              ORDER BY date DESC 
              LIMIT 49, 1
            ) AS temp
          )
        ');
        $deleteOldMessages->execute([$session_id, $getid, $getid, $session_id, $session_id, $getid, $getid, $session_id]);

        // Optimiser la table après suppression
        $connexion->query('OPTIMIZE TABLE messages');
      }
      
      $inserermessage = $connexion->prepare('INSERT INTO messages(id_destinataire, id_auteur, msg, file_path, file_type) 
                                     VALUES (?,?,?,?,?)');
      $inserermessage->execute(array($getid, $session_id, $message, $file_path, $file_type));
    }

    // Marquer les messages comme lus
    $mark_read = $connexion->prepare('UPDATE messages SET is_read = TRUE WHERE id_auteur = ? AND id_destinataire = ?');
    $mark_read->execute([$getid, $session_id]);
  } else {
    $user_not_found = "Aucun utilisateur trouvé";
  }
} else {
  $id_not_found = "Aucun identifiant trouvé";
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messagerie - Vibe</title>
    <link rel="stylesheet" href="style.css" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="icon" href="assets/images/vibe.png">
    <style>
      :root {
        --vibe-blue: #007bff;
        --vibe-light: #f8f9fa;
        --vibe-dark: #343a40;
      }
      
      body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
      }
      
      .chat-container {
        max-width: 800px;
        margin: 20px auto;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        background: white;
        overflow: hidden;
      }
      
      .chat-header {
        background: var(--vibe-blue);
        color: white;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
      }
      
      .chat-header img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid white;
        object-fit: cover;
      }
      
      .user-info h4 {
        margin: 0;
        font-size: 1.2rem;
      }
      
      .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
      }
      
      .status-online {
        background-color: #28a745;
      }
      
      .status-offline {
        background-color: #dc3545;
      }
      
      .chat-box {
        height: 500px;
        overflow-y: auto;
        padding: 20px;
        background: var(--vibe-light);
      }
      
      .message {
        max-width: 70%;
        margin-bottom: 15px;
        padding: 10px 15px;
        border-radius: 15px;
        position: relative;
      }
      
      .message.sent {
        background: var(--vibe-blue);
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 5px;
      }
      
      .message.received {
        background: white;
        color: var(--vibe-dark);
        margin-right: auto;
        border-bottom-left-radius: 5px;
      }
      
      .message-time {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 5px;
      }
      
      .message-file {
        margin-top: 10px;
        max-width: 100%;
      }
      
      .message-file img {
        max-width: 100%;
        border-radius: 10px;
      }
      
      .message-file audio {
        width: 100%;
      }
      
      .chat-input {
        padding: 15px;
        background: white;
        border-top: 1px solid #dee2e6;
      }
      
      .chat-input textarea {
        resize: none;
        border-radius: 20px;
        padding: 10px 15px;
      }
      
      .chat-input .btn {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .file-input {
        display: none;
      }
      
      .no-messages {
        text-align: center;
        padding: 40px;
      }
      
      .no-messages img {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 20px;
      }
      
      .no-messages h5 {
        color: var(--vibe-dark);
        font-style: italic;
      }
    </style>
  </head>
  <body>
    <div class="chat-container">
      <header class="chat-header">
        <a href="home.php" class="text-white">
          <i class="fa-solid fa-arrow-left"></i>
        </a>
        <img src="upload_images/<?= $userInfo['image'] ?? 'Profile-PNG-Photo.png' ?>" alt="Profile">
        <div class="user-info">
          <h4><?= $userInfo['prenom_nom'] ?? 'Utilisateur' ?></h4>
          <span>
            <span class="status-indicator status-<?= $userInfo['statut'] === 'en ligne' ? 'online' : 'offline' ?>"></span>
            <?= $userInfo['statut'] ?? 'hors ligne' ?>
          </span>
        </div>
      </header>

      <?php if(isset($id_not_found) || isset($user_not_found)) { ?>
        <div class="alert alert-danger m-3" role="alert">
          <?= $id_not_found ?? $user_not_found ?>
          </div>
      <?php } ?>

      <div class="chat-box">
        <?php
        if(isset($userInfo)) {
          $recupMessages = $connexion->prepare('
            SELECT m.*, u.prenom_nom, u.image 
            FROM messages m 
            JOIN utilisateur u ON m.id_auteur = u.id 
            WHERE (m.id_auteur = ? AND m.id_destinataire = ?) 
            OR (m.id_auteur = ? AND m.id_destinataire = ?) 
            ORDER BY m.date ASC
          ');
          $recupMessages->execute([$_SESSION['id'], $getid, $getid, $_SESSION['id']]);
          
          if($recupMessages->rowCount() > 0) {
            while($message = $recupMessages->fetch()) {
              $is_sent = $message['id_auteur'] == $_SESSION['id'];
        ?>
              <div class="message <?= $is_sent ? 'sent' : 'received' ?>">
                <?= $message['msg'] ?>
                
                <?php if($message['file_path']) { ?>
                  <div class="message-file">
                    <?php if(strpos($message['file_type'], 'image/') === 0) { ?>
                      <img src="<?= $message['file_path'] ?>" alt="Image">
                    <?php } elseif(strpos($message['file_type'], 'audio/') === 0) { ?>
                      <audio controls>
                        <source src="<?= $message['file_path'] ?>" type="<?= $message['file_type'] ?>">
                      </audio>
                    <?php } ?>
                  </div>
                <?php } ?>
                
                <div class="message-time">
                  <?= date('d/m/Y H:i', strtotime($message['date'])) ?>
                  <?php if($is_sent && $message['is_read']) { ?>
                    <i class="fas fa-check-double text-white-50"></i>
            <?php } ?>
                </div>
              </div>
          <?php
            }
          } else { 
          ?>
          <div class="no-messages">
            <img src="upload_images/<?= $userInfo['image'] ?>" alt="Profile">
            <h5>Vous n'avez aucun message avec <?= $userInfo['prenom_nom'] ?></h5>
            <p class="text-muted">Envoyez un message pour commencer la conversation</p>
      </div>
      <?php
       }
       }
      ?>
      </div>

      <div class="chat-input">
        <form action="" method="post" enctype="multipart/form-data" class="d-flex gap-2">
          <input type="file" name="file" id="file" class="file-input" accept="image/*,audio/*">
          <label for="file" class="btn btn-outline-primary">
            <i class="fas fa-image"></i>
          </label>
          <button type="button" class="btn btn-outline-primary" id="emojiButton">
            <i class="far fa-smile"></i>
          </button>
          <textarea name="msg" class="form-control flex-grow-1" placeholder="Votre message..." rows="1"></textarea>
          <button type="button" class="btn btn-outline-primary" id="recordButton">
            <i class="fas fa-microphone"></i>
          </button>
          <button type="submit" name="btn_msg" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>
    <script>
      console.log('Script chargé');

      // Initialisation des variables globales
      let mediaRecorder = null;
      let audioChunks = [];
      let isRecording = false;
      let textarea = null;
      let emojiButton = null;
      let recordButton = null;
      let picker = null;

      document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM chargé');
        
        // Initialisation des éléments
        textarea = document.querySelector('textarea[name="msg"]');
        emojiButton = document.getElementById('emojiButton');
        recordButton = document.getElementById('recordButton');

        console.log('Éléments:', {
          textarea: !!textarea,
          emojiButton: !!emojiButton,
          recordButton: !!recordButton
        });

        if (!textarea || !emojiButton || !recordButton) {
          console.error('Un ou plusieurs éléments n\'ont pas été trouvés');
          return;
        }

        // Auto-resize textarea
        textarea.addEventListener('input', function() {
          this.style.height = 'auto';
          this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Scroll to bottom of chat
        const chatBox = document.querySelector('.chat-box');
        if (chatBox) {
          chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Initialisation du sélecteur d'émojis
        try {
          picker = new EmojiButton({
            position: 'top',
            theme: 'dark',
            autoHide: false
          });
          console.log('Sélecteur d\'émojis initialisé');
        } catch (error) {
          console.error('Erreur lors de l\'initialisation du sélecteur d\'émojis:', error);
        }

        // Gestion des émojis
        emojiButton.addEventListener('click', (e) => {
          console.log('Bouton émoji cliqué');
          e.preventDefault();
          if (picker) {
            picker.togglePicker(emojiButton);
          }
        });

        if (picker) {
          picker.on('emoji', emoji => {
            console.log('Émoji sélectionné:', emoji);
            if (textarea) {
              const cursorPosition = textarea.selectionStart;
              const textBefore = textarea.value.substring(0, cursorPosition);
              const textAfter = textarea.value.substring(cursorPosition);
              textarea.value = textBefore + emoji + textAfter;
              textarea.focus();
              textarea.selectionStart = cursorPosition + emoji.length;
              textarea.selectionEnd = cursorPosition + emoji.length;
            }
          });
        }

        // Gestion de l'enregistrement vocal
        recordButton.addEventListener('click', async (e) => {
          console.log('Bouton enregistrement cliqué');
          e.preventDefault();
          
          if (!isRecording) {
            try {
              console.log('Début de l\'enregistrement');
              const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
              mediaRecorder = new MediaRecorder(stream);
              audioChunks = [];

              mediaRecorder.ondataavailable = (event) => {
                console.log('Données audio reçues');
                if (event.data.size > 0) {
                  audioChunks.push(event.data);
                }
              };

              mediaRecorder.onstop = async () => {
                console.log('Arrêt de l\'enregistrement');
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const formData = new FormData();
                formData.append('audio', audioBlob, 'audio.wav');
                formData.append('btn_msg', '1');
                formData.append('is_voice', '1'); // Ajout d'un flag pour indiquer que c'est un message vocal

                try {
                  console.log('Envoi de l\'audio...');
                  const response = await fetch('upload_audio.php?user_id=<?= $getid ?>', {
                    method: 'POST',
                    body: formData
                  });
                  if (response.ok) {
                    console.log('Audio envoyé avec succès');
                    location.reload();
                  } else {
                    console.error('Erreur lors de l\'envoi de l\'audio');
                  }
                } catch (error) {
                  console.error('Erreur lors de l\'envoi de l\'audio:', error);
                }
              };

              mediaRecorder.start();
              isRecording = true;
              recordButton.innerHTML = '<i class="fas fa-stop"></i>';
              recordButton.classList.add('btn-danger');
            } catch (error) {
              console.error('Erreur lors de l\'accès au microphone:', error);
              alert('Impossible d\'accéder au microphone. Veuillez vérifier les permissions.');
            }
          } else {
            console.log('Arrêt de l\'enregistrement');
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
              mediaRecorder.stop();
              isRecording = false;
              recordButton.innerHTML = '<i class="fas fa-microphone"></i>';
              recordButton.classList.remove('btn-danger');
            }
          }
        });
      });
    </script>
  </body>
</html>