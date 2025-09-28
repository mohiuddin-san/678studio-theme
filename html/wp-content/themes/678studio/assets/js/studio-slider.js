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

    // Mobile touch handling for better scroll experience
    let touchStartX = 0;
    let touchStartY = 0;
    let isHorizontalSwipe = null;

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
        drag: 'free', // Use free drag for better control

        // Performance
        lazyLoad: 'nearby',
        preloadPages: 2,

        // Accessibility
        live: true,

        // Responsive settings matching nii-photo.com
        breakpoints: {
            768: {
                padding: 0, // No padding for centered single image
                gap: "16px",
                focus: 'center', // Center the current slide
                perPage: 1, // Show one slide at a time
                perMove: 1, // Move one slide at a time
                // Mobile touch optimization
                drag: true, // Enable drag
                dragMinThreshold: {
                    mouse: 0,
                    touch: 10 // Lower threshold for horizontal swipe detection
                },
                dragAngleThreshold: 60, // Angle threshold (degrees) - vertical if angle > 60°
                waitForTransition: false, // Don't block interactions during transition
                flickPower: 300, // Normal flick sensitivity
                flickMaxPages: 1, // Limit flick to one page
                speed: 400 // Faster transition for better response
            }
        }
    });

    // Add touch event handlers for better mobile scroll control
    if (sliderElement && window.matchMedia('(max-width: 768px)').matches) {
        const track = sliderElement.querySelector('.splide__track');

        if (track) {
            track.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isHorizontalSwipe = null;
            }, { passive: true });

            track.addEventListener('touchmove', function(e) {
                if (isHorizontalSwipe !== null) return;

                const touchEndX = e.touches[0].clientX;
                const touchEndY = e.touches[0].clientY;

                const diffX = Math.abs(touchEndX - touchStartX);
                const diffY = Math.abs(touchEndY - touchStartY);

                // Determine swipe direction based on angle (not ratio)
                const angle = Math.atan2(diffY, diffX) * 180 / Math.PI;

                if (angle > 45) {
                    // Vertical scroll detected (angle > 45 degrees)
                    isHorizontalSwipe = false;
                    track.style.touchAction = 'pan-y';
                    e.stopPropagation(); // Stop event from reaching Splide
                } else if (diffX > 10) {
                    // Horizontal swipe detected (angle < 45 degrees and moved > 10px)
                    isHorizontalSwipe = true;
                    track.style.touchAction = 'pan-x';
                }
            }, { passive: true });

            track.addEventListener('touchend', function() {
                // Reset for next touch
                setTimeout(() => {
                    track.style.touchAction = '';
                    isHorizontalSwipe = null;
                }, 100);
            }, { passive: true });
        }
    }

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