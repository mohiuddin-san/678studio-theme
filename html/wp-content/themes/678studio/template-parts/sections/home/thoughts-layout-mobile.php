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
          'content_text' => '10年以上に渡り培ってきたシニア撮影の技術とノウハウを全国の写真館の皆様と共有し、より多くのシニアの方々に人生の大切な瞬間を美しく残していただきたいという想いから、678（ロクナナハチ）撮影の監修を行っています。',
          'class' => 'thoughts-layout-mobile-title-section'
        ]); ?>
      </div>
      <div class="thoughts-layout-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-pic-1.png" alt="想い画像">
      </div>
    </div>
  </div>
</section>