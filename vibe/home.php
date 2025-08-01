<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();
if(!isset($_SESSION['prenom_nom'])) {
  header('location: connexion.php');
}

// Mettre à jour le statut de l'utilisateur à chaque chargement de la page
if(isset($_SESSION['id'])) {
  $update_status = $connexion->prepare('UPDATE utilisateur SET derniere_connexion = NOW() WHERE id = ?');
  $update_status->execute(array($_SESSION['id']));
}

// Gérer la déconnexion
if(isset($_GET['logout'])) {
  $update_status = $connexion->prepare('UPDATE utilisateur SET statut = "hors ligne" WHERE id = ?');
  $update_status->execute(array($_SESSION['id']));
  session_destroy();
  header('location: connexion.php');
  exit;
}

if (isset($_SESSION['prenom_nom'])) {
/*if(isset($_GET['id']) AND $_GET['id'] > 0)
{*/
 
  include 'user.php';
  include 'autre-users.php';
  /*$takeid = intval($_GET['id']);

  $insertion = $connexion -> prepare ('SELECT * FROM utilisateur WHERE id = ?');
  $insertion -> execute (array($takeid));

  $takeinfo = $insertion ->fetch ();*/

  $user =getUser($_SESSION['prenom_nom'], $connexion); 

  // Récupérer le statut de l'utilisateur
  $get_status = $connexion->prepare('SELECT statut FROM utilisateur WHERE id = ?');
  $get_status->execute(array($_SESSION['id']));
  $user_status = $get_status->fetch()['statut'];

  // Logique de recherche avec tri des messages non lus
  $search_query = '';
  if(isset($_GET['recherche']) && !empty($_GET['recherche'])) {
    $search_query = $_GET['recherche'];
    $membres = $connexion->prepare('
      SELECT u.*, 
             COUNT(CASE WHEN m.is_read = FALSE AND m.id_destinataire = ? THEN 1 END) as unread_count
      FROM utilisateur u
      LEFT JOIN messages m ON m.id_auteur = u.id 
      WHERE u.prenom_nom LIKE ? 
      AND u.id != ?
      GROUP BY u.id
      ORDER BY unread_count DESC, u.prenom_nom ASC
    ');
    $membres->execute([$_SESSION['id'], '%' . $search_query . '%', $_SESSION['id']]);
  } else {
    $membres = $connexion->prepare('
      SELECT u.*, 
             COUNT(CASE WHEN m.is_read = FALSE AND m.id_destinataire = ? THEN 1 END) as unread_count
      FROM utilisateur u
      LEFT JOIN messages m ON m.id_auteur = u.id 
      WHERE u.id != ?
      GROUP BY u.id
      ORDER BY unread_count DESC, u.prenom_nom ASC
    ');
    $membres->execute([$_SESSION['id'], $_SESSION['id']]);
  }

  // Récupérer les messages non lus
  $unread_messages = $connexion->prepare('
    SELECT m.*, u.prenom_nom, u.image, u.statut 
    FROM messages m 
    JOIN utilisateur u ON m.id_auteur = u.id 
    WHERE m.id_destinataire = ? AND m.is_read = FALSE 
    ORDER BY m.date DESC
  ');
  $unread_messages->execute([$_SESSION['id']]);
  $unread_count = $unread_messages->rowCount();
/*};*/


?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accueil - Vibe</title>
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
      .unread-message {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
      }
    </style>
  </head>
  <body>
    <div class="users">
      <!-- Mon profile -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid" style="padding: 10px 20px">
        <div class="profile-section" style="display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 8px 15px; border-radius: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
          <div class="profile-image">
            <a class="navbar-brand" href="update_profile.php" style="padding: 0;">
              <img
                src="upload_images/<?= $user['image']?>"
                alt="Profile"
                style="
                  width: 45px;
                  height: 45px;
                  border: 2px solid #fff;
                  border-radius: 50%;
                  object-fit: cover;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                  transition: transform 0.3s ease;
                "
                onmouseover="this.style.transform='scale(1.05)'"
                onmouseout="this.style.transform='scale(1)'"
              />
            </a>
          </div>
          <div class="profile-info">
            <span style="font-size: 15px; font-weight: 600; color: #2c3e50; display: block;">
              <?= ucwords($user['prenom_nom'])?>
            </span>
            <span style="font-size: 12px; color: #6c757d; display: flex; align-items: center; gap: 5px;">
              <span class="status-indicator status-<?= $user_status === 'en ligne' ? 'online' : 'offline' ?>" style="width: 8px; height: 8px;"></span>
              <?= $user_status ?>
            </span>
          </div>
        </div>
        <div class="search" style="padding-top: 10px; margin-left: 2rem">
          <form action="" method="GET" class="d-flex" role="search">
            <input
              class="form-control me-2"
              type="search"
              name="recherche"
              id="searchInput"
              placeholder="Rechercher un utilisateur..."
              aria-label="Search"
              autocomplete="off"
              value="<?= isset($_GET['recherche']) ? htmlspecialchars($_GET['recherche']) : '' ?>"
            />
            <button class="btn btn-outline-primary" type="submit" name="search">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </nav>

    <!-- Section Événements -->
    <div class="events-section">
      <div class="events-header">
        <h4 class="events-title">Événements à venir</h4>
        <button class="btn btn-primary create-event-btn" data-bs-toggle="modal" data-bs-target="#addEventModal">
          <i class="fas fa-plus"></i> Créer un événement
        </button>
      </div>
      
      <div class="events-container">
        <div class="events-scroll">
          <?php
          // Récupérer les événements à venir
          $events = $connexion->prepare('
            SELECT e.*, u.prenom_nom, u.image as user_image 
            FROM evenements e 
            JOIN utilisateur u ON e.createur_id = u.id 
            WHERE e.date_evenement >= NOW() 
            ORDER BY e.date_evenement DESC
          ');
          $events->execute();

          if($events->rowCount() > 0) {
            while($event = $events->fetch()) {
              $event_date = new DateTime($event['date_evenement']);
              $now = new DateTime();
              $diff = $now->diff($event_date);
              $days_left = $diff->days;
              ?>
              <div class="event-card">
                <div class="event-image">
                  <img src="upload_images/<?= $event['image'] ?>" alt="<?= htmlspecialchars($event['titre']) ?>">
                  <div class="event-type-badge <?= $event['type_evenement'] ?>">
                    <?= ucfirst($event['type_evenement']) ?>
                  </div>
                </div>
                <div class="event-content">
                  <h4><?= htmlspecialchars($event['titre']) ?></h4>
                  <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                  <div class="event-details">
                    <div class="event-date">
                      <i class="fas fa-calendar"></i>
                      <?= $event_date->format('d/m/Y H:i') ?>
                    </div>
                    <div class="event-location">
                      <i class="fas fa-map-marker-alt"></i>
                      <?= htmlspecialchars($event['lieu']) ?>
                    </div>
                    <div class="event-contact">
                      <?php
                      $contact_info = explode(':', $event['contact_info']);
                      $contact_type = $contact_info[0];
                      $contact_value = $contact_info[1];
                      if ($contact_type === 'tel') {
                        echo '<i class="fas fa-phone"></i> <a href="tel:' . htmlspecialchars($contact_value) . '" class="contact-link">' . htmlspecialchars($contact_value) . '</a>';
                      } else {
                        echo '<i class="fas fa-link"></i> <a href="' . htmlspecialchars($contact_value) . '" class="contact-link" target="_blank">Réserver</a>';
                      }
                      ?>
                    </div>
                  </div>
                  <div class="event-creator">
                    <img src="upload_images/<?= $event['user_image'] ?>" alt="<?= htmlspecialchars($event['prenom_nom']) ?>">
                    <span><?= htmlspecialchars($event['prenom_nom']) ?></span>
                  </div>
                  <?php if($days_left <= 7): ?>
                    <div class="event-countdown">
                      <i class="fas fa-clock"></i>
                      <?= $days_left ?> jour<?= $days_left > 1 ? 's' : '' ?> restant<?= $days_left > 1 ? 's' : '' ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <?php
            }
          } else {
            echo '<div class="no-events">Aucun événement à venir</div>';
          }
          ?>
        </div>
      </div>
    </div>

    <!-- Modal pour créer un événement -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addEventModalLabel">Créer un nouvel événement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="create_event.php" method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="eventTitle" class="form-label">Titre de l'événement</label>
                <input type="text" class="form-control" id="eventTitle" name="titre" required>
              </div>
              <div class="mb-3">
                <label for="eventDescription" class="form-label">Description</label>
                <textarea class="form-control" id="eventDescription" name="description" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                <label for="eventDate" class="form-label">Date et heure</label>
                <input type="datetime-local" class="form-control" id="eventDate" name="date_evenement" required>
              </div>
              <div class="mb-3">
                <label for="eventLocation" class="form-label">Lieu</label>
                <input type="text" class="form-control" id="eventLocation" name="lieu" required>
              </div>
              <div class="mb-3">
                <label for="eventType" class="form-label">Type d'événement</label>
                <select class="form-select" id="eventType" name="type_evenement" required>
                  <option value="soiree">Soirée</option>
                  <option value="concert">Concerts et festivals de musique</option>
                  <option value="exposition">Expositions d'art</option>
                  <option value="spectacle">Représentations théâtrales et spectacles de danse</option>
                  <option value="cinema">Projections de films et ciné-clubs</option>
                  <option value="litteraire">Événements littéraires</option>
                  <option value="conference">Conférences et débats sur des sujets d'actualité</option>
                  <option value="sport">Compétitions sportives amicales</option>
                  <option value="hackathon">Hackathons et défis créatifs</option>
                  <option value="tech">Conférences et ateliers sur les nouvelles technologies</option>
                  <option value="salon">Salons et forums sur l'innovation et l'entrepreneuriat technologique</option>
                  <option value="formation">Formation</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="eventContact" class="form-label">Informations de contact pour la réservation</label>
                <div class="input-group">
                  <select class="form-select" id="contactType" name="contact_type" style="max-width: 120px;" required>
                    <option value="tel">Téléphone</option>
                    <option value="link">Lien</option>
                  </select>
                  <input type="text" class="form-control" id="eventContact" name="contact_info" placeholder="Entrez le numéro ou le lien" required>
                </div>
                <small class="text-muted">Fournissez un numéro de téléphone ou un lien pour permettre aux participants de réserver</small>
              </div>
              <div class="mb-3">
                <label for="eventImage" class="form-label">Image</label>
                <input type="file" class="form-control" id="eventImage" name="image" accept="image/*" required>
              </div>
              <button type="submit" class="btn btn-primary">Créer l'événement</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <style>
      .events-section {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      }

      .events-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
      }

      .events-title {
        font-size: 1.5rem;
        margin: 0;
      }

      .create-event-btn {
        padding: 8px 16px;
        font-size: 1rem;
      }

      /* Styles pour les écrans mobiles */
      @media (max-width: 768px) {
        .events-title {
          font-size: 1.2rem;
        }

        .create-event-btn {
          padding: 6px 12px;
          font-size: 0.9rem;
        }

        .create-event-btn i {
          font-size: 0.8rem;
        }
      }

      /* Styles pour les très petits écrans */
      @media (max-width: 480px) {
        .events-title {
          font-size: 1rem;
        }

        .create-event-btn {
          padding: 5px 10px;
          font-size: 0.8rem;
        }

        .create-event-btn i {
          font-size: 0.7rem;
        }
      }

      .events-container {
        position: relative;
        overflow: hidden;
      }

      .events-scroll {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 10px 0;
        gap: 20px;
        -webkit-overflow-scrolling: touch;
      }

      .events-scroll::-webkit-scrollbar {
        height: 8px;
      }

      .events-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
      }

      .events-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
      }

      .events-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
      }

      .event-card {
        flex: 0 0 300px;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
      }

      .event-card:hover {
        transform: translateY(-5px);
      }

      .event-image {
        position: relative;
        height: 150px;
        overflow: hidden;
      }

      .event-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .event-type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 20px;
        color: white;
        font-size: 12px;
        font-weight: bold;
      }

      .event-type-badge.soiree {
        background: #ff6b6b;
      }

      .event-type-badge.formation {
        background: #4ecdc4;
      }

      .event-type-badge.autre {
        background: #ffd166;
      }

      .event-content {
        padding: 15px;
      }

      .event-content h4 {
        margin: 0 0 10px 0;
        font-size: 18px;
        color: #333;
      }

      .event-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .event-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
      }

      .event-date, .event-location {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
      }

      .event-creator {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
      }

      .event-creator img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
      }

      .event-creator span {
        font-size: 14px;
        color: #666;
      }

      .event-countdown {
        margin-top: 10px;
        padding: 5px 10px;
        background: #ff6b6b;
        color: white;
        border-radius: 15px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
      }

      .no-events {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
      }

      .event-contact {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
        margin-top: 8px;
      }

      .contact-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
      }

      .contact-link:hover {
        text-decoration: underline;
      }

      .event-contact i {
        color: #666;
        width: 16px;
        text-align: center;
      }
    </style>

    <!-- Autres utilisateurs -->
    <div id="searchResults">
    <?php
    if(isset($_GET['recherche']) && !empty($_GET['recherche'])) {
      echo '<div class="container mt-3"><p>Résultats de recherche pour : <strong>' . htmlspecialchars($_GET['recherche']) . '</strong></p></div>';
    }
    
    if($membres -> rowCount() > 0 ) {
      while ($row = $membres->fetch()) {
        $has_unread = $row['unread_count'] > 0;
    ?>

    <section class="liste_utilisateur container <?= $has_unread ? 'unread-message' : '' ?>" style="justify-content: center; margin-top: 15px; border-radius: 5px; height: 100px;"> 
      <a href="chat.php?user_id=<?= $row['id']?>" style="text-decoration:none; color:black;">
        <div class="element" style="display:flex;align-items:center">
          <div class="image">
            <img
              src="upload_images/<?= $row['image']?>" 
              alt=""
              style="width: 80px; height: 80px; border: 1px solid white; border-radius: 50px; object-fit: cover;"
            />
          </div>
          <div class="info" style="margin-left:15px;">
            <h4 style="text-transform:capitalize"><?= $row['prenom_nom']?></h4>
            <span class="text-black-50"><?= $row['nom_etablissement']?></span> <br>
            <span>
              <span class="status-indicator status-<?= $row['statut'] === 'en ligne' ? 'online' : 'offline' ?>"></span>
              <?= $row['statut'] ?>
              <?php if($has_unread) { ?>
                <span class="badge bg-primary"><?= $row['unread_count'] ?> nouveau<?= $row['unread_count'] > 1 ? 'x' : '' ?> message<?= $row['unread_count'] > 1 ? 's' : '' ?></span>
              <?php } ?>
            </span>
          </div>
        </div>
      </a>
    </section>

    <?php  }    ?>
    <?php  }  else {  ?>
      <div class="no-users-found" style="text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 10px; margin: 20px auto; max-width: 500px;">
        <i class="fas fa-user-slash" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
        <p style="font-size: 18px; color: #495057; margin: 0;">Aucun utilisateur trouvé</p>
      </div>
      <?php  }    ?>
    </div>
    <?php  }    ?>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script>
      // Gestion de la recherche en temps réel
      document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchValue = this.value;
        const searchResults = document.getElementById('searchResults');
        
        // Créer une requête AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_users.php?recherche=' + encodeURIComponent(searchValue), true);
        
        xhr.onload = function() {
          if (this.status === 200) {
            searchResults.innerHTML = this.responseText;
          }
        };
        
        xhr.send();
      });

      // Gestion du type de contact
      document.getElementById('contactType').addEventListener('change', function() {
        const contactInput = document.getElementById('eventContact');
        if (this.value === 'tel') {
          contactInput.placeholder = 'Entrez le numéro de téléphone';
          contactInput.type = 'tel';
        } else {
          contactInput.placeholder = 'Entrez le lien de réservation';
          contactInput.type = 'url';
        }
      });
    </script>
    <script>
      // Rafraîchir la page automatiquement toutes les 1 minute
      setInterval(function() {
        window.location.reload();
      }, 60000); // 60000 millisecondes = 1 minute
    </script>
  </body>
</html>