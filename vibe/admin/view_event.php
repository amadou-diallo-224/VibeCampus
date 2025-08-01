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
$stmt = $db->prepare("SELECT e.*, u.prenom_nom 
                      FROM evenements e 
                      JOIN utilisateur u ON e.createur_id = u.id 
                      WHERE e.id = ?");
$stmt->execute([$_GET['id']]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'événement - Administration</title>
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
            max-width: 100%;
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
                    <h1>Détails de l'événement</h1>
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <img src="../upload_images/<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="Image de l'événement" 
                                 class="event-image">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informations générales</h5>
                                <table class="table">
                                    <tr>
                                        <th>Titre:</th>
                                        <td><?php echo htmlspecialchars($event['titre']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Créateur:</th>
                                        <td><?php echo htmlspecialchars($event['prenom_nom']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($event['date_evenement'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lieu:</th>
                                        <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Type:</th>
                                        <td><?php echo htmlspecialchars($event['type_evenement']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Description</h5>
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                
                                <h5 class="mt-4">Contact</h5>
                                <p><?php echo htmlspecialchars($event['contact_info']); ?></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 