<?php
/**
 * Template Name: Gallery Page
 * Description: A simple gallery page template
 */

get_header();
?>

<main class="main-content page-gallery">
  <!-- Breadcrumb -->
  <?php 
  get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => 'ギャラリー', 'url' => '']
    ]
  ]);
  ?>

  <!-- Gallery Hero Section -->
  <section class="gallery-hero">
    <div class="gallery-hero__container">
      <h1 class="gallery-hero__title">ギャラリー</h1>
      <p class="gallery-hero__description">678フォトスタジオの撮影作品をご覧ください</p>
    </div>
  </section>

  <!-- Gallery Content -->
  <section class="gallery-content">
    <div class="gallery-content__container">
      <?php
      // WordPress Media Libraryから画像を取得
      $media_images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC'
      ));

      if ($media_images): ?>
        <div class="gallery-grid">
          <?php foreach ($media_images as $image): 
            $img_url = wp_get_attachment_image_url($image->ID, 'large');
            $img_thumb = wp_get_attachment_image_url($image->ID, 'medium');
            ?>
            <div class="gallery-item">
              <img src="<?php echo esc_url($img_thumb); ?>" 
                   alt="<?php echo esc_attr($image->post_title); ?>" 
                   data-full-image="<?php echo esc_url($img_url); ?>"
                   loading="lazy">
              <div class="gallery-item__overlay">
                <svg class="gallery-item__icon" width="40" height="40" viewBox="0 0 40 40" fill="none">
                  <circle cx="16" cy="16" r="10" stroke="white" stroke-width="2"/>
                  <path d="M23 23L30 30" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="gallery-empty">
          <p>ギャラリー画像がありません。</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Contact & Booking Section -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>
</main>

<!-- Lightbox Modal -->
<div class="lightbox" id="galleryLightbox">
  <div class="lightbox__overlay"></div>
  <div class="lightbox__content">
    <button class="lightbox__close" aria-label="閉じる">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <img class="lightbox__image" src="" alt="">
  </div>
</div>

<script>
// シンプルなライトボックス機能
document.addEventListener('DOMContentLoaded', function() {
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = lightbox.querySelector('.lightbox__image');
  const closeBtn = lightbox.querySelector('.lightbox__close');
  const overlay = lightbox.querySelector('.lightbox__overlay');
  
  // 画像クリックイベント
  document.addEventListener('click', function(e) {
    if (e.target.closest('.gallery-item')) {
      const item = e.target.closest('.gallery-item');
      const img = item.querySelector('img');
      const fullImageSrc = img.dataset.fullImage || img.src;
      
      // ライトボックスを開く
      lightboxImage.src = fullImageSrc;
      lightboxImage.alt = img.alt;
      lightbox.classList.add('lightbox--active');
      document.body.style.overflow = 'hidden';
    }
  });
  
  // 閉じる機能
  const closeLightbox = function() {
    lightbox.classList.remove('lightbox--active');
    document.body.style.overflow = '';
  };
  
  closeBtn.addEventListener('click', closeLightbox);
  overlay.addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', function(e) {
    if (e.target === lightbox || e.target === overlay || e.target.classList.contains('lightbox__content')) {
      closeLightbox();
    }
  });
  
  // ESCキーで閉じる
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && lightbox.classList.contains('lightbox--active')) {
      closeLightbox();
    }
  });
});
</script>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>