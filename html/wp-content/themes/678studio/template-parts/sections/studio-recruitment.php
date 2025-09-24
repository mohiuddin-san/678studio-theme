<?php
/**
 * Studio Recruitment Section - 掲載希望の写真館向けページ
 */
?>

<section class="contact-section">
  <!-- Breadcrumb Section -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => '掲載希望の写真館へ', 'url' => '']
    ]
  ]); ?>

  <!-- Explanation Section -->
  <div class="recruitment-explanation-section">
    <h1 class="recruitment-explanation__title">掲載希望の写真館へ</h1>
  </div>

  <!-- Hero/Eyecatch Section -->
  <div class="recruitment-hero-section">
    <div class="recruitment-hero-grid">
      <div class="recruitment-hero__image">
        <!-- レスポンシブ画像 -->
        <picture>
          <source media="(max-width: 767px)" srcset="<?php echo get_template_directory_uri(); ?>/assets/images/dl-icatch-sp.jpg">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/dl-icatch.jpg" alt="シニア世代撮影">
        </picture>
      </div>
      <div class="recruitment-hero__overlay">
        <div class="recruitment-hero__logo">
          <!-- ロゴ画像（headerと同じ） -->
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="678 Logo">
        </div>
        <div class="recruitment-hero__text">
          <p>60代・70代・80代。<br>
          シニア世代の撮影で、あなたの写真館に<br>
          幅広い年代のお客さまを。</p>
        </div>
      </div>
    </div>
  </div>

  <!-- About Section (ロクナナハチ撮影でできること) -->
  <div class="recruitment-about-section">
    <div class="recruitment-about-container">
      <div class="recruitment-about__frame-left">
        <!-- レスポンシブSVG -->
        <picture>
          <source media="(max-width: 767px)" srcset="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about-upper-sp.svg">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about-left.svg" alt="">
        </picture>
      </div>

      <div class="recruitment-about__content">
        <!-- Item 1: Title -->
        <h2 class="recruitment-about__title">ロクナナハチ撮影でできること</h2>

        <!-- SP用モック画像（PCでは非表示） -->
        <div class="recruitment-about__image-mobile">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about.jpg" alt="678撮影サイトのPC・スマホ表示例">
        </div>

        <!-- Item 2: テキストとモック横並び -->
        <div class="recruitment-about__main">
          <div class="recruitment-about__text">
            <div class="recruitment-about__description">
              <div class="recruitment-about__highlight">
                <span>シニア撮影専用の集客サイトで</span>
                <span>撮影したいお客様と</span>
                <span>写真館をつなげます。</span>
              </div>

              <p>あなたの写真館の魅力を伝えるための<br>
              専用ページをご用意しました。<br>
              オフィシャルサイトへのリンク設置も可能です。<br>
              ギャラリーに撮影した写真を掲載することで<br>
              お客様に撮影イメージを伝え、集客につなげます。</p>
            </div>
          </div>

          <div class="recruitment-about__image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about.jpg" alt="678撮影サイトのPC・スマホ表示例">
          </div>
        </div>
      </div>

      <div class="recruitment-about__frame-right">
        <!-- レスポンシブSVG -->
        <picture>
          <source media="(max-width: 767px)" srcset="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about-bottom-sp.svg">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/dl-about-right.svg" alt="">
        </picture>
      </div>
    </div>
  </div>

  <!-- Merits Section (ロクナナハチ撮影の3つのメリット) -->
  <div class="recruitment-merits-section">
    <!-- Title Block -->
    <div class="recruitment-merits__title-block">
      <h2 class="recruitment-merits__title">ロクナナハチ撮影の3つのメリット</h2>
    </div>

    <!-- Cards Grid Container -->
    <div class="recruitment-merits-container">
      <div class="recruitment-merits__cards">
        <div class="recruitment-merits__card">
          <!-- Card 01: シニア世代のお客様が増える -->
          <div class="recruitment-merits__card-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-01.svg" alt="">
          </div>
          <h3 class="recruitment-merits__card-title">
            シニア世代の<br>
            お客様が増える
          </h3>
          <div class="recruitment-merits__card-illustration">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-01-illust.svg" alt="">
          </div>
          <p class="recruitment-merits__card-text">
            これまで写真館を利用する機会が少なかったシニア層の新規集客が見込まれます。<br>
            従来のファミリー層や子ども向け撮影に加え、新たな顧客層としてシニア層を取り込むことで、ターゲット層の幅が広がり、来店数全体の底上げや売上拡大にもつながります。
          </p>
        </div>
        <div class="recruitment-merits__card">
          <!-- Card 02: 既存客へのアプローチができる -->
          <div class="recruitment-merits__card-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-02.svg" alt="">
          </div>
          <h3 class="recruitment-merits__card-title">
            既存客への<br>
            アプローチができる
          </h3>
          <div class="recruitment-merits__card-illustration">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-02-illust.svg" alt="">
          </div>
          <p class="recruitment-merits__card-text">
            すでに写真館をご利用いただいている既存のお客様へのアプローチも可能です。<br>
            たとえば、七五三などでお子さまの撮影に訪れた親御さまに対して、「ご両親への写真撮影をプレゼントしてみませんか？」といった、シニア世代に向けた新たなご提案が行えます。
          </p>
        </div>
        <div class="recruitment-merits__card">
          <!-- Card 03: ベテラン従業員の経験が活きる -->
          <div class="recruitment-merits__card-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-03.svg" alt="">
          </div>
          <h3 class="recruitment-merits__card-title">
            ベテラン従業員の<br>
            経験が活きる
          </h3>
          <div class="recruitment-merits__card-illustration">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/adv-illust-03.svg" alt="">
          </div>
          <p class="recruitment-merits__card-text">
            これまでに培ってきた熟練の撮影技術や高い接客力を活かし、シニア世代への対応を通じて、ベテラン従業員が新たに活躍できる場を創出します。<br>
            年齢を重ねても第一線で活躍できる、キャリア支援の仕組みとしても機能します。
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Registration Fee Section -->
  <div class="recruitment-fee-section">
    <div class="recruitment-fee__grid">
      <div class="recruitment-fee__item-1">
        <!-- Background SVG -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cost-bg.svg" alt="">
      </div>
      <div class="recruitment-fee__item-2">
        <!-- Fee Card -->
        <div class="recruitment-fee__card">
          <div class="recruitment-fee__card-header">
            <span class="recruitment-fee__card-title">登録費用</span>
          </div>
          <div class="recruitment-fee__card-content">
            <h3 class="recruitment-fee__card-subtitle">年間登録費</h3>
            <div class="recruitment-fee__price">
              <span class="recruitment-fee__price-number">30,000</span>
              <span class="recruitment-fee__price-unit">円</span>
              <span class="recruitment-fee__price-tax">（税込）</span><span class="recruitment-fee__price-slash">／</span><span class="recruitment-fee__price-year">年</span>
            </div>
          </div>
          <!-- Illustrations -->
          <div class="recruitment-fee__illustrations">
            <div class="recruitment-fee__illustration-left">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cost-left-lady.svg" alt="">
            </div>
            <div class="recruitment-fee__illustration-right">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cost-right-men.svg" alt="">
            </div>
          </div>
        </div>

        <!-- Fee Details Text -->
        <div class="recruitment-fee__details">
          <p class="recruitment-fee__detail-item">
            <span class="recruitment-fee__bullet">●</span>登録や運営にあたり、必要となる費用は「年間登録費」のみです。
          </p>
          <p class="recruitment-fee__detail-item">
            <span class="recruitment-fee__bullet">●</span>成果報酬や仲介手数料などを別途お支払いいただく必要はございません。
          </p>
          <p class="recruitment-fee__detail-item">
            <span class="recruitment-fee__bullet">●</span>同一グループ内で複数の店舗をご登録いただく場合、店舗ごとに年間登録費が必要となります。
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Download Content Section -->
  <div class="contact-content download-content">
    <!-- Download Section -->
    <div class="recruitment-download-section">
      <div class="contact-header-container">
        <div class="contact-header">
          <h2 class="contact-header__title">Download</h2>
        </div>
      </div>
      <div class="download-card">
        <h2 class="download-card__title">
          資料ダウンロード
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/heroicons-outline/arrow-down-on-square.svg" alt="" class="download-title-icon">
        </h2>
        <p class="download-card__description">
          掲載希望をお考えの写真館様、まずは資料をダウンロードして<br>
          詳細をご確認ください
        </p>
        <a href="<?php echo get_template_directory_uri(); ?>/pdf/250922_ロクナナハチ_27_s.pdf" class="download-card__button" id="downloadButton" download>
          資料をダウンロード
        </a>
      </div>
    </div>
  </div>

  <!-- Application Content Section -->
  <div class="contact-content application-content">
    <!-- Application Section -->
    <div class="schedule-container">
      <div class="form-step" id="formStep">
        <div class="contact-section-header">
          <h2 class="contact-section-header__title">お申し込み</h2>
        </div>
        <div class="form-container">
          <form id="recruitmentForm" method="post" action="">
            <?php wp_nonce_field('recruitment_form_nonce', 'recruitment_nonce_field'); ?>

            <div class="input-field">
              <label for="company_name">法人名</label>
              <input type="text" id="company_name" name="company_name" placeholder="例：サンプル株式会社">
              <div class="error-message" id="company_name-error" style="display: none;">
                法人名を入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="contact_name">お名前 (必須)</label>
              <input type="text" id="contact_name" name="contact_name" placeholder="例：田中 太郎" required>
              <div class="error-message" id="contact_name-error" style="display: none;">
                お名前を入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="contact_kana">フリガナ (必須)</label>
              <input type="text" id="contact_kana" name="contact_kana" placeholder="例：タナカ タロウ" required>
              <div class="error-message" id="contact_kana-error" style="display: none;">
                フリガナを入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="phone_number">お電話番号 (必須)</label>
              <input type="tel" id="phone_number" name="phone_number" placeholder="例：03-1234-5678" required>
              <div class="input-note">※確認のお電話をさせていただく場合があります</div>
              <div class="error-message" id="phone_number-error" style="display: none;">
                お電話番号を入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="website_url">店舗WEBサイト (必須)</label>
              <input type="url" id="website_url" name="website_url" placeholder="例：https://example.com/store" required>
              <div class="error-message" id="website_url-error" style="display: none;">
                WEBサイトURLを入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="email_address">メールアドレス (必須)</label>
              <input type="email" id="email_address" name="email_address" placeholder="例：hanako@example.com" required>
              <div class="error-message" id="email_address-error" style="display: none;">
                正しいメールアドレスを入力してください
              </div>
            </div>

            <div class="textarea-field">
              <label for="inquiry_details">詳しいお問い合わせ内容・ご質問（任意）</label>
              <textarea id="inquiry_details" name="inquiry_details" placeholder="ご不安なこと、知りたいこと、特別な配慮が必要なことなど、何でもお気軽にお書きください。"></textarea>
            </div>

            <div class="privacy-policy-section">
              <div class="privacy-policy-text">
                <h3>＜個人情報取り扱い＞</h3>
                <p>当社は、応募者の個人情報を、以下の目的で利用いたします。<br>
                  お問い合わせに関する内容確認、調査及びご返信時の参照情報としてお問合せにあたり、「個人情報の取り扱い」を必ずご確認ください。</p>
                <p>※上記の個人情報の取り扱いに関する要項をご確認のうえ、同意いただける場合は「同意する」にチェックを入れてください。</p>
              </div>

              <div class="confirmation-field-check">
                <div class="privacy-agreement">
                  <input type="checkbox" name="agreement" id="agreement" required>
                  <label for="agreement">
                    <a href="#" onclick="event.preventDefault(); window.open('<?php echo home_url('/privacy/'); ?>', '_blank', 'noopener,noreferrer'); return false;">個人情報の取り扱い</a>について同意する
                  </label>
                </div>
                <div class="error-message" id="agreement-error" style="display: none;">
                  個人情報の取り扱いについて同意してください
                </div>
              </div>
            </div>

            <div class="contact-buttons">
              <button type="submit" class="confirm-button">確認する</button>
            </div>
          </form>
        </div>
      </div>

      <!-- 確認画面 -->
      <div class="confirmation-step" id="confirmationStep" style="display: none;">
        <div class="form-container">
          <h2 class="confirmation-step__title">入力内容の確認</h2>
          <div class="confirmation-step__content">
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">法人名</span>
              <span class="confirmation-step__value" id="confirmCompanyName" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">お名前</span>
              <span class="confirmation-step__value" id="confirmContactName" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">フリガナ</span>
              <span class="confirmation-step__value" id="confirmContactKana" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">お電話番号</span>
              <span class="confirmation-step__value" id="confirmPhoneNumber" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">店舗WEBサイト</span>
              <span class="confirmation-step__value" id="confirmWebsiteUrl" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">メールアドレス</span>
              <span class="confirmation-step__value" id="confirmEmailAddress" data-escape="true"></span>
            </div>
            <div class="confirmation-step__item confirmation-step__item--textarea">
              <span class="confirmation-step__label">お問い合わせ内容</span>
              <span class="confirmation-step__value" id="confirmInquiryDetails" data-escape="true"></span>
            </div>
          </div>
          <div class="confirmation-step__buttons">
            <button type="button" class="back-button" id="backButton">戻る</button>
            <button type="button" class="submit-button" id="submitButton">送信する</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- JavaScript functionality is now handled by recruitment-form.js -->