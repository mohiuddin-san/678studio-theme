<?php
/**
 * About Present Section - support セクションと同じ構造
 * プレゼント撮影セクション
 */
?>

<section class="about-present" id="present-section">
  <!-- PC Version -->
  <div class="about-present__grid pc">

    <!-- Grid Item 1: Photos (Left side) -->
    <div class="about-present__photos gitem">
      <div class="about-present__photo about-present__photo--main">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-present.jpg" alt="プレゼント写真"
          class="about-present__photo-img">
      </div>
    </div>

    <!-- Grid Item 2: Background (Yellow) -->
    <div class="about-present__background gitem">
      <!-- Background decoration with icon -->
      <div class="about-present__background-icon">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-present-icon.svg" alt="プレゼントアイコン"
          class="about-present__background-icon-img">
      </div>
    </div>

    <!-- Grid Item 3: Content (White background main area) -->
    <div class="about-present__content gitem">
      <div class="about-present__content-inner">
        <!-- Title -->
        <div class="about-present__title">
          <h2 class="about-present__title-text">贈り物として選ばれる─プレゼント</h2>
        </div>

        <!-- Description -->
        <div class="about-present__description">
          <p class="about-present__description-text">
            ご家族への贈り物として人気の記念撮影。お誕生日、記念日、長寿のお祝いなど、大切な方への心のこもったプレゼントとして多くの方に選ばれています。美しく仕上げられた写真は、きっと喜ばれる特別な贈り物になります。
          </p>
        </div>
      </div>
    </div>

  </div>

  <!-- SP Version -->
  <div class="about-present__container sp">

    <!-- Title Section -->
    <div class="about-present__title">
      <h3 class="about-present__title-text">贈り物として選ばれる─プレゼント</h3>
    </div>

    <!-- Description Section -->
    <div class="about-present__description">
      <p class="about-present__description-text">
        ご家族への贈り物として人気の記念撮影。お誕生日、記念日、長寿のお祝いなど、大切な方への心のこもったプレゼントとして多くの方に選ばれています。美しく仕上げられた写真は、きっと喜ばれる特別な贈り物になります。
      </p>
    </div>

    <!-- Photo Section -->
    <div class="about-present__photo">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-present.jpg" alt="プレゼント写真"
        class="about-present__photo-img">
    </div>

  </div>
</section>