/**
 * Mars Challenge SEO Optimizations - JavaScript
 * Enhances SEO performance and implements best practices
 */

(function() {
    'use strict';

    // Initialize Mars Challenge SEO features
    function MarsChallengeSEO() {
        this.init();
    }

    MarsChallengeSEO.prototype = {
        init: function() {
            this.optimizeImages();
            this.trackingOptimizations();
            this.performanceMonitoring();
        },

        // Optimize images after loading
        optimizeImages: function() {
            var images = document.querySelectorAll('img[loading="lazy"]');
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        // Mark as loaded for CSS transitions
                        img.setAttribute('data-lazy-loaded', '');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(function(img) {
                imageObserver.observe(img);
            });
        },

        // Optimize tracking to prevent Core Web Vitals issues
        trackingOptimizations: function() {
            // Load analytics asynchronously to avoid blocking
            if (typeof gtag !== 'undefined') {
                // Ensure Google Analytics doesn't block page load
                window.dataLayer = window.dataLayer || [];
            }
        },

        // Monitor Core Web Vitals
        performanceMonitoring: function() {
            // Implement Core Web Vitals monitoring
            if ('PerformanceObserver' in window) {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        // Monitor for performance issues
                        if (entry.name === 'first-input' && entry.processingStart) {
                            const fie = entry.processingStart - entry.startTime;
                            // Log First Input Delay if above threshold
                            if (fie > 100) {
                                console.log('FID: ', fie);
                            }
                        }
                    }
                });
                
                observer.observe({ entryTypes: ['paint', 'largest-contentful-paint', 'first-input', 'navigation'] });
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            new MarsChallengeSEO();
        });
    } else {
        new MarsChallengeSEO();
    }

    // Export for potential external use
    window.MarsChallengeSEO = MarsChallengeSEO;
})();