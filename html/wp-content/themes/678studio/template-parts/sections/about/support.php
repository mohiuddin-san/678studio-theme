<?php
/**
 * About Support Section
 * Description: Support section with photos and text content
 */
?>

<section class="about-support">
  <!-- PC Version -->
  <div class="about-support__grid pc">

    <!-- Grid Item 1: Photos (Left side) -->
    <div class="about-support__photos gitem">
      <div class="about-support__photo about-support__photo--main">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-portrait.png" alt="ポートレート写真"
          class="about-support__photo-img">
      </div>
    </div>

    <!-- Grid Item 2: Background (Yellow) -->
    <div class="about-support__background gitem">
      <!-- Background decoration with icon -->
      <div class="about-support__background-icon">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-portrait-2.svg" alt="カメラアイコン"
          class="about-support__background-icon-img">
      </div>
    </div>

    <!-- Grid Item 3: Content (White background main area) -->
    <div class="about-support__content gitem">
      <div class="about-support__content-inner">
        <!-- Title -->
        <div class="about-support__title">
          <h2 class="about-support__title-text">「今」を残す ─ ポートレート</h2>
        </div>

        <!-- Description -->
        <div class="about-support__description">
          <p class="about-support__description-text">
            あなたらしい今の姿を、写真館で撮影してみませんか？
            何気ない日常の一瞬も、写真に残せばかけがえのない記録となります。自由で特別な雰囲気に包まれて、「今の自分」を未来に伝える一枚を残してみてはいかがでしょう。
          </p>
        </div>
      </div>
    </div>

  </div>

  <!-- SP Version -->
  <div class="about-support__container sp">

    <!-- Title Section -->
    <div class="about-support__title">
      <h3 class="about-support__title-text">「今」を残す ─ ポートレート</h3>
    </div>

    <!-- Description Section -->
    <div class="about-support__description">
      <p class="about-support__description-text">
        あなたらしい今の姿を、写真館で撮影して残してみませんか？　何気ない日常の一瞬も、写真に残せばかけがえのない記録となります。節目や特別な機会に限らず、「今の自分」を未来に伝える一枚を残してみてはいかがでしょう。
      </p>
    </div>

    <!-- Photo Section -->
    <div class="about-support__photo">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-portrait.png" alt="ポートレート写真" class="about-support__photo-img">
    </div>

  </div>
</section>