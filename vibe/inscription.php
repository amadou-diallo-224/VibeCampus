<?php
session_start();
require_once 'config.php';

// Initialize database connection
$connexion = getDBConnection();

// Vérifier si l'utilisateur est déjà connecté

// Vérifier si l'utilisateur est déjà connecté
if(isset($_SESSION['prenom_nom'])) {
    header('location: home.php');
    exit;
}

// Gestion de la soumission du formulaire
if(isset($_POST['valider'])) {
    // Récupération et nettoyage des données
    $prenom_nom = htmlspecialchars($_POST['prenom_nom']);
    $matricule = htmlspecialchars($_POST['matricule']);
    $sexe = htmlspecialchars($_POST['sexe']);
    $tel = htmlspecialchars($_POST['tel']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $departement = htmlspecialchars($_POST['departement']);
    $niveau = htmlspecialchars($_POST['niveau']);
    $nom_etablissement = htmlspecialchars($_POST['nom_etablissement']);
    $cgu = isset($_POST['cgu']) ? true : false;

    // Vérification des données
    $erreur = '';
    
    // Vérifier si le matricule existe déjà
    $check_matricule = $connexion->prepare('SELECT * FROM utilisateur WHERE matricule = ?');
    $check_matricule->execute([$matricule]);
    
    if($check_matricule->rowCount() > 0) {
        $erreur = "Ce matricule est déjà utilisé. Veuillez utiliser un autre matricule.";
    } elseif(!$cgu) {
        $erreur = "Vous devez accepter les conditions générales d'utilisation pour continuer.";
    } else {
        // Vérification des champs obligatoires
        if(empty($prenom_nom) || empty($matricule) || empty($sexe) || empty($tel) || 
           empty($password) || empty($departement) || empty($niveau) || empty($nom_etablissement)) {
            $erreur = "Tous les champs doivent être complétés !";
        } else {
            // Gestion de l'upload de l'image
            $image = $_FILES['image'];
            $image_error = $image['error'];
            
            if($image_error === 0) {
                $image_size = $image['size'];
                $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                
                if($image_size <= 5000000) {
                    if(in_array($image_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $image_name = time() . '.' . $image_ext;
                        $image_path = 'upload_images/' . $image_name;
                        
                        if(!is_dir('upload_images')) {
                            mkdir('upload_images', 0777, true);
                        }
                        
                        if(move_uploaded_file($image['tmp_name'], $image_path)) {
                            // Insertion dans la base de données
                            $insertmbr = $connexion->prepare('INSERT INTO utilisateur(prenom_nom, matricule, sexe, tel, mot_de_pass, departement, niveau, nom_etablissement, image) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $insertmbr->execute([$prenom_nom, $matricule, $sexe, $tel, $password, $departement, $niveau, $nom_etablissement, $image_name]);
                            
                            $succes = "Félicitation, votre compte a été créé avec succès !";
                            header('location:connexion.php');
                            exit;
                        } else {
                            $erreur = "Erreur lors du téléchargement de l'image !";
                        }
                    } else {
                        $erreur = "Votre photo de profil doit être au format jpg, jpeg, png ou gif !";
                    }
                } else {
                    $erreur = "Votre photo de profil ne doit pas dépasser 5MB !";
                }
            } else {
                $erreur = "Erreur lors du téléchargement de l'image !";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inscription - Vibe</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="icon" href="assets/images/vibe.png" />
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .form-container {
            max-width: 600px;
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
        .form-floating, .form {
            margin-bottom: 1.5rem;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
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
        .form-check {
            margin-bottom: 1rem;
        }
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
        .custom-file-upload {
            border: 2px dashed #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .custom-file-upload:hover {
            border-color: #007bff;
        }
        .file-info {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="text-center mb-4">
                <img src="assets/logo.png" alt="Logo Vibe" style="max-width: 200px; height: auto;">
            </div>
            <h3 class="text-center">INSCRIPTION</h3>

            <?php if(isset($erreur)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $erreur; ?>
                </div>
            <?php } elseif(isset($succes)) { ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $succes; ?>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="prenom_nom" id="floatingInput" placeholder="" required>
                        <label for="floatingInput"><i class="fas fa-user me-2"></i>Prénom & Nom</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="matricule" id="floatingMatricule" placeholder="" required>
                        <label for="floatingMatricule"><i class="fas fa-id-card me-2"></i>Matricule</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="tel" id="floatingTel" placeholder="62X XX XX XX" required>
                        <label for="floatingTel"><i class="fas fa-phone me-2"></i>Téléphone</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sexe" id="homme" value="Homme" required>
                        <label class="form-check-label" for="homme"><i class="fas fa-mars me-2"></i>Homme</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sexe" id="femme" value="Femme" required>
                        <label class="form-check-label" for="femme"><i class="fas fa-venus me-2"></i>Femme</label>
                    </div>
                </div>
            </div>

            <div class="form">
                <select class="form-select" name="departement" required>
                    <option value="" selected disabled><i class="fas fa-building me-2"></i>Département</option>
                    <option value="Administration des Affaires">Administration des Affaires</option>
                    <option value="Banque et Assurances">Banque et Assurances</option>
                    <option value="Banque et Finances">Banque et Finances</option>
                    <option value="Economie">Economie</option>
                    <option value="Sciences Comptables">Sciences Comptables</option>
                    <option value="Commerce International,Transit et Douane">Commerce International,Transit et Douane</option>
                    <option value="Logistique et Transport">Logistique et Transport</option>
                    <option value="Droit">Droit</option>
                    <option value="Licence pro en Communication">Licence pro en Communication</option>
                    <option value="Sociologie">Sociologie</option>
                    <option value="Génie Informatique et Télécommunication">Génie Informatique et Télécommunication</option>
                    <option value="Miage">Miage</option>
                    <option value="Génie électronique">Génie électronique</option>
                    <option value="Génie Civil">Génie Civil</option>
                    <option value="Autres">Autres</option>
                </select>
            </div>

            <div class="form">
                <select class="form-select" name="niveau" required>
                    <option value="" selected disabled><i class="fas fa-graduation-cap me-2"></i>Niveau</option>
                    <optgroup label="LICENCE">
                        <option value="L1">L1</option>
                        <option value="L2">L2</option>
                        <option value="L3">L3</option>
                    </optgroup>
                    <optgroup label="MASTER">
                        <option value="M1">M1</option>
                        <option value="M2">M2</option>
                    </optgroup>
                </select>
            </div>

            <div class="form">
                <select class="form-select" name="nom_etablissement" required>
                    <option value="" selected disabled><i class="fas fa-university me-2"></i>Etablissement</option>
                    <option value="Université Gamal Abdel Nasser de Conakry (UGANC)">Université Gamal Abdel Nasser de Conakry (UGANC)</option>
                    <option value="Université Général Lansana Conté de Sonfonia">Université Général Lansana Conté de Sonfonia</option>
                    <option value="Université Kofi Annan de Guinée (UKAG)">Université Kofi Annan de Guinée (UKAG)</option>
                    <option value="Université Amadou Dieng (UAD)">Université Amadou Dieng (UAD)</option>
                    <option value="Université Nongo Conakry (UNC)">Université Nongo Conakry (UNC)</option>
                    <option value="Université Barack Obama (UBO)">Université Barack Obama (UBO)</option>
                    <option value="Université Internationale Cheick Modibo Diarra (UICMD)">Université Internationale Cheick Modibo Diarra (UICMD)</option>
                    <option value="Université de Kindia (UDK)">Université de Kindia (UDK)</option>
                </select>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Votre mot de passe" required>
                <label for="floatingPassword"><i class="fas fa-lock me-2"></i>Mot de passe</label>
            </div>

            <div class="form">
                <label for="image" class="custom-file-upload">
                    <i class="fas fa-camera me-2"></i> Ajouter une photo de profil
                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/jpg" style="display: none;" required>
                </label>
                <div class="file-info" id="fileInfo"><i class="fas fa-info-circle me-2"></i>Format accepté: PNG, JPG, JPEG (max 5Mo)</div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="cgu" name="cgu" required>
                <label class="form-check-label" for="cgu">
                    J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#cguModal">conditions générales d'utilisation</a>
                </label>
            </div>

            <div class="d-grid gap-2">
                <input class="btn btn-primary" type="submit" value="Let's Go" name="valider">
            </div>

            <p class="text-center mt-3">
                <i class="fas fa-sign-in-alt me-2"></i>J'ai déjà un compte,
                <a href="connexion.php">je me connecte</a>
            </p>
        </form>
    </div>

    <!-- Modal CGU -->
    <div class="modal fade" id="cguModal" tabindex="-1" aria-labelledby="cguModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cguModalLabel">Conditions Générales d'Utilisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptation des conditions</h6>
                    <p>En vous inscrivant sur Vibe, vous acceptez d'être lié par les présentes conditions générales d'utilisation.</p>

                    <h6>2. Description du service</h6>
                    <p>Vibe est une plateforme de mise en relation entre étudiants, permettant le partage d'informations et la création de communautés.</p>

                    <h6>3. Inscription et compte utilisateur</h6>
                    <p>Pour utiliser nos services, vous devez créer un compte en fournissant des informations exactes et à jour. Vous êtes responsable de la confidentialité de votre mot de passe.</p>

                    <h6>4. Respect de la vie privée</h6>
                    <p>Nous nous engageons à protéger vos données personnelles conformément à notre politique de confidentialité.</p>

                    <h6>5. Comportement des utilisateurs</h6>
                    <p>Vous vous engagez à :
                    <ul>
                        <li>Respecter les autres utilisateurs</li>
                        <li>Ne pas publier de contenu illégal ou inapproprié</li>
                        <li>Ne pas utiliser la plateforme à des fins commerciales non autorisées</li>
                    </ul>
                    </p>

                    <h6>6. Propriété intellectuelle</h6>
                    <p>Le contenu que vous publiez reste votre propriété, mais vous nous accordez une licence pour l'utiliser sur la plateforme.</p>

                    <h6>7. Modification des conditions</h6>
                    <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications prendront effet dès leur publication.</p>

                    <h6>8. Résiliation</h6>
                    <p>Nous pouvons suspendre ou résilier votre compte en cas de violation des présentes conditions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const cguCheckbox = document.getElementById('cgu');
            const matriculeInput = document.getElementById('floatingMatricule');
            const fileInput = document.getElementById('image');
            const file = fileInput.files[0];

            if (!cguCheckbox.checked) {
                alert('Vous devez accepter les conditions générales d\'utilisation pour continuer.');
                return false;
            }

            if (!matriculeInput.value) {
                alert('Veuillez saisir votre matricule.');
                return false;
            }

            if (!file) {
                alert('Veuillez sélectionner une photo de profil.');
                return false;
            }

            if (file.size > 5000000) {
                alert('La photo de profil ne doit pas dépasser 5MB.');
                return false;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('La photo de profil doit être au format JPG, PNG ou GIF.');
                return false;
            }

            return true;
        }

        // Afficher le nom du fichier sélectionné
        document.getElementById('image').addEventListener('change', function(e) {
            const fileInfo = document.getElementById('fileInfo');
            if (this.files[0]) {
                fileInfo.textContent = `Fichier sélectionné: ${this.files[0].name}`;
            } else {
                fileInfo.textContent = 'Format accepté: PNG, JPG, JPEG (max 5Mo)';
            }
        });
    </script>
</body>
</html>
