<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Vibe - La première plateforme de communication pour les étudiants guinéens</title>
    <meta name="description" content="Vibe est la première plateforme de communication dédiée aux étudiants guinéens. Connectez-vous, discutez avec vos camarades et partagez des événements universitaires, formations et soirées étudiantes.">
    <meta name="keywords" content="communication étudiante guinée, chat étudiant guinée, réseau social étudiant guinéen, événements universitaires guinée, formations étudiantes guinée, soirées étudiantes guinée, communauté étudiante guinéenne">
    <meta name="author" content="Vibe">
    <meta property="og:title" content="Vibe - La première plateforme de communication pour les étudiants guinéens">
    <meta property="og:description" content="Vibe est la première plateforme de communication dédiée aux étudiants guinéens. Connectez-vous, discutez avec vos camarades et partagez des événements universitaires, formations et soirées étudiantes.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://univ-vibe.com/">
    <meta property="og:image" content="http://univ-vibe.com/assets/logo.png">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Vibe">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Vibe - La première plateforme de communication pour les étudiants guinéens">
    <meta name="twitter:description" content="Vibe est la première plateforme de communication dédiée aux étudiants guinéens. Connectez-vous, discutez avec vos camarades et partagez des événements universitaires, formations et soirées étudiantes.">
    <meta name="twitter:image" content="http://univ-vibe.com/assets/logo.png">
    <meta name="twitter:site" content="@vibe_guinee">
    <meta name="twitter:creator" content="@vibe_guinee">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .splash-container {
            text-align: center;
        }
        .logo {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        .loading-text {
            color: #333;
            font-size: 18px;
            margin-top: 20px;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <img src="assets/logo.png" alt="Vibe Logo" class="logo">
        <div class="loading-text">Bienvenu chez VibeCampus</div>
    </div>

    <script src="script.js"></script>
    <script>
        // Redirection vers la page de connexion après 2 secondes
        setTimeout(function() {
            window.location.href = "connexion.php";
        }, 2000);
    </script>
</body>
</html> 