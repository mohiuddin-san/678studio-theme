/**
 * Store Gallery Slider
 * GSAP-powered image slider with lightbox functionality
 */

class StoreGallerySlider {
    constructor() {
        this.slider = document.getElementById('gallery-slider');
        this.slides = this.slider.querySelectorAll('.store-gallery__slide');
        this.prevButton = document.getElementById('gallery-prev');
        this.nextButton = document.getElementById('gallery-next');
        this.indicators = document.querySelectorAll('.store-gallery__indicator');
        
        this.currentSlide = 0;
        this.totalSlides = this.slides.length;
        
        this.init();
    }
    
    init() {
        // Set initial position
        gsap.set(this.slides, { x: '100%', opacity: 0 });
        gsap.set(this.slides[0], { x: '0%', opacity: 1 });
        
        // Add event listeners
        this.prevButton.addEventListener('click', () => this.prevSlide());
        this.nextButton.addEventListener('click', () => this.nextSlide());
        
        // Indicator clicks
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Add lightbox functionality
        this.slides.forEach(slide => {
            const img = slide.querySelector('.store-gallery__image');
            img.addEventListener('click', () => this.openLightbox(img.src, img.alt));
        });
        
        // Auto-play (optional)
        this.startAutoPlay();
    }
    
    prevSlide() {
        const prevIndex = this.currentSlide;
        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.animateSlide(prevIndex, this.currentSlide, 'prev');
        this.updateIndicators();
    }
    
    nextSlide() {
        const prevIndex = this.currentSlide;
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.animateSlide(prevIndex, this.currentSlide, 'next');
        this.updateIndicators();
    }
    
    goToSlide(index) {
        if (index === this.currentSlide) return;
        
        const prevIndex = this.currentSlide;
        this.currentSlide = index;
        const direction = index > prevIndex ? 'next' : 'prev';
        this.animateSlide(prevIndex, this.currentSlide, direction);
        this.updateIndicators();
    }
    
    animateSlide(fromIndex, toIndex, direction) {
        const fromSlide = this.slides[fromIndex];
        const toSlide = this.slides[toIndex];
        
        // Set starting position for incoming slide
        const startX = direction === 'next' ? '100%' : '-100%';
        gsap.set(toSlide, { x: startX, opacity: 0 });
        
        // Animation timeline
        const tl = gsap.timeline();
        
        // Animate outgoing slide
        tl.to(fromSlide, {
            x: direction === 'next' ? '-100%' : '100%',
            opacity: 0,
            duration: 0.5,
            ease: 'power2.inOut'
        });
        
        // Animate incoming slide
        tl.to(toSlide, {
            x: '0%',
            opacity: 1,
            duration: 0.5,
            ease: 'power2.inOut'
        }, '-=0.3');
    }
    
    updateIndicators() {
        this.indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentSlide);
        });
    }
    
    openLightbox(src, alt) {
        // Create lightbox if it doesn't exist
        let lightbox = document.getElementById('gallery-lightbox');
        if (!lightbox) {
            lightbox = this.createLightbox();
        }
        
        // Set image and show lightbox
        const lightboxImg = lightbox.querySelector('.lightbox__image');
        lightboxImg.src = src;
        lightboxImg.alt = alt;
        
        // Show with animation
        gsap.set(lightbox, { display: 'flex', opacity: 0 });
        gsap.to(lightbox, { opacity: 1, duration: 0.3 });
        gsap.fromTo(lightboxImg, 
            { scale: 0.8 }, 
            { scale: 1, duration: 0.3, ease: 'back.out(1.7)' }
        );
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.id = 'gallery-lightbox';
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox__backdrop"></div>
            <div class="lightbox__content">
                <img class="lightbox__image" src="" alt="">
                <button class="lightbox__close">&times;</button>
            </div>
        `;
        
        // Add styles
        const styles = `
            .lightbox {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }
            .lightbox__content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
            }
            .lightbox__image {
                width: 100%;
                height: auto;
                max-height: 90vh;
                object-fit: contain;
            }
            .lightbox__close {
                position: absolute;
                top: -50px;
                right: 0;
                background: none;
                border: none;
                color: white;
                font-size: 40px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
            .lightbox__close:hover {
                opacity: 0.7;
            }
        `;
        
        // Add styles to head if not exists
        if (!document.getElementById('lightbox-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'lightbox-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }
        
        // Add event listeners
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.classList.contains('lightbox__backdrop')) {
                this.closeLightbox();
            }
        });
        
        lightbox.querySelector('.lightbox__close').addEventListener('click', () => {
            this.closeLightbox();
        });
        
        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                this.closeLightbox();
            }
        });
        
        document.body.appendChild(lightbox);
        return lightbox;
    }
    
    closeLightbox() {
        const lightbox = document.getElementById('gallery-lightbox');
        if (!lightbox) return;
        
        gsap.to(lightbox, { 
            opacity: 0, 
            duration: 0.3,
            onComplete: () => {
                lightbox.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
    startAutoPlay() {
        // Auto-advance every 5 seconds
        setInterval(() => {
            this.nextSlide();
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Check if GSAP is loaded
    if (typeof gsap === 'undefined') {
        console.warn('GSAP not loaded. Gallery slider will not work properly.');
        return;
    }
    
    // Initialize slider if gallery exists
    const gallerySlider = document.getElementById('gallery-slider');
    if (gallerySlider) {
        new StoreGallerySlider();
    }
});