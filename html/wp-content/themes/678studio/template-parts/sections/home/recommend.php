<?php
/**
 * Recommend Section - こんな方におすすめ
 */
?>

<section class="recommend-section">
  <div class="recommend-section__container">
    <!-- タイトルエリア -->
    <div class="recommend-section__header">
      <div class="recommend-section__icon">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/light.svg" alt="太陽アイコン"
          class="recommend-section__sun-icon">
      </div>
      <h2 class="recommend-section__title">こんな方におすすめ</h2>
    </div>

    <!-- タグエリア -->
    <div class="recommend-section__tags">
      <!-- 1行目 -->
      <div class="recommend-section__tag-row">
        <div class="recommend-section__tag">60代（還暦）</div>
        <div class="recommend-section__tag">70代（古希）</div>
        <div class="recommend-section__tag">80代（傘寿）</div>
        <div class="recommend-section__tag">90代（卒寿）</div>
      </div>
      <!-- 2行目 -->
      <div class="recommend-section__tag-row">
        <div class="recommend-section__tag">遺影写真</div>
        <div class="recommend-section__tag">記念撮影</div>
        <div class="recommend-section__tag">家族写真</div>
        <div class="recommend-section__tag">プロフィール写真</div>
      </div>
    </div>
  </div>
  <!-- ボタンエリア -->
  <div class="recommend-section__button">
    <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => '詳しく見る',
          'url' => '/about/',
          'bg_color' => 'blue'
      ]); ?>
  </div>
</section>