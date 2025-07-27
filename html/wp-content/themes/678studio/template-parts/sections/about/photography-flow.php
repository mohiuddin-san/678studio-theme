<?php
/**
 * Photography Flow Section - About Page Specific
 * 撮影の流れセクション
 */
?>

<section class="photography-flow-section" id="photography-flow-section">
  <div class="photography-flow-section__container">

    <!-- ヘッダーエリア -->
    <div class="photography-flow-section__header scroll-animate-item" data-delay="0">
      <div class="photography-flow-section__label">
        <span class="photography-flow-section__label-text">Steps</span>
      </div>
      <h2 class="photography-flow-section__title">撮影の流れ</h2>
    </div>

    <!-- フローカード一覧 -->
    <div class="photography-flow-section__cards scroll-animate-item" data-delay="0.2">

      <!-- フローカード1 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow1.jpg"
            alt="お問い合わせ・ご予約">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">1</span>
            </div>
            <div class="flow-card__badge">お問い合わせ・ご予約</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            お電話またはWebフォームからお気軽にお問い合わせください。撮影日時やご希望をお聞かせいただき、最適なプランをご案内いたします。
          </p>
        </div>
      </div>

      <!-- フローカード2 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow2.jpg"
            alt="事前カウンセリング">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">2</span>
            </div>
            <div class="flow-card__badge">事前カウンセリング</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            撮影当日または事前に、撮影の目的やご希望のイメージをお聞きします。お客様のご要望に合わせて、最適なプランをご提案いたします。
          </p>
        </div>
      </div>

      <!-- フローカード3 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow3.jpg"
            alt="撮影">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">3</span>
            </div>
            <div class="flow-card__badge">撮影</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            経験豊富なカメラマンが、リラックスした雰囲気の中で撮影を行います。お客様の自然な表情を表現を引き出し、美しい瞬間を切り取ります。
          </p>
        </div>
      </div>

      <!-- フローカード4 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow4.jpg"
            alt="写真確定・データ納品">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">4</span>
            </div>
            <div class="flow-card__badge">写真確定・データ納品</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            撮影後、お客様と一緒に写真を選定いたします。選定した写真のデータをCDやUSBにてお渡しいたします。
          </p>
        </div>
      </div>

    </div>

  </div>
</section>