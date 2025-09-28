/**
 * Studio Hero Slider Initialization
 * Using Splide.js for image gallery slider on studio detail pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if Splide is available
    if (typeof Splide === 'undefined') {
        console.warn('Splide.js is not loaded');
        return;
    }

    const sliderElement = document.getElementById('studio-gallery-slider');

    if (!sliderElement) {
        return; // No slider found, exit gracefully
    }

    // Check if slider has slides
    const slides = sliderElement.querySelectorAll('.splide__slide');
    if (slides.length === 0) {
        console.warn('No slides found in studio gallery slider');
        return;
    }

    // Initialize Splide slider with nii-photo.com configuration
    const slider = new Splide('#studio-gallery-slider', {
        // Core settings matching nii-photo.com
        autoplay: true,
        type: "loop",
        pauseOnHover: false,
        pauseOnFocus: false,
        interval: 5400,        // Same as nii-photo.com
        speed: 2400,           // Same as nii-photo.com

        // Navigation
        arrows: false,
        pagination: true,

        // Layout settings (key differences!)
        padding: "28%",        // Large left/right padding
        gap: "20%",            // Large gap between slides
        autoHeight: false,     // Use CSS height instead

        // Behavior
        keyboard: true,
        drag: true,

        // Performance
        lazyLoad: 'nearby',
        preloadPages: 2,

        // Accessibility
        live: true,

        // Responsive settings matching nii-photo.com
        breakpoints: {
            768: {
                padding: "16px",
                gap: "16px",
                // Mobile touch optimization
                dragMinThreshold: {
                    mouse: 0,
                    touch: 40 // Require more horizontal movement to trigger slide
                },
                flickPower: 300, // Reduce flick sensitivity
                flickMaxPages: 1 // Limit flick to one page
            }
        }
    });

    // Handle slider events with nii-photo.com autoplay behavior
    slider.on('mounted', function() {
        console.log('Studio gallery slider mounted successfully');

        // Stop autoplay initially (like nii-photo.com)
        const autoplay = slider.Components.Autoplay;
        if (autoplay) {
            autoplay.pause();
        }

        // Add accessibility improvements
        const track = sliderElement.querySelector('.splide__track');
        if (track) {
            track.setAttribute('tabindex', '0');
            track.setAttribute('role', 'region');
            track.setAttribute('aria-live', 'polite');
        }
    });

    // Start autoplay after window load (like nii-photo.com)
    window.addEventListener('load', function() {
        const autoplay = slider.Components.Autoplay;
        if (autoplay) {
            autoplay.play();
        }
    });

    slider.on('moved', function(newIndex, prevIndex, destIndex) {
        // Update current slide info for analytics if needed
        // console.log('Moved to slide:', newIndex);
    });

    slider.on('autoplay:playing', function(rate) {
        // Auto-play started
        // console.log('Auto-play started');
    });

    slider.on('autoplay:pause', function() {
        // Auto-play paused
        // console.log('Auto-play paused');
    });

    // Handle errors gracefully
    try {
        slider.mount();
    } catch (error) {
        console.error('Error mounting studio gallery slider:', error);

        // Fallback: Show first image if slider fails
        const firstSlide = sliderElement.querySelector('.splide__slide');
        if (firstSlide) {
            firstSlide.style.display = 'block';
        }
    }

    // Optional: Intersection Observer to pause when out of view
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    // Slider is visible, resume autoplay if paused
                    if (slider.Components.Autoplay) {
                        slider.Components.Autoplay.play();
                    }
                } else {
                    // Slider is not visible, pause autoplay
                    if (slider.Components.Autoplay) {
                        slider.Components.Autoplay.pause();
                    }
                }
            });
        }, {
            threshold: 0.1 // Trigger when 10% visible
        });

        observer.observe(sliderElement);
    }
});