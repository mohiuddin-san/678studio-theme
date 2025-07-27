<?php
/**
 * Thoughts Layout Section - Simple 3 Boxes Test
 */
?>

<section class="thoughts-layout-section" id="thoughts-layout-section">
  <div class="thoughts-layout-section__container">
    <div class="thoughts-layout-section__wrapper">
      <div class="thoughts-layout-section__box-1 scroll-animate-item" data-delay="0"><img
          src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-pic-1.png" alt=""></div>
      <div class="thoughts-layout-section__box-2 scroll-animate-item" data-delay="0.2"></div>
      <div class="thoughts-layout-section__box-3 scroll-animate-item" data-delay="0.1">
        <div class="thoughts-layout-section__inner">
          <div class="thoughts-section__label scroll-animate-item" data-delay="0.4">
            <?php get_template_part('template-parts/components/thoughts-label', null, [
                        'text' => 'Our Thoughts'
                    ]); ?>
          </div>

          <div class="thoughts-section__title scroll-animate-item" data-delay="0.6">
            <?php get_template_part('template-parts/components/thoughts-title', null, [
                        'title' => 'ロクナナハチ撮影<br>への想い'
                    ]); ?>
          </div>

          <div class="thoughts-section__content scroll-animate-item" data-delay="0.8">
            <?php get_template_part('template-parts/components/thoughts-text', null, [
                        'text' => '10年以上に渡り培ってきたシニア撮影の技術とノウハウを全国の写真館の皆様と共有し、より多くのシニアの方々に人生の大切な瞬間を美しく残していただきたいという想いから、678（ロクナナハチ）撮影の監修を行っています。'
                    ]); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>