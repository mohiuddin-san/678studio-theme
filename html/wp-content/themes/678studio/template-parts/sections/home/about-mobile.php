<?php
/**
 * About Mobile Section Template
 */
?>

<section class="about-mobile">
  <div class="about-mobile__container">
    <div class="about-mobile__content">
      <div class="about-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-1.jpg" alt="私たちについて画像">
      </div>
      <div class="about-mobile__title">
        <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => 'About',
          'title_text' => '私たちについて',
          'content_text' => '678撮影について、<br>私たちの想いやサービスについてはこちらから',
          'class' => 'about-mobile-title-section'
        ]); ?>
      </div>
      <div class="about-mobile__button">
        <?php get_template_part('template-parts/components/detail-button', null, [
          'text' => '詳しく見る',
          'variant' => 'detail',
          'icon' => 'none'
        ]); ?>
      </div>
    </div>
  </div>
</section>