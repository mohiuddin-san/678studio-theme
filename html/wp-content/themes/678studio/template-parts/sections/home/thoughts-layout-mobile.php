<?php
/**
 * Thoughts Layout Mobile Section Template
 */
?>

<section class="thoughts-layout-mobile">
  <div class="thoughts-layout-mobile__container">
    <div class="thoughts-layout-mobile__content">
      <div class="thoughts-layout-mobile__title">
        <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => 'Thoughts',
          'title_text' => 'ロクナナハチ撮影<br>への想い',
          'content_text' => '678撮影の想いや、<br>撮影に対する考えについてはこちらから',
          'class' => 'thoughts-layout-mobile-title-section'
        ]); ?>
      </div>
      <div class="thoughts-layout-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-pic-1.png" alt="想い画像">
      </div>
    </div>
  </div>
</section>