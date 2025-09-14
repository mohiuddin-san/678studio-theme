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
          'title_text' => 'ロクナナハチ撮影の<br>
3つのポイント',
          'content_text' => '60代・70代・80代という人生の節目に写真館で撮影をし、あなたの「今」を残していただける、そんな撮影です。
ロクナナハチ撮影に登録している店舗では、共通して「撮影」「ヘアメイク」「撮影データのお渡し」をご提供しています。それ以外のサービス内容は各店舗によって異なります。
店舗ごとに特色を活かしたプランがあり、あなただけの特別な撮影体験をお楽しみいただけます。',
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
            <div class="about-mobile-card__text">年齢に合わせた自然で美しいメイクを施します。経験豊富なスタッフが、その方らしい魅力を引き出します。</div>
          </div>
        </div>

        <!-- (3) 撮影技術カード -->
        <div class="about-mobile-card about-mobile-card--shooting">
          <div class="about-mobile-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-2.jpg" alt="撮影技術">
          </div>
          <div class="about-mobile-card__content">
            <div class="about-mobile-card__tag">撮影</div>
            <div class="about-mobile-card__text">シニア世代の方々の魅力を最大限に引き出します。リラックスできる雰囲気で、自然な笑顔を残します。</div>
          </div>
        </div>

        <!-- (4) 変身体験×記念日カード -->
        <div class="about-mobile-card about-mobile-card--experience">
          <div class="about-mobile-card__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-3.jpg" alt="変身体験">
          </div>
          <div class="about-mobile-card__content">
            <div class="about-mobile-card__tag">撮影データのお渡し</div>
            <div class="about-mobile-card__text">撮影したデータをお渡しいたします。大切な瞬間を、何度でも振り返っていただけます。</div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- About Mobile Section Button -->
<div class="about-mobile-button-wrapper">
  <div class="about-mobile-button">
    <a href="#" class="about-details-button">詳しく見る</a>
  </div>
</div>