<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();
if(isset($_SESSION['prenom_nom'])) {
  header('location: home.php');
}

if(isset($_POST['submit']))
{
  $email = htmlspecialchars($_POST['matricule']);
  $password = $_POST['password'];

  $insertion = $connexion -> prepare('SELECT * FROM utilisateur WHERE matricule = ?');
  $insertion->execute(array($email));

  if($insertion->rowCount() > 0)
  {
    if(!empty($_POST['matricule']) AND !empty($_POST['password']))
    {
      $info = $insertion->fetch();
      if(password_verify($password, $info['mot_de_pass'])) {
        $_SESSION['id'] = $info['id'];
        $_SESSION['prenom_nom'] = $info['prenom_nom'];

        // Mettre à jour le statut de l'utilisateur
        $update_status = $connexion->prepare('UPDATE utilisateur SET statut = "en ligne", derniere_connexion = NOW() WHERE id = ?');
        $update_status->execute(array($_SESSION['id']));

        header('location: home.php?id='.$_SESSION['id']);
        exit;
      } else {
        $error1 = "Votre matricule ou mot de passe est incorrect";
      }
    }
  } else {
    $error1 = "Votre matricule ou mot de passe est incorrect";
  }
};

?>



<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion - Vibe</title>
    <link rel="stylesheet" href="style.css" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="assets/images/vibe.png">
    <style>
      body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }
      .form-container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem !important;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        background: white;
        animation: fadeIn 0.5s ease-in-out;
      }
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      h3 {
        color: #007bff;
        margin-bottom: 2rem;
        font-weight: 600;
      }
      .form-floating {
        margin-bottom: 1.5rem;
      }
      .form-control {
        border-radius: 10px;
        padding: 1rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
      }
      .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      }
      .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
      }
      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
      }
      .btn-primary:active {
        transform: translateY(1px);
      }
      .alert {
        border-radius: 10px;
        margin-bottom: 1.5rem;
      }
      a {
        color: #007bff;
        font-weight: 600;
        transition: all 0.3s ease;
      }
      a:hover {
        color: #0056b3;
        text-decoration: underline;
      }
      footer {
        margin-top: auto;
        padding: 1rem 0;
      }
    </style>
  </head>
  <body>
    <div class="container form-container">
      <form action="" method="post" enctype="multipart/form-data">
        <div class="text-center mb-4">
          <img src="assets/logo.png" alt="Logo Vibe" style="max-width: 200px; height: auto;">
        </div>
        <h3 class="text-center">CONNEXION</h3>

        <?php if(isset($error1)) { ?>
          <div class="alert alert-danger" role="alert">
            <?php echo $error1; ?>
          </div>
        <?php } ?>

        <div class="form-floating">
          <input
            type="text"
            class="form-control"
            id="floatingInput"
            name="matricule"
            placeholder="2100542"
            required
          />
          <label for="floatingInput"><i class="fas fa-id-card me-2"></i>Matricule</label>
        </div>
        <div class="form-floating">
          <input
            type="password"
            class="form-control"
            id="floatingPassword"
            name="password"
            placeholder="Votre mot de passe"
            required
          />
          <label for="floatingPassword"><i class="fas fa-lock me-2"></i>Mot de passe</label>
        </div>
        <div class="d-grid gap-2">
          <input
            class="btn btn-primary"
            type="submit"
            value="Let's Go"
            name="submit"
          />
        </div>
        <p class="text-center mt-3">
          <i class="fas fa-user-plus me-2"></i>J'ai pas de compte,
          <a href="inscription.php">je m'inscris</a>
        </p>
      </form>
    </div>
    <footer class="text-center">
      <span class="text-black-50">&copy; 2025 - Vibe</span>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
