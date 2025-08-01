<?php
require_once '../config.php';
require_once 'config_admin.php';
requireAdminLogin();

// Récupérer les statistiques
$connexion = getDBConnection();
$db = $connexion;

// Supprimer l'ancienne table si elle existe et créer la nouvelle
try {
    $db->exec("DROP TABLE IF EXISTS site_visits");
    $db->exec("CREATE TABLE site_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_date DATE,
        visit_time TIME,
        ip_address VARCHAR(45),
        browser VARCHAR(255),
        page_url VARCHAR(255),
        referrer VARCHAR(255),
        visit_count INT DEFAULT 1,
        INDEX (visit_date)
    )");
} catch (PDOException $e) {
    die("Erreur lors de la création de la table : " . $e->getMessage());
}

// Récupérer les informations du visiteur
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$page_url = $_SERVER['REQUEST_URI'];
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';

// Mettre à jour le compteur de visites avec plus de détails
$update_visits = $db->prepare("INSERT INTO site_visits (visit_date, visit_time, ip_address, browser, page_url, referrer) 
                             VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?)");
$update_visits->execute([$ip, $browser, $page_url, $referrer]);

// Récupérer les statistiques
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
    'events' => $db->query("SELECT COUNT(*) FROM evenements")->fetchColumn(),
    'messages' => $db->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
    'visits' => $db->query("SELECT COUNT(*) FROM site_visits")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .stat-card i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
        }
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .stat-card p {
            margin: 0;
            font-size: 1.1rem;
        }
        .stat-card.users { background: #007bff; }
        .stat-card.events { background: #28a745; }
        .stat-card.messages { background: #17a2b8; }
        .stat-card.visits { background: #6f42c1; }
        .visits-details {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .visits-details h3 {
            color: #343a40;
            margin-bottom: 20px;
        }
        .visits-details .table th {
            background: #f8f9fa;
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
                        <a class="nav-link active" href="index.php">
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
                <h1 class="mb-4">Tableau de bord</h1>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card users">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $stats['users']; ?></h3>
                            <p>Utilisateurs</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card events">
                            <i class="fas fa-calendar-alt"></i>
                            <h3><?php echo $stats['events']; ?></h3>
                            <p>Événements</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card messages">
                            <i class="fas fa-envelope"></i>
                            <h3><?php echo $stats['messages']; ?></h3>
                            <p>Messages</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card visits">
                            <i class="fas fa-eye"></i>
                            <h3><?php echo $stats['visits']; ?></h3>
                            <p>Visites</p>
                        </div>
                    </div>
                </div>

                <!-- Section Détails des visites -->
                <div class="visits-details">
                    <h3><i class="fas fa-chart-line me-2"></i>Détails des visites</h3>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-calendar-day me-2"></i>Visites aujourd'hui</h5>
                                    <p class="card-text display-4"><?php echo $db->query("SELECT COUNT(*) FROM site_visits WHERE visit_date = CURDATE()")->fetchColumn(); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-user-friends me-2"></i>Visiteurs uniques</h5>
                                    <p class="card-text display-4"><?php echo $db->query("SELECT COUNT(DISTINCT ip_address) FROM site_visits")->fetchColumn(); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>Pages vues</h5>
                                    <p class="card-text display-4"><?php echo $db->query("SELECT COUNT(*) FROM site_visits")->fetchColumn(); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar me-2"></i>Date</th>
                                    <th><i class="fas fa-clock me-2"></i>Heure</th>
                                    <th><i class="fas fa-network-wired me-2"></i>IP</th>
                                    <th><i class="fas fa-window-maximize me-2"></i>Navigateur</th>
                                    <th><i class="fas fa-link me-2"></i>Page</th>
                                    <th><i class="fas fa-external-link-alt me-2"></i>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_visits = $db->query("SELECT * FROM site_visits ORDER BY visit_date DESC, visit_time DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($recent_visits as $visit):
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($visit['visit_date'])); ?></td>
                                    <td><?php echo date('H:i:s', strtotime($visit['visit_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($visit['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($visit['browser'], 0, 30)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars(substr($visit['page_url'], 0, 30)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars(substr($visit['referrer'], 0, 30)) . '...'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 