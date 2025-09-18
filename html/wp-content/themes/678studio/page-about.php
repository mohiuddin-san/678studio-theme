<?php
/**
 * Template Name: 678撮影について
 * Description: 678撮影についてのページテンプレート
 */

get_header();
?>

<main class="main-content about-page">
  <!-- About FV Section (Desktop) -->
  <section class="about-fv pc" id="about-fv-section">
    <div class="about-fv__container">

      <!-- Text Content -->
      <div class="about-fv__content">
        <div class="about-fv__text">
          <div class="title-section title-section--default about-fv__title-section">
            <div class="title-section__title">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-fv-text.svg"
                alt="ロクナナハチ撮影とは？60代・70代・80代の方々のための特別な撮影サービス" class="about-fv__title-image">
            </div>
          </div>
        </div>
      </div>

      <!-- Image Content -->
      <div class="about-fv__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-fv.jpg" alt="ロクナナハチ撮影とは？"
          class="about-fv__image-img">
      </div>

    </div>
  </section>

  <!-- About FV Section (Mobile) -->
  <section class="about-mobile-fv sp" id="about-mobile-fv-section">
    <div class="about-mobile-fv__container">
      <!-- Text Section (Top) -->
      <div class="about-mobile-fv__text-section">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-fv-text.svg"
          alt="ロクナナハチ撮影とは？60代・70代・80代の方々のための特別な撮影サービス" class="about-mobile-fv__text-image">
      </div>

      <!-- Image Section (Bottom) -->
      <div class="about-mobile-fv__image-section">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-hero-sp.jpg" alt="ロクナナハチ撮影の様子"
          class="about-mobile-fv__image">
      </div>
    </div>
  </section>

  <!-- Breadcrumb Section -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => 'ロクナナハチ撮影とは？', 'url' => '']
    ]
  ]); ?>

  <!-- About Introduction Section -->
  <?php get_template_part('template-parts/sections/about/introduction'); ?>

  <!-- About Support Section -->
  <?php get_template_part('template-parts/sections/about/support'); ?>

  <!-- About Memorial Section -->
  <?php get_template_part('template-parts/sections/about/memorial'); ?>

  <!-- About Present Section -->
  <?php get_template_part('template-parts/sections/about/present'); ?>

  <!-- Photography Flow Section -->
  <div id="photography-flow"></div>
  <?php get_template_part('template-parts/sections/about/photography-flow'); ?>

  <!-- FAQ Section -->
  <?php get_template_part('template-parts/sections/about/faq'); ?>

  <!-- Contact & Booking Section -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>

</main>

<?php get_template_part('template-parts/components/footer'); ?>

<?php get_footer(); ?>