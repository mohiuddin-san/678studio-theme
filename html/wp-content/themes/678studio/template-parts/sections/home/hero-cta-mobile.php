<?php
/**
 * Hero CTA Mobile Section Component
 * SP専用の写真館を探すボタンセクション
 */
?>

<section class="hero-cta-mobile" id="hero-cta-mobile">
  <div class="hero-cta-mobile__container">
    <a href="<?php echo home_url('/stores'); ?>" class="hero-cta-mobile__button">
      写真館を探す
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/search.svg" alt="検索"
        class="hero-cta-mobile__icon">
    </a>
  </div>
</section>