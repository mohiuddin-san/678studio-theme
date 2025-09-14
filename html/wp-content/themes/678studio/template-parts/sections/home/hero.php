<?php
/**
 * Hero Section Component
 */
?>

<section class="hero-section" id="hero-section">
  <!-- PC Hero -->
  <div class="hero-section__pc">
    <div class="hero-section__grid-container">
      <!-- Grid Item 1: Background Image -->
      <div class="hero-section__bg-image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero_bg.jpg" alt="678 Studio Hero Image">
      </div>

      <!-- Grid Item 2: Left Side Content -->
      <div class="hero-section__content">
        <!-- Main Title -->
        <div class="hero-section__main-title">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-text-v2.svg"
            alt="60代・70代・80代 人生の節目に、写真館で撮影をしよう！">
        </div>

        <!-- Sub Text with Blue Background -->
        <div class="hero-section__sub-text">
          <div class="hero-section__sub-text-bg"></div>
          <p class="hero-section__sub-text-content">
            「今」を残したい。あなたと写真館をつなぐ場所。
          </p>
        </div>

        <!-- 3 Points -->
        <div class="hero-section__points">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-point.svg"
            alt="全国にてお近くの写真館検索 シニアに撮影で嬉しいサービス 撮影シーンやこだわりで探す">
        </div>
      </div>

      <!-- Grid Item 3: Right Side CTA Button -->
      <div class="hero-section__cta">
        <a href="<?php echo home_url('/stores'); ?>" class="hero-section__cta-button">
          写真館を探す
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/search.svg" alt="検索"
            class="hero-section__cta-icon">
        </a>
      </div>
    </div>
  </div>

  <!-- SP Hero -->
  <div class="hero-section__sp">
    <div class="hero-section__sp-container">
      <!-- Background Image -->
      <div class="hero-section__sp-bg">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero_bg_sp.png" alt="写真館背景">
      </div>

      <!-- Content -->
      <div class="hero-section__sp-content">
        <!-- Main Title (SVG) -->
        <div class="hero-section__sp-title">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-text-v2.svg"
            alt="60代・70代・80代 人生の節目に、写真館で撮影をしよう！">
        </div>

        <!-- Sub Text with Blue Background -->
        <div class="hero-section__sp-subtext">
          「今」を残したい。あなたと写真館をつなぐ場所。
        </div>

        <!-- 3 Points (SVG) -->
        <div class="hero-section__sp-points">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-point.svg"
            alt="全国にてお近くの写真館検索 シニアに撮影で嬉しいサービス 撮影シーンやこだわりで探す">
        </div>

      </div>
    </div>
  </div>

</section>