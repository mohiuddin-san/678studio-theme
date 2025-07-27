<?php
/**
 * Gallery Mobile Section Template
 */
?>

<section class="gallery-mobile">
  <div class="gallery-mobile__container">
    <div class="gallery-mobile__content">
      <div class="gallery-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-1.png" alt="ギャラリー画像">
      </div>
      <div class="gallery-mobile__title">
        <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => 'Gallery',
          'title_text' => 'ギャラリー',
          'content_text' => '678撮影のギャラリーや、<br>作品についてはこちらから',
          'class' => 'gallery-mobile-title-section'
        ]); ?>
      </div>
      <div class="gallery-mobile__button">
        <?php get_template_part('template-parts/components/detail-button', null, [
          'text' => '詳しく見る',
          'variant' => 'detail',
          'icon' => 'none'
        ]); ?>
      </div>
    </div>
  </div>
</section>