<?php
/**
 * Thoughts Section
 * 
 * 統合されたThoughtsセクション
 */
?>

<section class="thoughts-section">
  <div class="thoughts-section__inner">
    <div class="thoughts-section__label">
      <?php get_template_part('template-parts/components/thoughts-label', null, [
                'text' => 'Our Thoughts'
            ]); ?>
    </div>

    <div class="thoughts-section__title">
      <?php get_template_part('template-parts/components/thoughts-title', null, [
                'title' => 'ロクナナハチ<br>撮影への想い'
            ]); ?>
    </div>

    <div class="thoughts-section__content">
      <?php get_template_part('template-parts/components/thoughts-text', null, [
                'text' => '10年以上に渡り培ってきたシニア撮影の技術とノウハウを全国の写真館の皆様と共有し、より多くのシニアの方々に人生の大切な瞬間を美しく残していただきたいという想いから、678（ロクナナハチ）撮影の監修を行っています。'
            ]); ?>
    </div>
  </div>
</section>