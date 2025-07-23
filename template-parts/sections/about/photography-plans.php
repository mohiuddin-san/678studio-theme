<?php
/**
 * Photography Plans Section - About Page Specific
 * 撮影プランセクション
 */
?>

<section class="photography-plans">
  <div class="photography-plans__container">

    <!-- Grid Layout -->
    <div class="photography-plans__grid">

      <!-- Grid Item 1 -->
      <div class="photography-plans__item photography-plans__item--1">
        <?php get_template_part('template-parts/components/title-section', null, [
          'label_text' => '678撮影プラン',
          'title_text' => '678撮影プラン',
          'content_text' => '撮影時間 2時間<br>ヘアメイク<br>写真撮影(3カット)<br>データ納品'
        ]); ?>
      </div>

      <!-- Grid Item 2 -->
      <div class="photography-plans__item photography-plans__item--2">
      </div>

      <!-- Grid Item 3 -->
      <div class="photography-plans__item photography-plans__item--3">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-pic-1.png"
          class="photography-plans__image" alt="撮影プラン">
      </div>

    </div>

  </div>
</section>