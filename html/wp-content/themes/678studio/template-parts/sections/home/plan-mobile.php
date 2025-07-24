<?php
/**
 * Plan Mobile Section - 撮影プランモバイルセクション
 */
?>

<section class="plan-mobile">
  <div class="plan-mobile__container">
    <div class="plan-mobile__content">
      
      <!-- 画像エリア -->
      <div class="plan-mobile__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-1.jpg" alt="撮影プラン画像">
      </div>

      <!-- タイトルエリア -->
      <div class="plan-mobile__title">
        <!-- Title Section -->
        <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => 'Plan',
          'title_text' => '撮影プラン',
          'content_text' => '678撮影の流れや、<br>撮影プランについてはこちらから',
          'class' => 'plan-mobile-title-section'
        ]); ?>
      </div>

      <!-- ボタンエリア -->
      <div class="plan-mobile__button">
        <?php get_template_part('template-parts/components/detail-button', null, [
          'text' => '詳しく見る',
          'variant' => 'detail',
          'icon' => 'none'
        ]); ?>
      </div>

    </div>
  </div>
</section>