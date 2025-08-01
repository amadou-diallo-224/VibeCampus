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
$stmt = $db->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->execute([$_GET['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'] ?? '';
    $prenom_nom = $_POST['prenom_nom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $departement = $_POST['departement'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $nom_etablissement = $_POST['nom_etablissement'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($matricule) || empty($prenom_nom) || empty($sexe) || 
        empty($departement) || empty($niveau) || empty($nom_etablissement)) {
        $error = 'Tous les champs sont obligatoires';
    } else {
        try {
            if (!empty($password)) {
                $stmt = $db->prepare("UPDATE utilisateur SET 
                    matricule = ?, prenom_nom = ?, sexe = ?, 
                    departement = ?, niveau = ?, nom_etablissement = ?, 
                    mot_de_pass = ? WHERE id = ?");
                $stmt->execute([$matricule, $prenom_nom, $sexe, $departement, 
                              $niveau, $nom_etablissement, $password, $user['id']]);
            } else {
                $stmt = $db->prepare("UPDATE utilisateur SET 
                    matricule = ?, prenom_nom = ?, sexe = ?, 
                    departement = ?, niveau = ?, nom_etablissement = ? 
                    WHERE id = ?");
                $stmt->execute([$matricule, $prenom_nom, $sexe, $departement, 
                              $niveau, $nom_etablissement, $user['id']]);
            }
            $success = 'Utilisateur mis à jour avec succès';
            // Rafraîchir les données de l'utilisateur
            $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h3 class="mb-4">Administration</h3>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">
                            <i class="fas fa-calendar"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Modifier l'utilisateur</h1>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Informations personnelles</h5>
                                    <div class="mb-3">
                                        <label for="matricule" class="form-label">Matricule</label>
                                        <input type="text" class="form-control" id="matricule" name="matricule" 
                                               value="<?php echo htmlspecialchars($user['matricule']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="prenom_nom" class="form-label">Nom et Prénom</label>
                                        <input type="text" class="form-control" id="prenom_nom" name="prenom_nom" 
                                               value="<?php echo htmlspecialchars($user['prenom_nom']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sexe" class="form-label">Sexe</label>
                                        <select class="form-control" id="sexe" name="sexe" required>
                                            <option value="M" <?php echo $user['sexe'] === 'M' ? 'selected' : ''; ?>>Masculin</option>
                                            <option value="F" <?php echo $user['sexe'] === 'F' ? 'selected' : ''; ?>>Féminin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Informations académiques</h5>
                                    <div class="mb-3">
                                        <label for="departement" class="form-label">Département</label>
                                        <input type="text" class="form-control" id="departement" name="departement" 
                                               value="<?php echo htmlspecialchars($user['departement']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="niveau" class="form-label">Niveau</label>
                                        <input type="text" class="form-control" id="niveau" name="niveau" 
                                               value="<?php echo htmlspecialchars($user['niveau']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nom_etablissement" class="form-label">Établissement</label>
                                        <input type="text" class="form-control" id="nom_etablissement" name="nom_etablissement" 
                                               value="<?php echo htmlspecialchars($user['nom_etablissement']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 