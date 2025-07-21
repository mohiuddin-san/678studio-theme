/**
 * Auto-Scroll Media Slider Module
 * Handles GSAP-powered infinite horizontal scrolling
 */

class AutoMediaSlider {
  constructor() {
    this.slider = document.querySelector('.media-slider-section');
    if (!this.slider) return;

    this.track = this.slider.querySelector('.media-slider-section__track');
    this.originalItems = this.slider.querySelectorAll('.media-slider-section__item');
    
    this.slideWidth = 0;
    this.totalWidth = 0;
    this.animation = null;
    this.isHovered = false;
    
    this.init();
  }

  init() {
    if (this.originalItems.length === 0) return;
    
    // Clone items for infinite scroll
    this.cloneItems();
    
    // Set up slider dimensions
    this.setupSlider();
    
    // Start auto-scroll animation
    this.startAutoScroll();
    
    // Hover events for pause/resume
    this.initHoverEvents();
    
    // Window resize handler
    window.addEventListener('resize', () => this.handleResize());
  }

  cloneItems() {
    // Create enough clones for seamless infinite scroll
    const cloneCount = Math.ceil(window.innerWidth / this.getItemWidth()) + 2;
    
    // Clone items and append to track
    for (let i = 0; i < cloneCount; i++) {
      this.originalItems.forEach(item => {
        const clone = item.cloneNode(true);
        clone.classList.add('cloned-item');
        this.track.appendChild(clone);
      });
    }
  }

  getItemWidth() {
    // Get item width based on screen size
    const viewportWidth = window.innerWidth;
    
    if (viewportWidth <= 480) {
      return 240 + 20; // item width + padding
    } else if (viewportWidth <= 768) {
      return 280 + 20;
    } else {
      return 386 + 30;
    }
  }

  setupSlider() {
    // Calculate dimensions
    this.slideWidth = this.getItemWidth();
    const allItems = this.track.querySelectorAll('.media-slider-section__item');
    this.totalWidth = this.slideWidth * allItems.length;
    
    // Set track width
    this.track.style.width = `${this.totalWidth}px`;
    
    // Set initial position
    gsap.set(this.track, { x: 0 });
  }

  startAutoScroll() {
    if (this.animation) {
      this.animation.kill();
    }

    // Calculate scroll distance (one full set of original items)
    const scrollDistance = this.slideWidth * this.originalItems.length;
    
    // Calculate duration for consistent speed (pixels per second)
    const speed = this.getScrollSpeed(); // pixels per second
    const duration = scrollDistance / speed;

    // Create infinite scroll animation
    this.animation = gsap.to(this.track, {
      x: `-=${scrollDistance}`,
      duration: duration,
      ease: "none",
      repeat: -1,
      onRepeat: () => {
        // Reset position for seamless loop
        gsap.set(this.track, { x: 0 });
      }
    });
  }

  getScrollSpeed() {
    // Adjust speed based on device
    const viewportWidth = window.innerWidth;
    
    if (viewportWidth <= 480) {
      return 30; // pixels per second for mobile
    } else if (viewportWidth <= 768) {
      return 40; // pixels per second for tablet
    } else {
      return 50; // pixels per second for desktop
    }
  }

  initHoverEvents() {
    this.slider.addEventListener('mouseenter', () => {
      this.isHovered = true;
      if (this.animation) {
        this.animation.pause();
      }
    });

    this.slider.addEventListener('mouseleave', () => {
      this.isHovered = false;
      if (this.animation) {
        this.animation.resume();
      }
    });

    // Pause on focus for accessibility
    this.slider.addEventListener('focusin', () => {
      if (this.animation) {
        this.animation.pause();
      }
    });

    this.slider.addEventListener('focusout', () => {
      if (this.animation && !this.isHovered) {
        this.animation.resume();
      }
    });
  }

  handleResize() {
    // Recalculate dimensions and restart animation
    this.setupSlider();
    this.startAutoScroll();
  }

  // Public methods for external control
  pause() {
    if (this.animation) {
      this.animation.pause();
    }
  }

  resume() {
    if (this.animation && !this.isHovered) {
      this.animation.resume();
    }
  }

  destroy() {
    if (this.animation) {
      this.animation.kill();
    }
    
    // Remove cloned items
    this.track.querySelectorAll('.cloned-item').forEach(item => {
      item.remove();
    });
    
    // Remove event listeners
    window.removeEventListener('resize', this.handleResize);
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new AutoMediaSlider();
});