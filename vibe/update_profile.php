<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();
if(!isset($_SESSION['prenom_nom'])) {
  header('location: connexion.php');
}
if (isset($_SESSION['prenom_nom'])) {
  include 'user.php';
  $user = getUser($_SESSION['prenom_nom'], $connexion);

  // Traitement du formulaire
  if(isset($_POST['submit'])) {
    $errors = [];
    $success = false;

    // Vérification et mise à jour du nom
    if(!empty($_POST['prenom_nom'])) {
      $new_name = htmlspecialchars($_POST['prenom_nom']);
      $update_name = $connexion->prepare('UPDATE utilisateur SET prenom_nom = ? WHERE id = ?');
      $update_name->execute([$new_name, $user['id']]);
      $_SESSION['prenom_nom'] = $new_name;
      $success = true;
    }

    // Vérification et mise à jour du mot de passe
    if(!empty($_POST['password']) && !empty($_POST['password_confirm'])) {
      if($_POST['password'] === $_POST['password_confirm']) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_password = $connexion->prepare('UPDATE utilisateur SET mot_de_pass = ? WHERE id = ?');
        $update_password->execute([$hashed_password, $user['id']]);
        $success = true;
      } else {
        $errors[] = "Les mots de passe ne correspondent pas";
      }
    }

    // Traitement de l'image
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
      error_log("Traitement de l'image commencé");
      error_log("Type de fichier: " . $_FILES['image']['type']);
      error_log("Taille du fichier: " . $_FILES['image']['size']);
      
      $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
      $max_size = 5 * 1024 * 1024; // 5MB

      if(in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
        $upload_dir = __DIR__ . '/upload_images/';
        if(!is_dir($upload_dir)) {
          mkdir($upload_dir, 0777, true);
        }
        
        // Générer un nom de fichier unique
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = __DIR__ . '/upload_images/' . $new_filename;
        
        error_log("Chemin de destination: " . $upload_path);
        error_log("Ancienne image: " . $user['image']);
        error_log("Chemin complet de l'ancienne image: " . __DIR__ . '/upload_images/' . $user['image']);

        if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
          error_log("Fichier déplacé avec succès");
          
          // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
          if($user['image'] !== 'Profile-PNG-Photo.png' && file_exists(__DIR__ . '/upload_images/' . $user['image'])) {
            unlink(__DIR__ . '/upload_images/' . $user['image']);
            error_log("Ancienne image supprimée");
          }
          
          // Mettre à jour la base de données
          $update_image = $connexion->prepare('UPDATE utilisateur SET image = ? WHERE id = ?');
          $result = $update_image->execute([$new_filename, $user['id']]);
          
          error_log("Résultat de la mise à jour DB: " . ($result ? "succès" : "échec"));
          
          // Mettre à jour la variable $user avec la nouvelle image
          $user['image'] = $new_filename;
          $_SESSION['image'] = $new_filename;
          
          // Forcer le rechargement complet des données utilisateur
          $user = getUser($_SESSION['prenom_nom'], $connexion);
          
          $success = true;
        } else {
          error_log("Erreur lors du déplacement du fichier");
          $errors[] = "Erreur lors du téléchargement de l'image";
        }
      } else {
        error_log("Format ou taille de fichier invalide");
        $errors[] = "Format d'image non supporté ou fichier trop volumineux (max 5MB)";
      }
    }

    if($success) {
      // Recharger les données utilisateur après mise à jour
      $user = getUser($_SESSION['prenom_nom'], $connexion);
      $_SESSION['image'] = $user['image'];
      
      // Ajouter un délai pour s'assurer que tout est bien mis à jour
      sleep(1);
      
      error_log("Nouvelle image après rechargement: " . $user['image']);
      header('Location: update_profile.php?success=1');
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour du profil - Vibe</title>
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
      
      .profile-container {
        max-width: 800px;
        margin: 40px auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
      }
      
      .profile-header {
        text-align: center;
        margin-bottom: 30px;
      }
      
      .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--vibe-blue);
        margin-bottom: 20px;
        transition: transform 0.3s ease;
      }
      
      .profile-image:hover {
        transform: scale(1.05);
      }
      
      .profile-name {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--vibe-dark);
        margin-bottom: 5px;
      }
      
      .profile-status {
        color: #6c757d;
        font-size: 0.9rem;
      }
      
      .form-label {
        font-weight: 500;
        color: var(--vibe-dark);
      }
      
      .form-control {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #dee2e6;
      }
      
      .form-control:focus {
        border-color: var(--vibe-blue);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      }
      
      .btn-primary {
        background-color: var(--vibe-blue);
        border: none;
        padding: 10px 25px;
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
      }
      
      .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
      }
      
      .btn-danger {
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 500;
        transition: all 0.3s ease;
      }
      
      .btn-danger:hover {
        transform: translateY(-2px);
      }
      
      .alert {
        border-radius: 10px;
        margin-bottom: 20px;
      }
      
      .logout-link {
        color: #dc3545;
        text-decoration: none;
        transition: color 0.3s ease;
      }
      
      .logout-link:hover {
        color: #c82333;
      }
      
      .file-input-label {
        display: block;
        padding: 10px 15px;
        background-color: var(--vibe-light);
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
      }
      
      .file-input-label:hover {
        background-color: #e9ecef;
        border-color: var(--vibe-blue);
      }
      
      .file-input-text {
        color: #6c757d;
        font-size: 0.9rem;
      }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <div class="d-flex align-items-center">
          <a class="navbar-brand" href="home.php">
            <i class="fas fa-arrow-left"></i>
          </a>
          <h5 class="mb-0">Mise à jour du profil</h5>
        </div>
        <div>
          <a href="deconnexion.php" class="btn btn-outline-danger">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </div>
      </div>
    </nav>

    <div class="profile-container">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Profil mis à jour avec succès !
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php foreach($errors as $error): ?>
                    <p class="mb-0"><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <img
                src="upload_images/<?=$user['image']?>"
                alt="Photo de profil"
                class="profile-image"
            />
            <h3 class="profile-name"><?= htmlspecialchars($user['prenom_nom']) ?></h3>
            <p class="profile-status">
                <i class="fas fa-circle me-1" style="color: <?= $user['statut'] === 'en ligne' ? '#28a745' : '#dc3545' ?>"></i>
                <?= $user['statut'] ?>
            </p>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="prenom_nom" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="prenom_nom" name="prenom_nom" 
                       value="<?= htmlspecialchars($user['prenom_nom']) ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Laissez vide pour ne pas changer">
            </div>
            
            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                       placeholder="Laissez vide pour ne pas changer">
            </div>
            
            <div class="mb-4">
                <label for="image" class="form-label">Photo de profil</label>
                <label for="image" class="file-input-label">
                    <i class="fas fa-cloud-upload-alt me-2"></i>
                    Choisir une nouvelle photo
                    <input type="file" class="form-control d-none" id="image" name="image" accept="image/*">
                </label>
                <div class="file-input-text mt-2">
                    Formats acceptés : JPG, PNG. Taille maximale : 5MB
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script>
      // Afficher le nom du fichier sélectionné
      document.getElementById('image').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if(fileName) {
          document.querySelector('.file-input-label').innerHTML = 
            `<i class="fas fa-check-circle me-2"></i>${fileName}`;
        }
      });
    </script>
</body>
</html>