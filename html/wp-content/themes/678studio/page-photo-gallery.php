<?php
/**
 * Template Name: Gallery Page
 * Description: A page template for displaying image galleries with API integration
 */

get_header(); ?>

<!-- Breadcrumb Section -->
<?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => 'ギャラリー', 'url' => '']
    ]
]); ?>

<!-- Gallery Header Section -->
<section class="gallery-header">
  <div class="container">
    <div class="gallery-header__inner">
      <h1 class="gallery-header__title">Gallery</h1>
      <div class="gallery-header__filters">
        <div class="gallery-select-wrapper">
          <select class="gallery-select" id="studio-filter">
            <option value="all">全スタジオ</option>
            <!-- Options will be populated dynamically via JavaScript -->
          </select>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Gallery Grid Section -->
<section class="gallery-grid">
  <div class="gallery-grid__inner" id="gallery-grid">
    <!-- Images will be populated dynamically via JavaScript -->
  </div>

  <!-- Loading indicator -->
  <div class="gallery-loading" id="gallery-loading" style="display: none;">
    <div class="gallery-loading__spinner">
      <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="18" fill="none" stroke="#a5c3cf" stroke-width="2" stroke-linecap="round"
          stroke-dasharray="28 28" transform="rotate(-90 20 20)">
          <animateTransform attributeName="transform" type="rotate" values="-90 20 20;270 20 20" dur="1s"
            repeatCount="indefinite" />
        </circle>
      </svg>
    </div>
    <p class="gallery-loading__text">画像を読み込んでいます...</p>
  </div>

  <!-- End of content indicator (hidden) -->
  <div class="gallery-end" id="gallery-end" style="display: none;">
  </div>
</section>

<!-- Contact & Booking Section -->
<?php get_template_part('template-parts/components/contact-booking'); ?>

<!-- Lightbox Modal -->
<div class="lightbox" id="galleryLightbox">
  <div class="lightbox__overlay"></div>
  <div class="lightbox__content">
    <button class="lightbox__close" aria-label="閉じる">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <img class="lightbox__image" src="" alt="">
  </div>
</div>

<?php get_template_part('template-parts/components/footer'); ?>

<script src="<?php echo get_template_directory_uri(); ?>/assets/js/gallery-lightbox.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Register ScrollTrigger plugin if GSAP is available
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
  }
  const studioFilter = document.getElementById('studio-filter');
  const galleryGrid = document.getElementById('gallery-grid');

  // Store API data (will be loaded dynamically)
  let shopsData = [];

  // Animation configuration
  const CONFIG = {
    animationDuration: 1.5,
    animationDelay: 0.08,
    initialBlur: 10, // pixels
    gridRowHeight: 20, // Should match CSS grid-auto-rows
    gridGap: 24 // Should match CSS gap
  };

  // Function to load studio data asynchronously
  async function loadStudioData() {
    try {
      const response = await fetch(galleryAjax.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'get_gallery_studios',
          nonce: galleryAjax.nonce
        })
      });

      const data = await response.json();

      if (data.success && data.data.shops) {
        shopsData = data.data.shops;
        
        // デバッグ: main_imageがあるかをチェック
        console.log('DEBUG - Loaded shops data:', shopsData.length, 'shops');
        shopsData.forEach((shop, index) => {
          console.log(`DEBUG - Shop ${index + 1} (ID: ${shop.id}): name="${shop.name}", has_main_image=${!!(shop.main_image)}`);
          if (shop.main_image) {
            console.log(`DEBUG - Shop ${shop.id} main_image length:`, shop.main_image.length);
            console.log(`DEBUG - Shop ${shop.id} main_image starts with:`, shop.main_image.substring(0, 50));
          }
        });
        
        populateStudioFilter();
        updateGallery(); // Initial gallery population
      } else {
        console.error('Failed to load studio data:', data.data?.message || 'Unknown error');
        // Show fallback message
        galleryGrid.innerHTML = '<p>スタジオデータの読み込みに失敗しました。</p>';
      }
    } catch (error) {
      console.error('Error loading studio data:', error);
      galleryGrid.innerHTML = '<p>スタジオデータの読み込み中にエラーが発生しました。</p>';
    }
  }

  // Function to populate studio filter dropdown
  function populateStudioFilter() {
    // Clear existing options except "all"
    while (studioFilter.children.length > 1) {
      studioFilter.removeChild(studioFilter.lastChild);
    }

    // Add studio options
    shopsData.forEach(shop => {
      const option = document.createElement('option');
      option.value = shop.id;
      option.textContent = shop.name;
      studioFilter.appendChild(option);
    });
  }


  // Function to update gallery
  function updateGallery() {
    const selectedStudio = studioFilter.value;

    // Add updating class for smooth transition
    galleryGrid.classList.add('updating');

    // Clear gallery after brief delay for smooth transition
    setTimeout(() => {
      galleryGrid.innerHTML = '';

      // Create all gallery items at once
      let imageCount = 0;
      shopsData.forEach(shop => {
        // Filter by studio
        if (selectedStudio === 'all' || shop.id.toString() === selectedStudio) {
          // Handle main gallery images (simplified gallery system)
          if (shop.main_gallery_images && Array.isArray(shop.main_gallery_images)) {
            shop.main_gallery_images.forEach((image, index) => {
              // Check if image has url property (new structure) or is direct URL (old structure)
              const imageUrl = (typeof image === 'object' && image.url) ? image.url : image;
              const alt = `Gallery Image ${index + 1} from ${shop.name}`;
              createGalleryItem(imageUrl, alt);
              imageCount++;
            });
          }
        }
      });

      // Remove updating class
      galleryGrid.classList.remove('updating');

      // If no images are found, display a message
      if (imageCount === 0) {
        galleryGrid.innerHTML = '<p>選択したスタジオに画像がありません。</p>';
      } else {
        // Setup beautiful fade animations
        setupScrollAnimations();
      }
    }, 150);
  }

  // Setup masonry layout
  function setupMasonryLayout() {
    return new Promise((resolve) => {
      const galleryItems = galleryGrid.querySelectorAll('.gallery-grid__item');
      if (galleryItems.length === 0) {
        resolve();
        return;
      }

      // Wait for all images to load
      const images = Array.from(galleryItems).map(item => item.querySelector('img'));
      let loadedCount = 0;

      const checkAllLoaded = () => {
        loadedCount++;
        if (loadedCount === images.length) {
          // Add a small delay to ensure layout is stable
          setTimeout(() => {
            calculateMasonryLayout();
            resolve();
          }, 50);
        }
      };

      images.forEach(img => {
        if (img.complete && img.naturalHeight > 0) {
          checkAllLoaded();
        } else {
          img.addEventListener('load', () => {
            // Ensure image has rendered before calculating
            requestAnimationFrame(checkAllLoaded);
          });
          img.addEventListener('error', checkAllLoaded); // Handle broken images
        }
      });
    });
  }

  // Calculate and apply masonry layout
  function calculateMasonryLayout() {
    const galleryItems = galleryGrid.querySelectorAll('.gallery-grid__item');
    if (galleryItems.length === 0) return;

    // Get grid properties
    const gridComputedStyle = window.getComputedStyle(galleryGrid);
    const gridColumnGap = parseInt(gridComputedStyle.getPropertyValue('gap')) || CONFIG.gridGap;

    // Determine number of columns based on screen size
    const columns = getColumnCount();

    // Track height of each column
    const columnHeights = new Array(columns).fill(0);

    galleryItems.forEach((item, index) => {
      const img = item.querySelector('img');
      if (!img) return;

      // Use actual rendered dimensions instead of natural dimensions
      const actualHeight = img.offsetHeight || img.getBoundingClientRect().height;
      const actualWidth = img.offsetWidth || img.getBoundingClientRect().width;

      // If image hasn't loaded yet, wait for it
      if (actualHeight === 0 || actualWidth === 0) {
        const imgHeight = img.naturalHeight || 200;
        const imgWidth = img.naturalWidth || 200;
        const itemWidth = galleryGrid.offsetWidth / columns - (gridColumnGap * (columns - 1)) / columns;
        const calculatedHeight = (imgHeight / imgWidth) * itemWidth;
        item.style.height = calculatedHeight + 'px';
      }

      // Get the actual item height (including image + any padding)
      const itemHeight = item.offsetHeight || item.getBoundingClientRect().height;

      // Find the shortest column
      const shortestColumnIndex = columnHeights.indexOf(Math.min(...columnHeights));

      // Calculate grid row span needed to fit the actual height
      const rowSpan = Math.ceil(itemHeight / CONFIG.gridRowHeight);

      // Apply grid positioning
      item.style.gridColumn = shortestColumnIndex + 1;
      item.style.gridRowEnd = `span ${rowSpan}`;

      // Update column height
      columnHeights[shortestColumnIndex] += itemHeight + gridColumnGap;
    });
  }

  // Get number of columns based on screen width
  function getColumnCount() {
    const width = window.innerWidth;
    if (width <= 479) return 1; // sm
    if (width <= 767) return 2; // md
    if (width <= 1023) return 3; // lg
    return 4; // desktop
  }

  // Setup beautiful fade-in animations for all items (no blur, pure fade)
  function setupScrollAnimations() {
    const galleryItems = galleryGrid.querySelectorAll('.gallery-grid__item');
    if (galleryItems.length === 0) return;

    // Set initial state for all items (hidden but without blur)
    galleryItems.forEach(item => {
      const img = item.querySelector('img');
      if (img) {
        // Pure CSS-based fade animation
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
        item.classList.add('gallery-fade-item');
      }
    });

    // Use Intersection Observer for smooth fade-in
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          const item = entry.target;
          // Staggered delay for beautiful cascading effect
          const delay = (Array.from(galleryItems).indexOf(item) % 4) * 100;

          setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
            item.classList.add('gallery-fade-visible');
          }, delay);

          observer.unobserve(item);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    // Observe all gallery items
    galleryItems.forEach(item => {
      observer.observe(item);
    });
  }

  // Function to create gallery item
  function createGalleryItem(imageUrl, altText) {
    // Validate image URL
    if (imageUrl && typeof imageUrl === 'string' && (imageUrl.endsWith('.jpg') || imageUrl.endsWith('.jpeg') ||
        imageUrl.endsWith('.png'))) {
      const item = document.createElement('div');
      item.className = 'gallery-grid__item';
      item.innerHTML = `
                <img src="${imageUrl}" alt="${altText}" data-full-image="${imageUrl}" loading="lazy" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/grayscale.jpg';">
                <div class="gallery-grid__overlay">
                    <svg class="gallery-grid__icon" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="10" stroke="white" stroke-width="2" />
                        <path d="M23 23L30 30" stroke="white" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            `;
      galleryGrid.appendChild(item);
    }
  }

  // Event listeners for filters
  studioFilter.addEventListener('change', updateGallery);

  // Add resize handler for responsive layout updates
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (typeof ScrollTrigger !== 'undefined') {
        ScrollTrigger.refresh();
      }
    }, 250);
  });

  // Load studio data and initialize gallery
  loadStudioData();

  // Lightbox functionality
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = lightbox.querySelector('.lightbox__image');
  const closeButton = lightbox.querySelector('.lightbox__close');

  galleryGrid.addEventListener('click', function(e) {
    const img = e.target.closest('img');
    if (img) {
      lightboxImage.src = img.dataset.fullImage;
      lightboxImage.alt = img.alt;
      lightbox.classList.add('lightbox--active');
    }
  });

  closeButton.addEventListener('click', function() {
    lightbox.classList.remove('lightbox--active');
  });

  lightbox.querySelector('.lightbox__overlay').addEventListener('click', function() {
    lightbox.classList.remove('lightbox--active');
  });
});
</script>

<?php get_footer(); ?>