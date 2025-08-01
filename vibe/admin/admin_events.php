<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if(!isset($_SESSION['prenom_nom']) || $_SESSION['role'] !== 'admin') {
    header('location: connexion.php');
    exit();
}

$connexion = getDBConnection();

// Gérer la suppression d'un événement
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_event = $connexion->prepare('DELETE FROM evenement WHERE id = ?');
    $delete_event->execute(array($delete_id));
    header('location: admin_events.php');
    exit();
}

// Gérer la validation d'un événement
if(isset($_POST['validate_event']) && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $update_event = $connexion->prepare('UPDATE evenement SET statut = "validé" WHERE id = ?');
    $update_event->execute(array($event_id));
    header('location: admin_events.php');
    exit();
}

// Récupérer tous les événements
$events = $connexion->query('SELECT e.*, u.prenom_nom as createur 
                           FROM evenement e 
                           JOIN utilisateur u ON e.id_createur = u.id 
                           ORDER BY e.date_creation DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements - Vibe Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .event-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4>Vibe Admin</h4>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_users.php">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_events.php">
                                <i class="fas fa-calendar-alt"></i> Événements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_messages.php">
                                <i class="fas fa-comments"></i> Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_reports.php">
                                <i class="fas fa-flag"></i> Signalements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="home.php">
                                <i class="fas fa-arrow-left"></i> Retour au site
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2>Gestion des Événements</h2>
                <hr>

                <!-- Table des événements -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Lieu</th>
                                        <th>Créateur</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($events as $event): ?>
                                    <tr>
                                        <td><?= $event['id'] ?></td>
                                        <td>
                                            <img src="upload_images/<?= $event['image'] ?>" alt="Image de l'événement" 
                                                 class="event-image">
                                        </td>
                                        <td><?= htmlspecialchars($event['titre']) ?></td>
                                        <td><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</td>
                                        <td><?= date('d/m/Y', strtotime($event['date'])) ?></td>
                                        <td><?= htmlspecialchars($event['lieu']) ?></td>
                                        <td><?= htmlspecialchars($event['createur']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $event['statut'] == 'validé' ? 'success' : 'warning' ?>">
                                                <?= $event['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin_event_details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($event['statut'] != 'validé'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                <button type="submit" name="validate_event" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <a href="?delete=<?= $event['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 