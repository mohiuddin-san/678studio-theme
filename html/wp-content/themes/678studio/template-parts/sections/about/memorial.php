<?php
/**
 * Memorial Section - Portrait Photography のX軸180度反転版
 * 記念写真セクション
 */
?>

<section class="about-memorial" id="memorial-section">
  <!-- PC Version -->
  <div class="about-memorial__grid pc">

    <!-- Grid Item 1: Content (Left side - supportのContentを左に反転) -->
    <div class="about-memorial__content gitem">
      <div class="about-memorial__content-inner">
        <!-- Title -->
        <div class="about-memorial__title">
          <h2 class="about-memorial__title-text">お祝いや記念日に─プレゼント</h2>
        </div>

        <!-- Description -->
        <div class="about-memorial__description">
          <p class="about-memorial__description-text">
            自然な表情で、ご家族に安心してお任せいただける生前遺影撮影。終活の一環として撮影される方も増えています。
          </p>
        </div>
      </div>
    </div>

    <!-- Grid Item 2: Background (Peach color) -->
    <div class="about-memorial__background gitem">
      <!-- Background decoration with icon -->
      <div class="about-memorial__background-icon">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-memorial-icon.svg" alt="記念撮影アイコン"
          class="about-memorial__background-icon-img">
      </div>
    </div>

    <!-- Grid Item 3: Photos (Right side - supportのPhotosを右に反転) -->
    <div class="about-memorial__photos gitem">
      <div class="about-memorial__photo about-memorial__photo--main">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-memorial.jpg" alt="記念写真"
          class="about-memorial__photo-img">
      </div>
    </div>

  </div>

  <!-- SP Version -->
  <div class="about-memorial__container sp">

    <!-- Title Section -->
    <div class="about-memorial__title">
      <h3 class="about-memorial__title-text">お祝いや記念日に─プレゼント</h3>
    </div>

    <!-- Description Section -->
    <div class="about-memorial__description">
      <p class="about-memorial__description-text">
        自然な表情で、ご家族に安心してお任せいただける生前遺影撮影。終活の一環として撮影される方も増えています。
      </p>
    </div>

    <!-- Photo Section -->
    <div class="about-memorial__photo">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-memorial.jpg" alt="記念写真"
           class="about-memorial__photo-img">
    </div>

  </div>
</section>