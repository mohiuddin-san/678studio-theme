<?php
/**
 * Template Name: 678撮影について
 * Description: 678撮影についてのページテンプレート
 */

get_header();
?>

<main class="main-content about-page">
  <!-- About FV Section (Desktop) -->
  <section class="about-fv pc">
    <div class="about-fv__container">

      <!-- Text Content -->
      <div class="about-fv__content">
        <div class="about-fv__text">
          <div class="title-section title-section--default about-fv__title-section">
            <div class="title-section__title">
              <div class="thoughts-title">
                <h2 class="thoughts-title__text">
                  <span class="thoughts-title__line">
                    ロクナナハチ撮影
                    <img class="thoughts-title__underline"
                      src="<?php echo get_template_directory_uri(); ?>/assets/images/underline.svg" alt="">
                  </span>
                  <span class="thoughts-title__line about-fv__subtitle">
                    とは？
                    <img class="thoughts-title__underline"
                      src="<?php echo get_template_directory_uri(); ?>/assets/images/underline.svg" alt="">
                  </span>
                </h2>
              </div>
            </div>
            <div class="title-section__content">
              <div class="thoughts-text">
                60代・70代・80代の方々のための<br>特別な撮影サービス
              </div>
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
  <section class="about-mobile-fv sp">
    <div class="about-mobile-fv__container">
      <!-- Grid Layout Container -->
      <div class="about-mobile-fv__grid">
        <!-- Item 1 -->
        <div class="about-mobile-fv__item1">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-fv-sp.jpg" alt="ロクナナハチ撮影とは？">
        </div>

        <!-- Item 2 -->
        <div class="about-mobile-fv__item2">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/sp-about-fv-text.svg" alt="ロクナナハチ撮影とは？">
        </div>
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

  <!-- About Link Section -->
  <?php get_template_part('template-parts/components/about-link', null, [
    'buttons' => [
      ['text' => '利用シーン', 'url' => '#usage-scenes'],
      ['text' => '撮影プラン', 'url' => '#photography-plans'],
      ['text' => '撮影の流れ', 'url' => '#photography-flow']
    ]
  ]); ?>

  <!-- Memorial Photography Section (利用シーン) -->
  <div id="usage-scenes"></div>
  <?php get_template_part('template-parts/sections/about/memorial-photography', null, [
    'title' => '記念撮影',
    'description' => '特別な瞬間を美しく残します',
    'images' => [
      ['src' => 'memorial-1.jpg', 'alt' => '記念撮影1'],
      ['src' => 'memorial-2.jpg', 'alt' => '記念撮影2'],
      ['src' => 'memorial-3.jpg', 'alt' => '記念撮影3'],
      ['src' => 'memorial-4.jpg', 'alt' => '記念撮影4'],
    ]
  ]); ?>

  <!-- Portrait Photography Section -->
  <?php get_template_part('template-parts/sections/about/portrait-photography'); ?>

  <!-- Family Photography Section -->
  <?php get_template_part('template-parts/sections/about/family-photography'); ?>

  <!-- Photography Plans Section -->
  <div id="photography-plans"></div>
  <?php get_template_part('template-parts/sections/about/photography-plans'); ?>

  <!-- Options Section -->
  <?php get_template_part('template-parts/sections/about/options'); ?>

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