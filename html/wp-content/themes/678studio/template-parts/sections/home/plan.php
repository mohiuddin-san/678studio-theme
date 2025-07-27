<?php
/**
 * Plan Section - 撮影プランセクション
 */
?>

<section class="plan-section" id="plan-section">
  <div class="plan-section__container">

    <!-- ボックス1 - タイトルエリア -->
    <div class="plan-box plan-box--1 scroll-animate-item" data-delay="0">
      <!-- Title Section -->
      <?php get_template_part('template-parts/components/title-section', null, [
        'variant' => 'default',
        'label_text' => 'Plan',
        'title_text' => '撮影プラン',
        'content_text' => '678撮影の流れや、<br>撮影プランについてはこちらから'
      ]); ?>
    </div>

    <!-- ボックス2 - 背景 -->
    <div class="plan-box plan-box--2 scroll-animate-item" data-delay="0.2">
      <div class="plan-box__button">
        <?php get_template_part('template-parts/components/detail-button', null, [
          'text' => '詳しく見る',
          'url' => home_url('/about/'),
          'variant' => 'detail',
          'icon' => 'none'
        ]); ?>
      </div>
    </div>

    <!-- ボックス3 - 画像エリア -->
    <div class="plan-box plan-box--3">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-1.jpg" alt="撮影プラン画像" class="plan-box__image">
    </div>

  </div>
</section>