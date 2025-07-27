<?php
/**
 * Contact Section Component
 */
?>


<section class="contact-section">
  <!-- Breadcrumb Section -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => 'お問い合わせ', 'url' => '']
    ]
  ]); ?>
  <!-- Content Section -->
  <div class="contact-content">
    <div class="contact-header-container">
      <div class="contact-header">
        <h2 class="contact-header__title">Inquiry</h2>
      </div>

    </div>
    <h1 class="contact__main-title">お問い合わせ</h1>

    <div class="contact-search">
      <select class="contact-select">
        <option>ご予約・お問い合わせの店舗をお選びください</option>
      </select>
    </div>

    <div class="contact-details" style="display: none;">
      <div class="contact-image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="Studio Image">
      </div>
      <div class="contact-info">
        <table>
          <tr>
            <td>店舗名</td>
            <td>選択された店舗名</td>
          </tr>
          <tr>
            <td>住所</td>
            <td>店舗住所</td>
          </tr>
          <tr>
            <td>電話番号</td>
            <td>店舗電話番号</td>
          </tr>
          <tr>
            <td>営業時間</td>
            <td>店舗営業時間</td>
          </tr>
          <tr>
            <td>定休日</td>
            <td>店舗定休日</td>
          </tr>
        </table>
      </div>
    </div>

    <div class="schedule-container">
      <!-- フォーム入力画面 -->
      <div class="form-step" id="formStep">
        <div class="form-container">
          <form id="inquiryForm">
            <div class="input-field">
              <label for="name">お名前 (必須)</label>
              <input type="text" id="name" name="name" placeholder="例: 山田 花子" required>
            </div>

            <div class="input-field">
              <label for="kana">フリガナ (必須)</label>
              <input type="text" id="kana" name="kana" placeholder="例: ヤマダ ハナコ" required>
            </div>

            <div class="input-field">
              <label for="contact">お電話番号</label>
              <input type="text" id="contact" name="contact" placeholder="例: 03-1234-5678">
            </div>

            <div class="input-field">
              <label for="email">メールアドレス (必須)</label>
              <input type="email" id="email" name="email" placeholder="例: hanako@example.com" required>
            </div>

            <div class="textarea-field">
              <label for="notes">詳しいご相談内容・ご質問（任意）</label>
              <textarea id="notes" name="notes" placeholder="ご不安なこと、知りたいこと、特別な配慮が必要なことなど、 何でもお気軽にお書きください。  
記入例： ・化粧品アレルギーがあります ・車椅子での利用を考えています ・家族へのサプライズ撮影を計画中です 
・以前他の写真館で満足できませんでした。"></textarea>
            </div>

            <div class="confirmation-field-check">
              <label>
                <input type="checkbox" name="agreement" required> 個人情報の取り扱いについて同意する
              </label>
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
              <span class="confirmation-step__label">お名前</span>
              <span class="confirmation-step__value" id="confirmName"></span>
            </div>
            
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">フリガナ</span>
              <span class="confirmation-step__value" id="confirmKana"></span>
            </div>
            
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">お電話番号</span>
              <span class="confirmation-step__value" id="confirmContact"></span>
            </div>
            
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">メールアドレス</span>
              <span class="confirmation-step__value" id="confirmEmail"></span>
            </div>
            
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">選択店舗</span>
              <span class="confirmation-step__value" id="confirmStore"></span>
            </div>
            
            <div class="confirmation-step__item confirmation-step__item--textarea">
              <span class="confirmation-step__label">ご相談内容</span>
              <span class="confirmation-step__value" id="confirmNotes"></span>
            </div>
          </div>

          <div class="confirmation-step__buttons">
            <button type="button" class="back-button" id="backButton">戻る</button>
            <button type="button" class="submit-button" id="submitButton">送信する</button>
          </div>
        </div>
      </div>
    </div>
</section>

<?php get_template_part('template-parts/components/footer'); ?>