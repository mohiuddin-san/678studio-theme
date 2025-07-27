<?php
/**
 * Photography Plans Section - About Page Specific
 * 撮影プランセクション
 */
?>

<section class="photography-plans" id="photography-plans-section">
  <div class="photography-plans__container">

    <!-- Grid Layout -->
    <div class="photography-plans__grid">

      <!-- Grid Item 1 -->
      <div class="photography-plans__item photography-plans__item--1 scroll-animate-item" data-delay="0">
        <div class="photography-plans__content">
          <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => '678撮影プラン',
          'title_text' => '￥24,000<span class="tax-included">(税込)</span>',
          'content_text' => '・写真撮影<br>・ヘアメイク<br>・撮影データ1カット<br>・貸衣装 <br><small style="font-size: 0.8em; color: #999;">※貸衣装の有無は店舗により異なります</small>',
          'class' => 'gallery-mobile-title-section'
        ]); ?>
        </div>
      </div>

      <!-- Grid Item 2 -->
      <div class="photography-plans__item photography-plans__item--2 scroll-animate-item" data-delay="0.2">
      </div>

      <!-- Grid Item 3 -->
      <div class="photography-plans__item photography-plans__item--3 scroll-animate-item" data-delay="0.4">
        <div class="photography-plans__content">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-plan-pic.jpg" alt="撮影プラン">
        </div>
      </div>

    </div>

  </div>
</section>