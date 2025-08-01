<?php
require_once '../config.php';
require_once 'config_admin.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$connexion = getDBConnection();
$db = $connexion;
$stmt = $db->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$_GET['id']]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_evenement = $_POST['date_evenement'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $type_evenement = $_POST['type_evenement'] ?? '';
    $contact_info = $_POST['contact_info'] ?? '';

    if (empty($titre) || empty($description) || empty($date_evenement) || 
        empty($lieu) || empty($type_evenement) || empty($contact_info)) {
        $error = 'Tous les champs sont obligatoires';
    } else {
        try {
            // Gestion de l'image
            $image = $event['image']; // Garder l'image existante par défaut
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
                    $upload_dir = '../upload_images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
                        if ($event['image'] !== 'default_event.jpg') {
                            unlink($upload_dir . $event['image']);
                        }
                        $image = $new_filename;
                    } else {
                        throw new Exception("Erreur lors du téléchargement de l'image");
                    }
                } else {
                    throw new Exception("Format d'image non supporté ou fichier trop volumineux (max 5MB)");
                }
            }

            $stmt = $db->prepare("UPDATE evenements SET 
                                titre = ?, description = ?, date_evenement = ?, 
                                lieu = ?, type_evenement = ?, contact_info = ?, 
                                image = ? WHERE id = ?");
            $stmt->execute([$titre, $description, $date_evenement, $lieu, 
                          $type_evenement, $contact_info, $image, $event['id']]);
            
            $success = 'Événement mis à jour avec succès';
            // Rafraîchir les données de l'événement
            $stmt = $db->prepare("SELECT * FROM evenements WHERE id = ?");
            $stmt->execute([$event['id']]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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
    <title>Modifier l'événement - Administration</title>
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
        .event-image {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 15px;
            }
            .main-content {
                padding: 15px;
            }
            .event-image {
                max-width: 100%;
                margin-bottom: 15px;
            }
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
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="events.php">
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
                    <h1>Modifier l'événement</h1>
                    <a href="events.php" class="btn btn-secondary">
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
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="titre" class="form-label">Titre</label>
                                        <input type="text" class="form-control" id="titre" name="titre" 
                                               value="<?php echo htmlspecialchars($event['titre']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="date_evenement" class="form-label">Date de l'événement</label>
                                        <input type="datetime-local" class="form-control" id="date_evenement" 
                                               name="date_evenement" 
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($event['date_evenement'])); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lieu" class="form-label">Lieu</label>
                                        <input type="text" class="form-control" id="lieu" name="lieu" 
                                               value="<?php echo htmlspecialchars($event['lieu']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="type_evenement" class="form-label">Type d'événement</label>
                                        <select class="form-control" id="type_evenement" name="type_evenement" required>
                                            <option value="soiree" <?php echo $event['type_evenement'] === 'soiree' ? 'selected' : ''; ?>>Soirée</option>
                                            <option value="concert" <?php echo $event['type_evenement'] === 'concert' ? 'selected' : ''; ?>>Concert</option>
                                            <option value="exposition" <?php echo $event['type_evenement'] === 'exposition' ? 'selected' : ''; ?>>Exposition</option>
                                            <option value="spectacle" <?php echo $event['type_evenement'] === 'spectacle' ? 'selected' : ''; ?>>Spectacle</option>
                                            <option value="cinema" <?php echo $event['type_evenement'] === 'cinema' ? 'selected' : ''; ?>>Cinéma</option>
                                            <option value="litteraire" <?php echo $event['type_evenement'] === 'litteraire' ? 'selected' : ''; ?>>Littéraire</option>
                                            <option value="conference" <?php echo $event['type_evenement'] === 'conference' ? 'selected' : ''; ?>>Conférence</option>
                                            <option value="sport" <?php echo $event['type_evenement'] === 'sport' ? 'selected' : ''; ?>>Sport</option>
                                            <option value="hackathon" <?php echo $event['type_evenement'] === 'hackathon' ? 'selected' : ''; ?>>Hackathon</option>
                                            <option value="tech" <?php echo $event['type_evenement'] === 'tech' ? 'selected' : ''; ?>>Tech</option>
                                            <option value="salon" <?php echo $event['type_evenement'] === 'salon' ? 'selected' : ''; ?>>Salon</option>
                                            <option value="formation" <?php echo $event['type_evenement'] === 'formation' ? 'selected' : ''; ?>>Formation</option>
                                            <option value="autre" <?php echo $event['type_evenement'] === 'autre' ? 'selected' : ''; ?>>Autre</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_info" class="form-label">Informations de contact</label>
                                        <input type="text" class="form-control" id="contact_info" name="contact_info" 
                                               value="<?php echo htmlspecialchars($event['contact_info']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image (laisser vide pour garder l'image actuelle)</label>
                                        <input type="file" class="form-control" id="image" name="image" 
                                               accept="image/jpeg,image/png,image/jpg">
                                        <small class="text-muted">Format accepté: JPG, PNG (max 5MB)</small>
                                    </div>
                                    <div class="mb-3">
                                        <img src="../upload_images/<?php echo htmlspecialchars($event['image']); ?>" 
                                             alt="Image actuelle" 
                                             class="event-image">
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