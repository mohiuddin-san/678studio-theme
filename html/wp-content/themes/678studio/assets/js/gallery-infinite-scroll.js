/**
 * Gallery Infinite Scroll with GSAP Animation
 */
(function() {
    'use strict';

    // Check if GSAP is available
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
        console.error('GSAP or ScrollTrigger not loaded');
        return;
    }

    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);

    // Configuration
    const CONFIG = {
        perPage: 8,           // Images per load
        threshold: 0.1,       // Intersection observer threshold
        debounceDelay: 300,   // Debounce delay for scroll events
        animationDuration: 0.8,
        staggerDelay: 0.1,
        maxRetries: 3
    };

    // State management
    let state = {
        currentPage: 1,
        loading: false,
        hasMore: true,
        totalImages: 0,
        loadedImages: 8, // Initial images count
        retryCount: 0,
        currentCategory: 'all',
        currentStudio: 'all'
    };

    // DOM elements
    const elements = {
        container: null,
        loading: null,
        endIndicator: null,
        categoryFilter: null,
        studioFilter: null
    };

    /**
     * Initialize the gallery infinite scroll
     */
    function init() {
        // Get DOM elements
        elements.container = document.getElementById('gallery-container');
        elements.loading = document.getElementById('gallery-loading');
        elements.endIndicator = document.getElementById('gallery-end');
        elements.categoryFilter = document.getElementById('category-filter');
        elements.studioFilter = document.getElementById('studio-filter');

        if (!elements.container || !elements.loading) {
            console.error('Required gallery elements not found');
            return;
        }

        // Setup initial animations for existing items
        animateInitialItems();

        // Setup intersection observer for infinite scroll
        setupIntersectionObserver();

        // Setup filter change handlers
        setupFilterHandlers();

        // Setup lightbox for dynamically loaded images
        setupDynamicLightbox();

        console.log('Gallery infinite scroll initialized');
    }

    /**
     * Animate initial gallery items on page load
     */
    function animateInitialItems() {
        const initialItems = elements.container.querySelectorAll('.gallery-grid__item');
        
        if (initialItems.length === 0) return;

        // Set initial state
        gsap.set(initialItems, {
            opacity: 0,
            y: 30,
            scale: 0.9
        });

        // Animate items in with stagger
        gsap.to(initialItems, {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: CONFIG.animationDuration,
            stagger: CONFIG.staggerDelay,
            ease: "power2.out"
        });
    }

    /**
     * Setup intersection observer for infinite scroll trigger
     */
    function setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !state.loading && state.hasMore) {
                    loadMoreImages();
                }
            });
        }, {
            threshold: CONFIG.threshold,
            rootMargin: '100px'
        });

        observer.observe(elements.loading);
    }

    /**
     * Setup filter change handlers
     */
    function setupFilterHandlers() {
        if (elements.categoryFilter) {
            elements.categoryFilter.addEventListener('change', handleFilterChange);
        }

        if (elements.studioFilter) {
            elements.studioFilter.addEventListener('change', handleFilterChange);
        }
    }

    /**
     * Handle filter changes
     */
    function handleFilterChange() {
        const newCategory = elements.categoryFilter ? elements.categoryFilter.value : 'all';
        const newStudio = elements.studioFilter ? elements.studioFilter.value : 'all';

        // Check if filters actually changed
        if (newCategory === state.currentCategory && newStudio === state.currentStudio) {
            return;
        }

        // Update state
        state.currentCategory = newCategory;
        state.currentStudio = newStudio;
        state.currentPage = 1;
        state.hasMore = true;
        state.loadedImages = 0;

        // Clear existing items and reload
        clearGallery();
        loadMoreImages();
    }

    /**
     * Clear gallery items
     */
    function clearGallery() {
        const items = elements.container.querySelectorAll('.gallery-grid__item');
        
        if (items.length === 0) return;

        // Animate out existing items
        gsap.to(items, {
            opacity: 0,
            y: -20,
            scale: 0.9,
            duration: 0.3,
            stagger: 0.02,
            ease: "power2.in",
            onComplete: () => {
                items.forEach(item => item.remove());
            }
        });
    }

    /**
     * Load more images via AJAX
     */
    function loadMoreImages() {
        if (state.loading || !state.hasMore) return;

        state.loading = true;
        showLoading();

        const formData = new FormData();
        formData.append('action', 'gallery_load_more');
        formData.append('page', state.currentPage);
        formData.append('per_page', CONFIG.perPage);
        formData.append('category', state.currentCategory);
        formData.append('studio', state.currentStudio);
        formData.append('nonce', galleryAjax.nonce);

        fetch(galleryAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                handleLoadSuccess(data.data);
            } else {
                handleLoadError(data.data ? data.data.message : 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            handleLoadError('Network error occurred');
        });
    }

    /**
     * Handle successful image load
     */
    function handleLoadSuccess(data) {
        state.loading = false;
        hideLoading();

        if (!data.images_html || data.loaded_count === 0) {
            // No more images to load
            state.hasMore = false;
            showEndIndicator();
            return;
        }

        // Create temporary container for new items
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = data.images_html;
        const newItems = Array.from(tempDiv.querySelectorAll('.gallery-grid__item'));

        // Add new items to container
        newItems.forEach(item => {
            elements.container.appendChild(item);
        });

        // Animate new items
        animateNewItems(newItems);

        // Update state
        state.currentPage++;
        state.loadedImages += data.loaded_count;
        state.totalImages = data.total_images;
        state.hasMore = data.has_more;
        state.retryCount = 0;

        // Setup lightbox for new images
        setupLightboxForItems(newItems);

        // Show end indicator if no more items
        if (!state.hasMore) {
            showEndIndicator();
        }

        console.log(`Loaded ${data.loaded_count} images. Total: ${state.loadedImages}/${state.totalImages}`);
    }

    /**
     * Handle load error
     */
    function handleLoadError(message) {
        state.loading = false;
        hideLoading();

        console.error('Load error:', message);

        // Retry logic
        if (state.retryCount < CONFIG.maxRetries) {
            state.retryCount++;
            console.log(`Retrying... (${state.retryCount}/${CONFIG.maxRetries})`);
            setTimeout(() => {
                loadMoreImages();
            }, 1000 * state.retryCount);
        } else {
            // Show error message
            showErrorMessage();
        }
    }

    /**
     * Animate new items into view
     */
    function animateNewItems(items) {
        if (items.length === 0) return;

        // Set initial state
        gsap.set(items, {
            opacity: 0,
            y: 50,
            scale: 0.8
        });

        // Animate items in with stagger
        gsap.to(items, {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: CONFIG.animationDuration,
            stagger: CONFIG.staggerDelay,
            ease: "power2.out"
        });
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        if (elements.loading) {
            elements.loading.classList.add('gallery-loading--visible');
        }
    }

    /**
     * Hide loading indicator
     */
    function hideLoading() {
        if (elements.loading) {
            elements.loading.classList.remove('gallery-loading--visible');
        }
    }

    /**
     * Show end indicator
     */
    function showEndIndicator() {
        if (elements.endIndicator) {
            elements.endIndicator.style.display = 'flex';
            
            // Animate in
            gsap.fromTo(elements.endIndicator, 
                { opacity: 0, y: 20 },
                { 
                    opacity: 1, 
                    y: 0, 
                    duration: 0.5, 
                    ease: "power2.out" 
                }
            );
        }
    }

    /**
     * Show error message
     */
    function showErrorMessage() {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'gallery-error';
        errorDiv.innerHTML = `
            <p>画像の読み込みに失敗しました。</p>
            <button onclick="location.reload()">ページを再読み込み</button>
        `;
        
        if (elements.loading) {
            elements.loading.parentNode.insertBefore(errorDiv, elements.loading.nextSibling);
        }
    }

    /**
     * Setup lightbox for dynamically loaded images
     */
    function setupDynamicLightbox() {
        // Delegate click events for dynamically added images
        elements.container.addEventListener('click', function(e) {
            const clickedItem = e.target.closest('.gallery-grid__item');
            if (clickedItem) {
                const img = clickedItem.querySelector('img');
                if (img) {
                    const fullImageUrl = img.getAttribute('data-full-image') || img.src;
                    openLightbox(fullImageUrl, img.alt);
                }
            }
        });
    }

    /**
     * Setup lightbox for specific items
     */
    function setupLightboxForItems(items) {
        // This will be handled by the delegated event listener
        // No additional setup needed
    }

    /**
     * Open lightbox (using existing lightbox functionality)
     */
    function openLightbox(imageUrl, alt) {
        const lightbox = document.getElementById('galleryLightbox');
        if (lightbox) {
            const lightboxImage = lightbox.querySelector('.lightbox__image');
            if (lightboxImage) {
                lightboxImage.src = imageUrl;
                lightboxImage.alt = alt;
                lightbox.classList.add('lightbox--active');
            }
        }
    }

    /**
     * Debounce function for performance
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Refresh ScrollTrigger on window resize
    window.addEventListener('resize', debounce(() => {
        ScrollTrigger.refresh();
    }, CONFIG.debounceDelay));

})();