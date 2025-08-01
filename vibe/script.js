// Gestionnaire d'événements pour le service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker enregistré avec succès');
            })
            .catch(error => {
                console.log('Échec de l\'enregistrement du ServiceWorker:', error);
            });
    });
}

// Gestionnaire pour l'installation de l'application
let deferredPrompt;

// Vérifier si l'application peut être installée
if (window.matchMedia('(display-mode: standalone)').matches) {
    // L'application est déjà installée
    console.log('Application déjà installée');
} else {
    window.addEventListener('beforeinstallprompt', (e) => {
        // Empêcher Chrome de montrer automatiquement la boîte de dialogue
        e.preventDefault();
        
        // Stocker l'événement pour l'utiliser plus tard
        deferredPrompt = e;
        
        // Créer le bouton d'installation
        const installButton = document.createElement('button');
        installButton.textContent = 'Installer Vibe';
        installButton.className = 'btn btn-primary';
        installButton.style.position = 'fixed';
        installButton.style.bottom = '20px';
        installButton.style.right = '20px';
        installButton.style.zIndex = '1000';
        installButton.style.padding = '10px 20px';
        installButton.style.borderRadius = '5px';
        installButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
        
        installButton.addEventListener('click', async () => {
            // Afficher la boîte de dialogue d'installation
            deferredPrompt.prompt();
            
            // Attendre que l'utilisateur réponde
            const { outcome } = await deferredPrompt.userChoice;
            
            // Nettoyer
            deferredPrompt = null;
            installButton.remove();
            
            if (outcome === 'accepted') {
                console.log('L\'utilisateur a accepté l\'installation');
            } else {
                console.log('L\'utilisateur a refusé l\'installation');
            }
        });
        
        // Ajouter le bouton au DOM
        document.body.appendChild(installButton);
    });
}

// Vérifier si l'application est déjà installée
window.addEventListener('load', () => {
    if (window.matchMedia('(display-mode: standalone)').matches) {
        // L'application est déjà installée
        console.log('Application déjà installée');
    }
});
