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
          <select class="gallery-select" id="category-filter">
            <option value="all">ALL</option>
          </select>
        </div>
        <div class="gallery-select-wrapper">
          <select class="gallery-select" id="studio-filter">
            <option value="all">全スタジオ</option>
            <?php
            // Fetch shop data from API
            $api_url = 'https://678photo.com/api/get_all_studio_shop.php';
            $response = wp_remote_get($api_url);
            $shops = [];
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                if ($data['success'] && !empty($data['shops'])) {
                    $shops = $data['shops'];
                    foreach ($shops as $shop) {
                        echo '<option value="' . esc_attr($shop['id']) . '">' . esc_html($shop['name']) . '</option>';
                    }
                }
            }
            ?>
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
</section>

<!-- Contact & Booking Section -->
<?php get_template_part('template-parts/components/contact-booking'); ?>

<!-- Lightbox Modal -->
<div class="lightbox" id="galleryLightbox">
  <div class="lightbox__overlay"></div>
  <div class="lightbox__content">
    <button class="lightbox__close" aria-label="閉じる">
      <svg widthуму24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <img class="lightbox__image" src="" alt="">
  </div>
</div>

<?php get_template_part('template-parts/components/footer'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const studioFilter = document.getElementById('studio-filter');
    const galleryGrid = document.getElementById('gallery-grid');
    
    // Store API data
    const shopsData = <?php echo json_encode($shops); ?>;
    
    // Function to update category filter based on selected shop
    function updateCategoryFilter(shopId) {
        // Clear existing categories
        categoryFilter.innerHTML = '<option value="all">ALL</option>';
        
        if (shopId === 'all') {
            // For 'all' studios, collect all unique categories
            const categories = new Set();
            shopsData.forEach(shop => {
                if (shop.category_images && typeof shop.category_images === 'object') {
                    Object.keys(shop.category_images).forEach(category => {
                        categories.add(category);
                    });
                }
            });
            if (categories.size === 0) {
                const option = document.createElement('option');
                option.value = 'no-category';
                option.textContent = 'No category';
                option.disabled = true;
                categoryFilter.appendChild(option);
            } else {
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categoryFilter.appendChild(option);
                });
            }
        } else {
            // For specific shop
            const shop = shopsData.find(s => s.id.toString() === shopId);
            if (shop && shop.category_images && typeof shop.category_images === 'object') {
                const categories = Object.keys(shop.category_images);
                if (categories.length === 0) {
                    const option = document.createElement('option');
                    option.value = 'no-category';
                    option.textContent = 'No category';
                    option.disabled = true;
                    categoryFilter.appendChild(option);
                } else {
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        categoryFilter.appendChild(option);
                    });
                }
            } else {
                const option = document.createElement('option');
                option.value = 'no-category';
                option.textContent = 'No category';
                option.disabled = true;
                categoryFilter.appendChild(option);
            }
        }
    }
    
    // Function to update gallery
    function updateGallery() {
        const selectedStudio = studioFilter.value;
        const selectedCategory = categoryFilter.value;
        galleryGrid.innerHTML = '';
        
        // Update categories when studio changes
        updateCategoryFilter(selectedStudio);
        
        shopsData.forEach(shop => {
            // Filter by studio
            if (selectedStudio === 'all' || shop.id.toString() === selectedStudio) {
                // Handle only category images
                if (shop.category_images && typeof shop.category_images === 'object') {
                    Object.entries(shop.category_images).forEach(([category, images]) => {
                        if (selectedCategory === 'all' || selectedCategory === category) {
                            if (Array.isArray(images)) {
                                images.forEach((image, index) => {
                                    createGalleryItem(image, `${category} Image ${index + 1} from ${shop.name}`);
                                });
                            }
                        }
                    });
                }
            }
        });
        
        // If no images are found, display a message
        if (galleryGrid.innerHTML === '') {
            galleryGrid.innerHTML = '<p>No images available for the selected filters.</p>';
        }
    }
    
    // Function to create gallery item
    function createGalleryItem(imageUrl, altText) {
        // Validate image URL
        if (imageUrl && typeof imageUrl === 'string' && (imageUrl.endsWith('.jpg') || imageUrl.endsWith('.jpeg') || imageUrl.endsWith('.png'))) {
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
    categoryFilter.addEventListener('change', updateGallery);
    
    // Initial gallery population
    updateGallery();
    
    // Lightbox functionality
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxImage = lightbox.querySelector('.lightbox__image');
    const closeButton = lightbox.querySelector('.lightbox__close');
    
    galleryGrid.addEventListener('click', function(e) {
        const img = e.target.closest('img');
        if (img) {
            lightboxImage.src = img.dataset.fullImage;
            lightboxImage.alt = img.alt;
            lightbox.classList.add('active');
        }
    });
    
    closeButton.addEventListener('click', function() {
        lightbox.classList.remove('active');
    });
    
    lightbox.querySelector('.lightbox__overlay').addEventListener('click', function() {
        lightbox.classList.remove('active');
    });
});
</script>

<?php get_footer(); ?>