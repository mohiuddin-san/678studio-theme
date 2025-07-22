/**
 * Gallery JavaScript functionality
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.category-btn');
        const categories = document.querySelectorAll('.image-category');

        if (!buttons.length || !categories.length) return;

        // Add click event to category buttons
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');

                // Hide all categories
                categories.forEach(cat => {
                    cat.style.display = 'none';
                    cat.setAttribute('data-show', 'false');
                });

                // Show the selected category or all
                const selectedCategory = this.getAttribute('data-category');
                
                if (selectedCategory === 'all') {
                    categories.forEach(cat => {
                        cat.style.display = 'block';
                        cat.setAttribute('data-show', 'true');
                    });
                } else {
                    const targetCategory = document.querySelector(`.image-category[data-category="${selectedCategory}"]`);
                    if (targetCategory) {
                        targetCategory.style.display = 'block';
                        targetCategory.setAttribute('data-show', 'true');
                    }
                }
            });
        });

        // Trigger "All" button click on load
        const allButton = document.querySelector('.category-btn[data-category="all"]');
        if (allButton) {
            allButton.click();
        }

        // Optional: Add lazy loading enhancement for better performance
        const images = document.querySelectorAll('.gallery-item img');
        
        if ('loading' in HTMLImageElement.prototype) {
            // Browser supports native lazy loading
            images.forEach(img => {
                img.loading = 'lazy';
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            // You could implement Intersection Observer here if needed
        }
    });
})();