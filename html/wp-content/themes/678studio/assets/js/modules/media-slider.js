/**
 * Media Slider Module
 * Handles the GSAP-powered media slider functionality
 */

class MediaSlider {
  constructor() {
    this.slider = document.querySelector('.media-slider-section');
    if (!this.slider) return;

    this.track = this.slider.querySelector('.media-slider-section__track');
    this.slides = this.slider.querySelectorAll('.media-slider-section__item');
    this.prevBtn = this.slider.querySelector('.media-slider-section__nav--prev');
    this.nextBtn = this.slider.querySelector('.media-slider-section__nav--next');
    this.dots = this.slider.querySelectorAll('.media-slider-section__dot');
    
    this.currentIndex = 0;
    this.slideCount = this.slides.length;
    this.slideWidth = 0;
    this.isDragging = false;
    this.startX = 0;
    this.currentX = 0;
    this.autoplayTimer = null;
    
    this.init();
  }

  init() {
    if (this.slideCount === 0) return;
    
    // Set up slider dimensions
    this.setupSlider();
    
    // Event listeners
    this.prevBtn.addEventListener('click', () => this.goToPrevSlide());
    this.nextBtn.addEventListener('click', () => this.goToNextSlide());
    
    // Dot navigation
    this.dots.forEach((dot, index) => {
      dot.addEventListener('click', () => this.goToSlide(index));
    });
    
    // Touch/Mouse drag support
    this.initDraggable();
    
    // Window resize
    window.addEventListener('resize', () => this.handleResize());
    
    // Start autoplay
    this.startAutoplay();
    
    // Pause autoplay on hover
    this.slider.addEventListener('mouseenter', () => this.stopAutoplay());
    this.slider.addEventListener('mouseleave', () => this.startAutoplay());
  }

  setupSlider() {
    // Calculate slide width based on viewport
    const viewportWidth = window.innerWidth;
    let slidesPerView = 3;
    
    if (viewportWidth <= 768) {
      slidesPerView = 1;
    } else if (viewportWidth <= 1024) {
      slidesPerView = 2;
    }
    
    const containerWidth = this.slider.querySelector('.media-slider-section__slider').offsetWidth;
    this.slideWidth = containerWidth / slidesPerView;
    
    // Set slide widths
    this.slides.forEach(slide => {
      slide.style.width = `${this.slideWidth}px`;
    });
    
    // Set track width
    this.track.style.width = `${this.slideWidth * this.slideCount}px`;
    
    // Initial position
    this.updateSliderPosition();
  }

  initDraggable() {
    // Make the track draggable with GSAP
    if (typeof Draggable !== 'undefined') {
      this.draggable = Draggable.create(this.track, {
        type: 'x',
        edgeResistance: 0.65,
        bounds: {
          minX: -(this.slideWidth * (this.slideCount - 1)),
          maxX: 0
        },
        inertia: true,
        onDragEnd: () => {
          this.snapToNearestSlide();
        }
      })[0];
    } else {
      // Fallback to basic touch/mouse events if Draggable plugin not available
      this.initBasicDrag();
    }
  }

  initBasicDrag() {
    let startX = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    
    const handleStart = (e) => {
      this.isDragging = true;
      startX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
      this.stopAutoplay();
    };
    
    const handleMove = (e) => {
      if (!this.isDragging) return;
      e.preventDefault();
      
      const currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
      const diffX = currentX - startX;
      currentTranslate = prevTranslate + diffX;
      
      gsap.set(this.track, { x: currentTranslate });
    };
    
    const handleEnd = () => {
      if (!this.isDragging) return;
      this.isDragging = false;
      
      prevTranslate = currentTranslate;
      this.snapToNearestSlide();
      this.startAutoplay();
    };
    
    // Touch events
    this.track.addEventListener('touchstart', handleStart);
    this.track.addEventListener('touchmove', handleMove);
    this.track.addEventListener('touchend', handleEnd);
    
    // Mouse events
    this.track.addEventListener('mousedown', handleStart);
    this.track.addEventListener('mousemove', handleMove);
    this.track.addEventListener('mouseup', handleEnd);
    this.track.addEventListener('mouseleave', handleEnd);
  }

  snapToNearestSlide() {
    const currentX = gsap.getProperty(this.track, 'x');
    const nearestIndex = Math.round(-currentX / this.slideWidth);
    this.goToSlide(Math.max(0, Math.min(nearestIndex, this.slideCount - 1)));
  }

  goToSlide(index) {
    if (index < 0 || index >= this.slideCount) return;
    
    this.currentIndex = index;
    this.updateSliderPosition();
    this.updateDots();
  }

  goToPrevSlide() {
    const newIndex = this.currentIndex - 1;
    if (newIndex < 0) {
      this.goToSlide(this.slideCount - 1); // Loop to last slide
    } else {
      this.goToSlide(newIndex);
    }
  }

  goToNextSlide() {
    const newIndex = this.currentIndex + 1;
    if (newIndex >= this.slideCount) {
      this.goToSlide(0); // Loop to first slide
    } else {
      this.goToSlide(newIndex);
    }
  }

  updateSliderPosition() {
    const targetX = -this.currentIndex * this.slideWidth;
    
    gsap.to(this.track, {
      x: targetX,
      duration: 0.6,
      ease: 'power2.inOut'
    });
  }

  updateDots() {
    this.dots.forEach((dot, index) => {
      dot.classList.toggle('media-slider-section__dot--active', index === this.currentIndex);
    });
  }

  startAutoplay() {
    this.stopAutoplay();
    this.autoplayTimer = setInterval(() => {
      this.goToNextSlide();
    }, 5000); // Change slide every 5 seconds
  }

  stopAutoplay() {
    if (this.autoplayTimer) {
      clearInterval(this.autoplayTimer);
      this.autoplayTimer = null;
    }
  }

  handleResize() {
    this.setupSlider();
    if (this.draggable) {
      this.draggable.update();
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new MediaSlider();
});