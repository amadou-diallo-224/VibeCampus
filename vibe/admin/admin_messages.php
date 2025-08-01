<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if(!isset($_SESSION['prenom_nom']) || $_SESSION['role'] !== 'admin') {
    header('location: connexion.php');
    exit();
}

$connexion = getDBConnection();

// Gérer la suppression d'un message
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_message = $connexion->prepare('DELETE FROM messages WHERE msg_id = ?');
    $delete_message->execute(array($delete_id));
    header('location: admin_messages.php');
    exit();
}

// Récupérer tous les messages
$messages = $connexion->query('
    SELECT m.*, 
           u1.prenom_nom as auteur, 
           u2.prenom_nom as destinataire 
    FROM messages m 
    JOIN utilisateur u1 ON m.id_auteur = u1.id 
    JOIN utilisateur u2 ON m.id_destinataire = u2.id 
    ORDER BY m.date DESC
')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Vibe Admin</title>
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
        .message-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                            <a class="nav-link" href="admin_events.php">
                                <i class="fas fa-calendar-alt"></i> Événements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_messages.php">
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
                <h2>Gestion des Messages</h2>
                <hr>

                <!-- Table des messages -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Expéditeur</th>
                                        <th>Destinataire</th>
                                        <th>Message</th>
                                        <th>Fichier</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $message): ?>
                                    <tr>
                                        <td><?= $message['msg_id'] ?></td>
                                        <td><?= htmlspecialchars($message['auteur']) ?></td>
                                        <td><?= htmlspecialchars($message['destinataire']) ?></td>
                                        <td class="message-content"><?= htmlspecialchars($message['msg']) ?></td>
                                        <td>
                                            <?php if($message['file_path']): ?>
                                                <?php if(strpos($message['file_type'], 'image/') === 0): ?>
                                                    <img src="<?= $message['file_path'] ?>" alt="Image" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php elseif(strpos($message['file_type'], 'audio/') === 0): ?>
                                                    <audio controls style="width: 150px;">
                                                        <source src="<?= $message['file_path'] ?>" type="<?= $message['file_type'] ?>">
                                                    </audio>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($message['date'])) ?></td>
                                        <td>
                                            <a href="admin_message_details.php?id=<?= $message['msg_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?delete=<?= $message['msg_id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
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