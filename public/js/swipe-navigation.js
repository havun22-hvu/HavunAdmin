/**
 * Swipe Navigation for Mobile
 * Allows users to swipe left/right to navigate between pages
 */

class SwipeNavigation {
    constructor() {
        this.touchStartX = 0;
        this.touchEndX = 0;
        this.touchStartY = 0;
        this.touchEndY = 0;
        this.minSwipeDistance = 50; // Minimum distance for swipe
        this.maxVerticalDistance = 100; // Maximum vertical movement allowed

        // Define navigation routes in order
        this.routes = [
            { name: 'Dashboard', url: '/dashboard' },
            { name: 'Inkomsten', url: '/invoices' },
            { name: 'Uitgaven', url: '/expenses' },
            { name: 'Projecten', url: '/projects' },
            { name: 'Rapportages', url: '/reports' },
            { name: 'Sync', url: '/sync' },
            { name: 'Reconciliatie', url: '/reconciliation' }
        ];

        this.currentRouteIndex = this.getCurrentRouteIndex();
        this.init();
    }

    getCurrentRouteIndex() {
        const currentPath = window.location.pathname;
        const index = this.routes.findIndex(route => currentPath.startsWith(route.url));
        return index >= 0 ? index : 0;
    }

    init() {
        // Only enable on mobile devices (screen width < 768px)
        if (window.innerWidth >= 768) {
            return;
        }

        document.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
        document.addEventListener('touchend', (e) => this.handleTouchEnd(e), { passive: true });

        // Show swipe indicator on first load
        this.showSwipeIndicator();
    }

    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
        this.touchStartY = e.changedTouches[0].screenY;
    }

    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        this.touchEndY = e.changedTouches[0].screenY;
        this.handleSwipe();
    }

    handleSwipe() {
        const horizontalDistance = this.touchEndX - this.touchStartX;
        const verticalDistance = Math.abs(this.touchEndY - this.touchStartY);

        // Check if vertical movement is too much (likely scrolling)
        if (verticalDistance > this.maxVerticalDistance) {
            return;
        }

        // Swipe left (next page)
        if (horizontalDistance < -this.minSwipeDistance) {
            this.navigateNext();
        }

        // Swipe right (previous page)
        if (horizontalDistance > this.minSwipeDistance) {
            this.navigatePrevious();
        }
    }

    navigateNext() {
        if (this.currentRouteIndex < this.routes.length - 1) {
            const nextRoute = this.routes[this.currentRouteIndex + 1];
            this.showTransition('left', nextRoute.name);
            setTimeout(() => {
                window.location.href = nextRoute.url;
            }, 200);
        }
    }

    navigatePrevious() {
        if (this.currentRouteIndex > 0) {
            const prevRoute = this.routes[this.currentRouteIndex - 1];
            this.showTransition('right', prevRoute.name);
            setTimeout(() => {
                window.location.href = prevRoute.url;
            }, 200);
        }
    }

    showTransition(direction, pageName) {
        const transition = document.createElement('div');
        transition.className = 'swipe-transition';
        transition.textContent = pageName;
        transition.style.cssText = `
            position: fixed;
            top: 50%;
            ${direction}: 20px;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            z-index: 9999;
            animation: swipeFade 0.3s ease-out;
        `;

        document.body.appendChild(transition);

        setTimeout(() => {
            transition.remove();
        }, 300);
    }

    showSwipeIndicator() {
        // Check if user has seen the indicator before
        if (localStorage.getItem('swipeIndicatorSeen')) {
            return;
        }

        const indicator = document.createElement('div');
        indicator.className = 'swipe-indicator';
        indicator.innerHTML = `
            <div style="
                position: fixed;
                bottom: 80px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(99, 102, 241, 0.95);
                color: white;
                padding: 12px 24px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 500;
                z-index: 9999;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: swipePulse 2s ease-in-out infinite;
                text-align: center;
            ">
                👈 Swipe om te navigeren 👉
            </div>
        `;

        document.body.appendChild(indicator);

        // Remove after 3 seconds
        setTimeout(() => {
            indicator.style.animation = 'swipeFadeOut 0.5s ease-out forwards';
            setTimeout(() => {
                indicator.remove();
            }, 500);
        }, 3000);

        // Mark as seen
        localStorage.setItem('swipeIndicatorSeen', 'true');
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes swipeFade {
        from {
            opacity: 0;
            transform: translateY(-50%) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateY(-50%) scale(1);
        }
    }

    @keyframes swipePulse {
        0%, 100% {
            transform: translateX(-50%) scale(1);
        }
        50% {
            transform: translateX(-50%) scale(1.05);
        }
    }

    @keyframes swipeFadeOut {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
    }

    /* Disable text selection during swipe */
    body.swiping {
        user-select: none;
        -webkit-user-select: none;
    }
`;
document.head.appendChild(style);

// Initialize swipe navigation when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new SwipeNavigation();
    });
} else {
    new SwipeNavigation();
}
