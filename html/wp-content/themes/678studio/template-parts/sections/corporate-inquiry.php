<?php
/**
 * Corporate Inquiry Section - 企業向けお問い合わせページ
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
        <h2 class="contact-header__title">Application</h2>
      </div>
    </div>
    <h1 class="contact__main-title">お問い合わせ</h1>

    <!-- Application Section -->
    <div class="schedule-container">
      <div class="form-step" id="formStep">
        <div class="form-container">
          <form id="inquiryForm" method="post" action="">

            <div class="input-field">
              <label for="company_name">法人名</label>
              <input type="text" id="company_name" name="company_name" placeholder="例：株式会社サンクリエーション">
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
              <label for="email_address">メールアドレス (任意)</label>
              <input type="email" id="email_address" name="email_address" placeholder="例：hanako@example.com">
              <div class="error-message" id="email_address-error" style="display: none;">
                正しいメールアドレスを入力してください
              </div>
            </div>

            <div class="textarea-field">
              <label for="inquiry_details">お問い合わせ内容</label>
              <textarea id="inquiry_details" name="inquiry_details" placeholder="お問い合わせ内容の詳細をご記入ください"></textarea>
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
              <span class="confirmation-step__value" id="confirmCompanyName"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">お名前</span>
              <span class="confirmation-step__value" id="confirmContactName"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">フリガナ</span>
              <span class="confirmation-step__value" id="confirmContactKana"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">お電話番号</span>
              <span class="confirmation-step__value" id="confirmPhoneNumber"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">店舗WEBサイト</span>
              <span class="confirmation-step__value" id="confirmWebsiteUrl"></span>
            </div>
            <div class="confirmation-step__item">
              <span class="confirmation-step__label">メールアドレス</span>
              <span class="confirmation-step__value" id="confirmEmailAddress"></span>
            </div>
            <div class="confirmation-step__item confirmation-step__item--textarea">
              <span class="confirmation-step__label">お問い合わせ内容</span>
              <span class="confirmation-step__value" id="confirmInquiryDetails"></span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Form validation and confirmation
  const form = document.getElementById('inquiryForm');
  const formStep = document.getElementById('formStep');
  const confirmationStep = document.getElementById('confirmationStep');
  const backButton = document.getElementById('backButton');
  const submitButton = document.getElementById('submitButton');

  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      let isValid = true;
      const requiredFields = ['contact_name', 'contact_kana', 'phone_number', 'website_url'];

      // Validate required fields
      requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(fieldName + '-error');

        if (!field.value.trim()) {
          errorElement.style.display = 'block';
          isValid = false;
        } else {
          errorElement.style.display = 'none';
        }
      });

      // Validate email if provided
      const emailField = document.getElementById('email_address');
      const emailError = document.getElementById('email_address-error');
      if (emailField.value.trim() && !isValidEmail(emailField.value)) {
        emailError.style.display = 'block';
        isValid = false;
      } else {
        emailError.style.display = 'none';
      }

      // Validate agreement checkbox
      const agreementCheckbox = document.getElementById('agreement');
      const agreementError = document.getElementById('agreement-error');
      if (!agreementCheckbox.checked) {
        agreementError.style.display = 'block';
        isValid = false;
      } else {
        agreementError.style.display = 'none';
      }

      if (isValid) {
        // Show confirmation page
        showConfirmation();
      }
    });
  }

  // Show confirmation page
  function showConfirmation() {
    // Hide form step and show confirmation step
    formStep.style.display = 'none';
    confirmationStep.style.display = 'block';

    // Populate confirmation values
    document.getElementById('confirmCompanyName').textContent = document.getElementById('company_name').value || '-';
    document.getElementById('confirmContactName').textContent = document.getElementById('contact_name').value;
    document.getElementById('confirmContactKana').textContent = document.getElementById('contact_kana').value;
    document.getElementById('confirmPhoneNumber').textContent = document.getElementById('phone_number').value;
    document.getElementById('confirmWebsiteUrl').textContent = document.getElementById('website_url').value;
    document.getElementById('confirmEmailAddress').textContent = document.getElementById('email_address').value || '-';
    document.getElementById('confirmInquiryDetails').textContent = document.getElementById('inquiry_details').value || '-';

    // Scroll to top
    window.scrollTo(0, 0);
  }

  // Back button functionality
  if (backButton) {
    backButton.addEventListener('click', function() {
      confirmationStep.style.display = 'none';
      formStep.style.display = 'block';
      window.scrollTo(0, 0);
    });
  }

  // Submit button functionality
  if (submitButton) {
    submitButton.addEventListener('click', function() {
      // Here you would typically submit the form data
      alert('フォームの送信処理を実装してください');
    });
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
});
</script>