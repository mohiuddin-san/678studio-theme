<?php
/**
 * Family Photography Section - About Page Specific
 * 家族写真セクション
 */
?>

<section class="family-photography" id="family-photography-section">
  <div class="family-photography__container">

    <!-- Grid Layout based on Figma measurements -->
    <div class="family-photography__grid">

      <!-- Grid Item 1 - 517px column -->
      <div class="family-photography__item family-photography__item--1 scroll-animate-item" data-delay="0">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/kazoku.jpg" alt="家族写真の例"
          class="family-photography__image">
      </div>

      <!-- Grid Item 2 - 243px column -->
      <div class="family-photography__item family-photography__item--2">
      </div>

      <!-- Grid Item 3 - 78px column (Text content) -->
      <div class="family-photography__item family-photography__item--3">
        <div class="family-photography__content">
          <div class="family-photography__title-box">
            <h3 class="family-photography__title">家族写真</h3>
          </div>
          <div class="family-photography__text-content">
            <p class="family-photography__description">
              ご家族が集まる貴重な機会に、絆を美しく残す家族写真。<br>
              お孫さんとの触れ合いも自然に撮影いたします。
            </p>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>