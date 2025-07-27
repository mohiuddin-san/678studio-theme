<?php
/**
 * Portrait Photography Section - About Page Specific
 * 遺影撮影セクション
 */
?>

<section class="portrait-photography" id="portrait-photography-section">
  <div class="portrait-photography__container">

    <!-- Grid Layout based on Figma measurements (mirrored from memorial) -->
    <div class="portrait-photography__grid">

      <!-- Grid Item 1 - Main content box (left side) -->
      <div class="portrait-photography__item portrait-photography__item--1 scroll-animate-item" data-delay="0">
        <div class="portrait-photography__content">
          <div class="portrait-photography__title-box">
            <h3 class="portrait-photography__title">遺影撮影</h3>
          </div>
          <div class="portrait-photography__text-content">
            <p class="portrait-photography__description">
              自然な表情で、ご家族に安心してお任せいただける<br>
              遺影撮影。生前整理の一環として撮影される方も増<br>
              えています。
            </p>
            <ul class="portrait-photography__features">
              <li class="portrait-photography__feature">
                ☑️
                <span>デジタル・プリント両対応</span>
              </li>
              <li class="portrait-photography__feature">
                ☑️
                <span>生前整理としての活用</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Grid Item 2 - Background box -->
      <div class="portrait-photography__item portrait-photography__item--2 scroll-animate-item" data-delay="0.2">
      </div>

      <!-- Grid Item 3 - Image (right side) -->
      <div class="portrait-photography__item portrait-photography__item--3 scroll-animate-item" data-delay="0.4">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/iei.png" alt="遺影撮影の例"
          class="portrait-photography__image">
      </div>

    </div>

  </div>
</section>