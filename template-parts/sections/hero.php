<?php
/**
 * Hero Section Component
 */
?>

<section class="hero-section">
  <div class="hero-section__image-container">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/fv.jpg" alt="678 Studio Hero Image"
      class="hero-section__image">
    
    <div class="hero-section__title-image">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/fv_title.svg" alt="678 Studio Title" class="hero-section__title-svg">
    </div>
    
    <div class="hero-section__content">
      <?php get_template_part('template-parts/components/camera-button', null, [
            'text' => '写真館を探す',
            'url' => home_url('/search'),
            'class' => 'hero-section__button',
            'bg_color' => 'white'
        ]); ?>
    </div>
  </div>
</section>