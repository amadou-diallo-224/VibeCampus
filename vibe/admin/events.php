<?php
require_once 'config_admin.php';
requireAdminLogin();

// Récupérer la liste des événements
$db = new PDO('mysql:host=localhost;dbname=vibe', 'root', '');
$stmt = $db->query("SELECT e.*, u.prenom_nom 
                    FROM evenements e 
                    JOIN utilisateur u ON e.createur_id = u.id 
                    ORDER BY e.date_evenement DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des événements - Administration</title>
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
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        /* Styles responsifs */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 15px;
            }
            .main-content {
                padding: 15px;
            }
            .table-responsive {
                margin: 0 -15px;
            }
            .event-image {
                width: 60px;
                height: 60px;
            }
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            .table td, .table th {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
            .table th {
                white-space: nowrap;
            }
            .table td:nth-child(2),
            .table td:nth-child(3),
            .table td:nth-child(4),
            .table td:nth-child(5),
            .table td:nth-child(6) {
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
        
        @media (max-width: 576px) {
            .event-image {
                width: 40px;
                height: 40px;
            }
            .table td, .table th {
                padding: 0.35rem;
                font-size: 0.8rem;
            }
            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
            .table td:nth-child(2),
            .table td:nth-child(3),
            .table td:nth-child(4),
            .table td:nth-child(5),
            .table td:nth-child(6) {
                max-width: 100px;
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
                <h1 class="mb-4">Gestion des événements</h1>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Créateur</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <img src="../upload_images/<?php echo htmlspecialchars($event['image']); ?>" 
                                         alt="Image de l'événement" 
                                         class="event-image">
                                </td>
                                <td><?php echo htmlspecialchars($event['titre']); ?></td>
                                <td><?php echo htmlspecialchars($event['prenom_nom']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($event['date_evenement'])); ?></td>
                                <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                                <td><?php echo htmlspecialchars($event['type_evenement']); ?></td>
                                <td>
                                    <a href="view_event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-danger"
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 