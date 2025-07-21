<?php
/**
 * Plan Section - 撮影プランセクション
 */
?>

<section class="plan-section">
  <div class="plan-section__container">

    <!-- ボックス1 - 画像エリア -->
    <div class="plan-box plan-box--1">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-1.jpg" alt="撮影風景"
        class="plan-box__image">
    </div>

    <!-- ボックス2 - 背景 -->
    <div class="plan-box plan-box--2">
    </div>

    <!-- ボックス3 - テキストエリア -->
    <div class="plan-box plan-box--3">
      <div class="plan-box__content">
        <div class="plan-box__label">
          <span class="plan-label">plan</span>
        </div>
        
        <h2 class="plan-box__title">撮影プラン</h2>
        
        <p class="plan-box__text">
          678撮影の流れや、<br>
          撮影プランについてはこちらから
        </p>
        
        <div class="plan-box__button">
          <?php get_template_part('template-parts/components/detail-button', null, [
              'text' => '詳しく見る',
              'variant' => 'detail',
              'icon' => 'none'
          ]); ?>
        </div>
      </div>
    </div>

  </div>
</section>