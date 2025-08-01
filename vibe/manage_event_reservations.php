<?php
session_start();
require_once 'config.php';

$connexion = getDBConnection();

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['id'])) {
    header('location: connexion.php');
    exit;
}

// Vérifier si on a un ID d'événement
if(!isset($_GET['event_id'])) {
    header('location: home.php');
    exit;
}

$event_id = intval($_GET['event_id']);

// Vérifier si l'utilisateur est le créateur de l'événement
$check_creator = $connexion->prepare('SELECT createur_id, titre FROM evenements WHERE id = ?');
$check_creator->execute([$event_id]);
$event = $check_creator->fetch();

if(!$event || $event['createur_id'] != $_SESSION['id']) {
    header('location: home.php');
    exit;
}

// Récupérer les réservations pour cet événement
$reservations = $connexion->prepare('SELECT r.*, u.prenom_nom, u.image FROM reservations r JOIN utilisateur u ON r.utilisateur_id = u.id WHERE r.evenement_id = ? ORDER BY r.date_reservation DESC');
$reservations->execute([$event_id]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations - <?= htmlspecialchars($event['titre']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des réservations - <?= htmlspecialchars($event['titre']) ?></h2>
            <div>
                <a href="home.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Retour</a>
                <button class="btn btn-primary" onclick="startScan()">
                    <i class="fas fa-qrcode"></i> Scanner QR code
                </button>
            </div>
        </div>

        <!-- Conteneur pour le scanner QR -->
        <div id="scanner-container" style="display: none; margin-top: 20px;">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h5>Scanner le QR code du participant</h5>
                        <button class="btn btn-danger" onclick="stopScan()">Arrêter le scanner</button>
                    </div>
                    <div id="preview" style="width: 100%; height: 300px; background: #000; margin-bottom: 15px;"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Liste des réservations</h4>
                
                <?php if($reservations->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Date de réservation</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($reservation = $reservations->fetch()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="upload_images/<?= htmlspecialchars($reservation['image']) ?>" 
                                                 alt="<?= htmlspecialchars($reservation['prenom_nom']) ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 35px; height: 35px; object-fit: cover;">
                                            <span><?= htmlspecialchars($reservation['prenom_nom']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= $reservation['date_reservation']->format('d/m/Y H:i') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $reservation['statut'] === 'confirme' ? 'success' : ($reservation['statut'] === 'annule' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($reservation['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($reservation['statut'] === 'en_attente'): ?>
                                            <button class="btn btn-sm btn-success me-2" onclick="changeStatus(<?= $reservation['id'] ?>, 'confirme')">
                                                <i class="fas fa-check"></i> Confirmer
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="changeStatus(<?= $reservation['id'] ?>, 'annule')">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucune réservation pour cet événement.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script>
        let scanner = null;
        let currentEventId = <?= $event_id ?>;

        function startScan() {
            scanner = new Instascan.Scanner({
                video: document.getElementById('preview'),
                scanPeriod: 5,
                mirror: false
            });

            scanner.addListener('scan', function(content) {
                // Le contenu du QR code devrait être le matricule de l'utilisateur
                checkReservation(content);
            });

            Instascan.Camera.getCameras().then(function(cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                    document.getElementById('scanner-container').style.display = 'block';
                } else {
                    alert('Aucune caméra trouvée sur votre appareil.');
                }
            }).catch(function(e) {
                console.error(e);
                alert('Erreur lors de l\'accès à la caméra: ' + e.message);
            });
        }

        function checkReservation(matricule) {
            // Rechercher la réservation correspondante
            fetch('check_reservation.php?matricule=' + matricule + '&event_id=' + currentEventId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Afficher un message de confirmation
                        alert('Réservation trouvée pour ' + data.user.prenom_nom + '. Statut: ' + data.reservation.statut);
                        // Mettre à jour l'interface si nécessaire
                        window.location.reload();
                    } else {
                        alert('Aucune réservation trouvée pour ce matricule.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la vérification de la réservation.');
                });
        }

        function stopScan() {
            if (scanner) {
                scanner.stop();
            }
            document.getElementById('scanner-container').style.display = 'none';
        }

        function changeStatus(reservationId, newStatus) {
            if(confirm('Êtes-vous sûr de vouloir changer le statut de cette réservation ?')) {
                window.location.href = 'update_reservation_status.php?id=' + reservationId + '&status=' + newStatus;
            }
        }
    </script>
</body>
</html>
