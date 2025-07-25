<?php
/**
 * About Mobile Section Template - PC版と統一された内容
 */
?>

<section class="about-mobile">
  <div class="about-mobile__container">
    <div class="about-mobile__content">
      
      <!-- (1) Title Section -->
      <div class="about-mobile__title">
        <?php get_template_part('template-parts/components/title-section', null, [
          'variant' => 'default',
          'label_text' => 'What is 678?',
          'title_text' => 'ロクナナハチ<br>撮影とは？',
          'content_text' => '60代・70代・80代のための<br>特別な撮影サービスです',
          'class' => 'about-mobile-title-section'
        ]); ?>
      </div>

      <!-- Service Cards -->
      <div class="about-mobile__cards">
        
        <!-- (2) ヘアメイクカード -->
        <div class="about-mobile-card about-mobile-card--hairmake">
          <div class="about-mobile-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-1.jpg" alt="ヘアメイク">
          </div>
          <div class="about-mobile-card__content">
            <div class="about-mobile-card__tag">ヘアメイク</div>
            <div class="about-mobile-card__text">年齢に合わせた美しいメイクを施します</div>
          </div>
        </div>

        <!-- (3) 撮影技術カード -->
        <div class="about-mobile-card about-mobile-card--shooting">
          <div class="about-mobile-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-2.jpg" alt="撮影技術">
          </div>
          <div class="about-mobile-card__content">
            <div class="about-mobile-card__tag">撮影技術</div>
            <div class="about-mobile-card__text">シニア世代の方々の魅力を最大限に引き出します</div>
          </div>
        </div>

        <!-- (4) 変身体験×記念日カード -->
        <div class="about-mobile-card about-mobile-card--experience">
          <div class="about-mobile-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-3.jpg" alt="変身体験">
          </div>
          <div class="about-mobile-card__content">
            <div class="about-mobile-card__tag">変身体験×記念日</div>
            <div class="about-mobile-card__text">美しく変身する喜びと記念を残す感動の両方を体験</div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>