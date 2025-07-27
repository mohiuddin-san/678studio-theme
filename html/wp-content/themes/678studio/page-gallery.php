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
        alt="Gallery Image <?php echo $i; ?>" loading="lazy">
    </div>
    <?php endfor; ?>
  </div>
</section>
<!-- Contact & Booking Section -->
<?php get_template_part('template-parts/components/contact-booking'); ?>


<?php get_template_part('template-parts/components/footer'); ?>