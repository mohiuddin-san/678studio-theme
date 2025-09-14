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
          'title_text' => 'シニア世代の写真を<br>
思いを込めて撮影する。<br>
そんな写真館と出会えます',
          'content_text' => 'シニア世代の方に写真館で撮影を楽しんでいただきたいといっても、多くの写真館で子どもの写真が主に飾られており、「自分は対象ではないのかも」と感じ、撮影に踏み出しにくいことがあります。<br><br>
当サイトは、シニア世代の撮影を行う写真館が登録している専用サイトです。
近隣の店舗検索からそのまま予約もできるので、「撮りたい」と思ったそのときの気持ちを大切に、写真館を探していただけます。',
          'class' => 'thoughts-layout-mobile-title-section'
        ]); ?>
      </div>
      <div class="thoughts-layout-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-pic-1.png" alt="想い画像">
      </div>
    </div>
  </div>
</section>