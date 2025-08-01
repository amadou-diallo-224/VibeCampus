<?php
require_once 'config_admin.php';
requireAdminLogin();

// Récupérer la liste des messages
$db = new PDO('mysql:host=localhost;dbname=vibe', 'root', '');
$stmt = $db->query("SELECT m.*, 
                    u1.prenom_nom as expediteur_nom,
                    u2.prenom_nom as destinataire_nom
                    FROM messages m
                    JOIN utilisateur u1 ON m.id_auteur = u1.id
                    JOIN utilisateur u2 ON m.id_destinataire = u2.id
                    ORDER BY m.date DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages de succès/erreur
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = 'Le message a été supprimé avec succès.';
}

if (isset($_GET['error'])) {
    $error_message = 'Une erreur est survenue lors de la suppression du message.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Administration</title>
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
        .message-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 15px;
            }
            .main-content {
                padding: 15px;
            }
            .message-content {
                max-width: 150px;
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
                        <a class="nav-link" href="events.php">
                            <i class="fas fa-calendar"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="messages.php">
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
                <h1 class="mb-4">Messages</h1>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

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
                                        <th>Date</th>
                                        <th>Lu</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($message['msg_id']); ?></td>
                                            <td><?php echo htmlspecialchars($message['expediteur_nom']); ?></td>
                                            <td><?php echo htmlspecialchars($message['destinataire_nom']); ?></td>
                                            <td class="message-content">
                                                <?php echo htmlspecialchars($message['msg']); ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($message['date'])); ?></td>
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-danger"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view_message.php?id=<?php echo $message['msg_id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="delete_message.php?id=<?php echo $message['msg_id']; ?>" 
                                                   class="btn btn-sm btn-danger"
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 