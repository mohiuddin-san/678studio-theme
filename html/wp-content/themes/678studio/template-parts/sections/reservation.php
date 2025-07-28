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
      ['text' => 'ご予約', 'url' => '']
    ]
  ]); ?>

  <!-- Content Section -->
  <div class="contact-content">
    <div class="contact-header-container">
      <div class="contact-header">
        <h2 class="contact-header__title">Reservation</h2>
      </div>
    </div>
    <h1 class="contact__main-title">ご予約</h1>

    <div class="contact-search">
      <label for="store-select" class="contact-search__label">選択店舗 (必須)</label>
      <select class="contact-select" id="shop-dropdown" name="shop-id" required>
        <option value="">ご予約・お問い合わせの店舗をお選びください</option>
      </select>
      <div class="error-message" id="store-error" style="display: none;">
        店舗を選択してください
      </div>
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
          <h1>オンライン予約フォーム</h1>
           <form id="inquiry-form" method="post" action="">
            <div class="input-field">
              <label for="name">お名前 (必須)</label>
              <input type="text" id="name" name="name" placeholder="例: 山田 花子" required>
              <div class="error-message" id="name-error" style="display: none;">
                お名前を入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="kana">フリガナ (必須)</label>
              <input type="text" id="kana" name="kana" placeholder="例: ヤマダ ハナコ" required>
              <div class="error-message" id="kana-error" style="display: none;">
                フリガナを入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="contact">お電話番号</label>
              <input type="text" id="contact" name="contact" placeholder="例: 03-1234-5678">
            </div>

            <div class="input-field">
              <label for="email">メールアドレス (必須)</label>
              <input type="email" id="email" name="email" placeholder="例: hanako@example.com" required>
              <div class="error-message" id="email-error" style="display: none;">
                正しいメールアドレスを入力してください
              </div>
            </div>

            <div class="input-field">
              <label for="reservation_date">撮影希望日（必須）</label>
              <input type="date" id="reservation_date" name="reservation_date" required>
              <div class="error-message" id="reservation_date-error" style="display: none;">
                撮影希望日を選択してください
              </div>
            </div>

            <div class="input-field">
              <label for="reservation_time_from">開始時間（必須）</label>
              <input type="time" id="reservation_time_from" name="reservation_time_from" min="09:00" max="18:00" required>
              <div class="error-message" id="reservation_time_from-error" style="display: none;">
                開始時間を選択してください
              </div>
            </div>

            <div class="input-field">
              <label for="reservation_time_to">終了時間（必須）</label>
              <input type="time" id="reservation_time_to" name="reservation_time_to" min="09:00" max="20:00" required>
              <div class="error-message" id="reservation_time_to-error" style="display: none;">
                終了時間を選択してください
              </div>
            </div>

            <div class="textarea-field">
              <label for="notes">詳しいご相談内容・ご質問（任意）</label>
              <textarea id="notes" name="notes" placeholder="ご不安なこと、知りたいこと、特別な配慮が必要なことなど、 何でもお気軽にお書きください。  
記入例： ・化粧品アレルギーがあります ・車椅子での利用を考えています ・家族へのサプライズ撮影を計画中です 
・以前他の写真館で満足できませんでした。"></textarea>
            </div>

            <div class="privacy-policy-section">
              <div class="privacy-policy-text">
                <h3>＜個人情報取り扱い＞</h3>
                <p>当社は、応募者の個人情報を、以下の目的で利用いたします。<br>
                お問い合わせに関する内容確認、調査及びご返信時の参照情報としてお問合せにあたり、「個人情報の取り扱い」を必ずご確認ください。</p>
                <p>※上記の個人情報の取り扱いに関する要項をご確認のうえ、同意いただける場合は「同意する」にチェックを入れてください。</p>
              </div>
              
              <div class="confirmation-field-check">
                <label>
                  <input type="checkbox" name="agreement" id="agreement" required> 個人情報の取り扱いについて同意する
                </label>
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

            <div class="confirmation-step__item">
              <span class="confirmation-step__label">撮影希望日</span>
              <span class="confirmation-step__value" id="confirmDate"></span>
            </div>

            <div class="confirmation-step__item">
              <span class="confirmation-step__label">開始時間</span>
              <span class="confirmation-step__value" id="confirmTimeFrom"></span>
            </div>

            <div class="confirmation-step__item">
              <span class="confirmation-step__label">終了時間</span>
              <span class="confirmation-step__value" id="confirmTimeTo"></span>
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
  </div>
</section>

<?php get_template_part('template-parts/components/footer'); ?>