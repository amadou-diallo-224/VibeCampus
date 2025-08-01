<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

if(!isset($_SESSION['prenom_nom'])) {
  exit;
}

include 'user.php';
include 'autre-users.php';

$user = getUser($_SESSION['prenom_nom'], $connexion);

// Récupérer le statut de l'utilisateur
$get_status = $connexion->prepare('SELECT statut FROM utilisateur WHERE id = ?');
$get_status->execute(array($_SESSION['id']));
$user_status = $get_status->fetch()['statut'];

// Logique de recherche
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

// Afficher les résultats
if(isset($_GET['recherche']) && !empty($_GET['recherche'])) {
  echo '<div class="container mt-3"><p>Résultats de recherche pour : <strong>' . htmlspecialchars($_GET['recherche']) . '</strong></p></div>';
}

if($membres->rowCount() > 0) {
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
    <?php
  }
} else {
  echo '<p>Aucun utilisateur trouvé</p>';
}
?> 