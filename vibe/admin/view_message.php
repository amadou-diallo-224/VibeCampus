<?php
require_once 'config_admin.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: messages.php');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=vibe', 'root', '');
$stmt = $db->prepare("SELECT m.*, 
                      u1.prenom_nom as expediteur_nom,
                      u2.prenom_nom as destinataire_nom
                      FROM messages m
                      JOIN utilisateur u1 ON m.id_auteur = u1.id
                      JOIN utilisateur u2 ON m.id_destinataire = u2.id
                      WHERE m.msg_id = ?");
$stmt->execute([$_GET['id']]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    header('Location: messages.php');
    exit();
}

// Marquer le message comme lu
$stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE msg_id = ?");
$stmt->execute([$_GET['id']]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du message - Administration</title>
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
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .file-preview {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 15px;
            }
            .main-content {
                padding: 15px;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Détails du message</h1>
                    <a href="messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informations générales</h5>
                                <table class="table">
                                    <tr>
                                        <th>ID:</th>
                                        <td><?php echo htmlspecialchars($message['msg_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Expéditeur:</th>
                                        <td><?php echo htmlspecialchars($message['expediteur_nom']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Destinataire:</th>
                                        <td><?php echo htmlspecialchars($message['destinataire_nom']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($message['date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Statut:</th>
                                        <td>
                                            <?php if ($message['is_read']): ?>
                                                <span class="badge bg-success">Lu</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Non lu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Contenu du message</h5>
                                <div class="message-content p-3 bg-light rounded">
                                    <?php echo nl2br(htmlspecialchars($message['msg'])); ?>
                                </div>

                                <?php if ($message['file_path']): ?>
                                    <h5 class="mt-4">Fichier joint</h5>
                                    <div class="mt-2">
                                        <?php if (strpos($message['file_type'], 'image/') === 0): ?>
                                            <img src="../<?php echo htmlspecialchars($message['file_path']); ?>" 
                                                 alt="Image jointe" 
                                                 class="file-preview">
                                        <?php elseif (strpos($message['file_type'], 'audio/') === 0): ?>
                                            <audio controls class="w-100">
                                                <source src="../<?php echo htmlspecialchars($message['file_path']); ?>" 
                                                        type="<?php echo htmlspecialchars($message['file_type']); ?>">
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                        <?php else: ?>
                                            <a href="../<?php echo htmlspecialchars($message['file_path']); ?>" 
                                               class="btn btn-primary" 
                                               download>
                                                <i class="fas fa-download"></i> Télécharger le fichier
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="delete_message.php?id=<?php echo $message['msg_id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
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