<?php
/**
 * Gallery Section - ギャラリーセクション
 */
?>

<section class="gallery-section">
  <div class="gallery-section__container">





    <!-- ボックス1 -->
    <div class="gallery-box gallery-box--1">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-1.png" alt="ギャラリー画像1"
        class="gallery-box__image">
    </div>

    <!-- ボックス2 -->
    <div class="gallery-box gallery-box--2">
    </div>

    <!-- ボックス3 -->
    <div class="gallery-box gallery-box--3">
      <!-- Title Section -->
      <?php get_template_part('template-parts/components/title-section', null, [
        'variant' => 'default',
        'label_text' => 'Our Gallery',
        'title_text' => 'ギャラリー',
         'content_text' =>
  '様々なお客様の笑顔がここから見れます'
    ]); ?>
    </div>

    <!-- ボックス4 -->
    <div class="gallery-box gallery-box--4">
      <div class="gallery-box__button">
        <?php get_template_part('template-parts/components/detail-button', null, [
          'text' => '写真ギャラリーを見る',
          'variant' => 'gallery',
          'icon' => 'cam'
      ]); ?>
      </div>
    </div>
  </div>
</section>