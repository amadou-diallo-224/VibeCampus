<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if(!isset($_SESSION['prenom_nom']) || $_SESSION['role'] !== 'admin') {
    header('location: connexion.php');
    exit();
}

$connexion = getDBConnection();

// Gérer la suppression d'un utilisateur
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_user = $connexion->prepare('DELETE FROM utilisateur WHERE id = ?');
    $delete_user->execute(array($delete_id));
    header('location: admin_users.php');
    exit();
}

// Gérer le changement de rôle
if(isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    $update_role = $connexion->prepare('UPDATE utilisateur SET role = ? WHERE id = ?');
    $update_role->execute(array($new_role, $user_id));
    header('location: admin_users.php');
    exit();
}

// Récupérer tous les utilisateurs
$users = $connexion->query('SELECT * FROM utilisateur ORDER BY date_inscription DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Vibe Admin</title>
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
                            <a class="nav-link active" href="admin_users.php">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_events.php">
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
                <h2>Gestion des Utilisateurs</h2>
                <hr>

                <!-- Table des utilisateurs -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Matricule</th>
                                        <th>Département</th>
                                        <th>Niveau</th>
                                        <th>Rôle</th>
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <img src="upload_images/<?= $user['image'] ?>" alt="Photo de profil" 
                                                 style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td><?= htmlspecialchars($user['prenom_nom']) ?></td>
                                        <td><?= htmlspecialchars($user['matricule']) ?></td>
                                        <td><?= htmlspecialchars($user['departement']) ?></td>
                                        <td><?= htmlspecialchars($user['niveau']) ?></td>
                                        <td>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <select name="new_role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                                </select>
                                                <input type="hidden" name="change_role" value="1">
                                            </form>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                        <td>
                                            <a href="admin_user_details.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
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