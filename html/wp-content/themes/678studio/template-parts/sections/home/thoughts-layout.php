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
                        'title' => 'シニア世代の写真を<br>
思いを込めて撮影する。<br>
そんな写真館と出会えます'
                    ]); ?>
          </div>

          <div class="thoughts-section__content scroll-animate-item" data-delay="0.8">
            <?php get_template_part('template-parts/components/thoughts-text', null, [
                        'text' => 'シニア世代の方に写真館で撮影を楽しんでいただきたいといっても、多くの写真館で子どもの写真が主に飾られており、「自分は対象ではないのかも」と感じ、撮影に踏み出しにくいことがあります。
当サイトは、シニア世代の撮影を行う写真館が登録している専用サイトです。
近隣の店舗検索からそのまま予約もできるので、「撮りたい」と思ったそのときの気持ちを大切に、写真館を探していただけます。'
                    ]); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>