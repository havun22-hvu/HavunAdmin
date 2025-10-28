/**
 * PWA Install Prompt for Havun Admin
 * Shows a friendly prompt to install the app
 */

class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.init();
    }

    init() {
        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the default mini-infobar
            e.preventDefault();

            // Store the event for later use
            this.deferredPrompt = e;

            // Show the custom install prompt
            this.showInstallPrompt();
        });

        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            this.hideInstallPrompt();
            this.deferredPrompt = null;
        });

        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('App is running in standalone mode');
        }
    }

    showInstallPrompt() {
        // Check if user has dismissed before
        if (localStorage.getItem('pwaInstallDismissed')) {
            return;
        }

        // Create install banner
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.innerHTML = `
            <div style="
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
                color: white;
                padding: 16px;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: slideUp 0.3s ease-out;
            ">
                <div style="flex: 1; padding-right: 16px;">
                    <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;">
                        📱 Installeer Havun Admin
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">
                        Voeg toe aan je startscherm voor snelle toegang
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button id="pwa-install-btn" style="
                        background: white;
                        color: #4F46E5;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 6px;
                        font-weight: 600;
                        font-size: 14px;
                        cursor: pointer;
                        white-space: nowrap;
                    ">
                        Installeren
                    </button>
                    <button id="pwa-install-close" style="
                        background: transparent;
                        color: white;
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        padding: 10px 16px;
                        border-radius: 6px;
                        font-size: 14px;
                        cursor: pointer;
                    ">
                        ✕
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        // Install button click
        document.getElementById('pwa-install-btn').addEventListener('click', () => {
            this.installApp();
        });

        // Close button click
        document.getElementById('pwa-install-close').addEventListener('click', () => {
            this.dismissPrompt();
        });
    }

    async installApp() {
        if (!this.deferredPrompt) {
            return;
        }

        // Show the install prompt
        this.deferredPrompt.prompt();

        // Wait for the user's response
        const { outcome } = await this.deferredPrompt.userChoice;

        console.log(`User response: ${outcome}`);

        if (outcome === 'accepted') {
            console.log('User accepted the install prompt');
        } else {
            console.log('User dismissed the install prompt');
        }

        // Clear the prompt
        this.deferredPrompt = null;
        this.hideInstallPrompt();
    }

    dismissPrompt() {
        this.hideInstallPrompt();
        // Remember dismissal for 7 days
        localStorage.setItem('pwaInstallDismissed', Date.now() + (7 * 24 * 60 * 60 * 1000));
    }

    hideInstallPrompt() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                banner.remove();
            }, 300);
        }
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }

    @media (min-width: 768px) {
        #pwa-install-banner > div {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 24px !important;
        }
    }

    /* iOS Safari specific styles */
    @supports (-webkit-touch-callout: none) {
        #pwa-install-banner {
            padding-bottom: max(16px, env(safe-area-inset-bottom)) !important;
        }
    }
`;
document.head.appendChild(style);

// Initialize PWA installer
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new PWAInstaller();
    });
} else {
    new PWAInstaller();
}
