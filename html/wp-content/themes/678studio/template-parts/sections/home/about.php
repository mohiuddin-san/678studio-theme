<?php
/**
 * About Section with Left-Right Layout
 */
?>

<section class="about-section" id="about-section">
  <div class="about-section__container">
    <div class="about-section__left scroll-animate-item" data-delay="0">
      <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'custom_class' => 'about-title-section',
          'label_text' => 'What is 678?',
          'title_text' => 'ロクナナハチ<br>撮影とは？',
          'content_text' => '60代・70代・80代のための<br>特別な撮影サービスです'
      ]); ?>
    </div>
    <div class="about-section__right">
      <!-- 3つのサービスカード -->
      <div class="about-cards">
        <!-- カード1: ヘアメイク -->
        <div class="about-card about-card--hairmake scroll-animate-item" data-delay="0.2">
          <div class="about-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-1.jpg" alt="ヘアメイク">
          </div>
          <div class="about-card__content">
            <div class="about-card__tag">ヘアメイク</div>
            <div class="about-card__text">年齢に合わせた美しいメイクを施します</div>
          </div>
        </div>

        <!-- カード2: 撮影技術 -->
        <div class="about-card about-card--shooting about-card--reverse scroll-animate-item" data-delay="0.4">
          <div class="about-card__content">
            <div class="about-card__tag">撮影技術</div>
            <div class="about-card__text">シニア世代の方々の魅力を最大限に引き出します</div>
          </div>
          <div class="about-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-2.jpg" alt="撮影技術">
          </div>
        </div>

        <!-- カード3: 変身体験×記念日 -->
        <div class="about-card about-card--experience scroll-animate-item" data-delay="0.6">
          <div class="about-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-3.jpg" alt="変身体験">
          </div>
          <div class="about-card__content">
            <div class="about-card__tag">変身体験×記念日</div>
            <div class="about-card__text">美しく変身する喜びと記念を残す感動の<br>両方を体験</div>
          </div>
        </div>
      </div>
    </div>
    <div class="about-section__bottom">
      <!-- 新しく追加されたボックス -->
    </div>
  </div>
</section>