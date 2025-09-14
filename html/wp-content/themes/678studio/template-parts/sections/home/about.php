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
          'title_text' => 'ロクナナハチ撮影の<br>
3つのポイント',
          'content_text' => '60代・70代・80代という人生の節目に写真館で撮影をし、あなたの「今」を残していただける、そんな撮影です。
ロクナナハチ撮影に登録している店舗では、共通して「撮影」「ヘアメイク」「撮影データのお渡し」をご提供しています。それ以外のサービス内容は各店舗によって異なります。
店舗ごとに特色を活かしたプランがあり、あなただけの特別な撮影体験をお楽しみいただけます。'
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
            <div class="about-card__text">年齢に合わせた自然で美しいメイクを施します。<br>経験豊富なスタッフが、その方らしい魅力を引き出します。</div>
          </div>
        </div>

        <!-- カード2: 撮影技術 -->
        <div class="about-card about-card--shooting about-card--reverse scroll-animate-item" data-delay="0.4">
          <div class="about-card__content">
            <div class="about-card__tag">撮影</div>
            <div class="about-card__text">シニア世代の方々の魅力を最大限に引き出します。<br>リラックスできる雰囲気で、自然な笑顔を残します。</div>
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
            <div class="about-card__tag">撮影データのお渡し</div>
            <div class="about-card__text">撮影したデータをお渡しいたします。<br>大切な瞬間を、何度でも振り返っていただけます。</div>
          </div>
        </div>
      </div>
    </div>
    <div class="about-section__bottom">
      <!-- 新しく追加されたボックス -->
    </div>
  </div>
</section>

<!-- About Section Button (Outside of grid) -->
<div class="about-section-button-wrapper">
  <div class="about-section-button">
    <a href="#" class="about-details-button">詳しく見る</a>
  </div>
</div>