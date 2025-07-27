<?php
/**
 * Template Name: Gallery Page
 * Description: A page template for displaying image galleries with FTP integration
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
            <option value="interior">インテリア</option>
            <option value="exterior">エクステリア</option>
            <option value="furniture">家具</option>
            <option value="space">空間デザイン</option>
          </select>
        </div>
        <div class="gallery-select-wrapper">
          <select class="gallery-select" id="studio-filter">
            <option value="all">全スタジオ</option>
            <option value="tokyo">東京スタジオ</option>
            <option value="osaka">大阪スタジオ</option>
            <option value="nagoya">名古屋スタジオ</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Gallery Grid Section -->
<section class="gallery-grid">
  <div class="gallery-grid__inner">
    <?php 
    // 静的に32個（4列×8行）の画像を生成
    for ($i = 1; $i <= 32; $i++): 
    ?>
    <div class="gallery-grid__item">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/grayscale.jpg"
        alt="Gallery Image <?php echo $i; ?>" 
        data-full-image="<?php echo get_template_directory_uri(); ?>/assets/images/grayscale.jpg"
        loading="lazy">
      <div class="gallery-grid__overlay">
        <svg class="gallery-grid__icon" width="40" height="40" viewBox="0 0 40 40" fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <circle cx="16" cy="16" r="10" stroke="white" stroke-width="2" />
          <path d="M23 23L30 30" stroke="white" stroke-width="2" stroke-linecap="round" />
        </svg>
      </div>
    </div>
    <?php endfor; ?>
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