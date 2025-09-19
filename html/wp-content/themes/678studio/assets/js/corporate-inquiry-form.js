document.addEventListener('DOMContentLoaded', function() {
    // Form validation and confirmation
    const form = document.getElementById('inquiryForm');
    const formStep = document.getElementById('formStep');
    const confirmationStep = document.getElementById('confirmationStep');
    const backButton = document.getElementById('backButton');
    const submitButton = document.getElementById('submitButton');
    const confirmButton = document.querySelector('.confirm-button');

    // 必須フィールドの定義
    const requiredFields = [
        { id: 'contact_name', errorId: 'contact_name-error', message: 'お名前を入力してください' },
        { id: 'contact_kana', errorId: 'contact_kana-error', message: 'フリガナを入力してください' },
        { id: 'phone_number', errorId: 'phone_number-error', message: 'お電話番号を入力してください' },
        { id: 'website_url', errorId: 'website_url-error', message: 'WEBサイトURLを入力してください' },
        { id: 'agreement', errorId: 'agreement-error', message: '個人情報の取り扱いについて同意してください' }
    ];

    // 初期状態では確認ボタンを有効化（バリデーションは送信時のみ）
    if (confirmButton) {
        confirmButton.disabled = false;
    }

    // リアルタイムバリデーションの設定
    setupRealTimeValidation();

    // 確認ボタンクリック時の処理（フォーム送信イベントを使用しない）
    if (confirmButton) {
        confirmButton.addEventListener('click', (e) => {
            e.preventDefault();

            // バリデーション
            if (validateForm()) {
                showConfirmation();
            }
        });
    }

    // バリデーション関数（フォーム送信時用）
    function validateForm() {
        return validateAndUpdateButton();
    }

    // Show confirmation page
    function showConfirmation() {
        // Hide form step and show confirmation step
        formStep.style.display = 'none';
        confirmationStep.style.display = 'block';

        // Populate confirmation values
        document.getElementById('confirmCompanyName').textContent = document.getElementById('company_name').value || '入力なし';
        document.getElementById('confirmContactName').textContent = document.getElementById('contact_name').value;
        document.getElementById('confirmContactKana').textContent = document.getElementById('contact_kana').value;
        document.getElementById('confirmPhoneNumber').textContent = document.getElementById('phone_number').value;
        document.getElementById('confirmWebsiteUrl').textContent = document.getElementById('website_url').value;
        document.getElementById('confirmEmailAddress').textContent = document.getElementById('email_address').value || '入力なし';
        document.getElementById('confirmInquiryDetails').textContent = document.getElementById('inquiry_details').value || '入力なし';

        // Scroll to top
        window.scrollTo(0, 0);
    }

    // リアルタイムバリデーション設定
    function setupRealTimeValidation() {
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                if (element.type === 'checkbox') {
                    element.addEventListener('change', validateAndUpdateButton);
                } else {
                    element.addEventListener('blur', () => validateField(field));
                    element.addEventListener('input', validateAndUpdateButton);
                }
            }
        });

        // メールアドレスの特別なバリデーション
        const emailField = document.getElementById('email_address');
        if (emailField) {
            emailField.addEventListener('blur', () => validateEmailField());
            emailField.addEventListener('input', validateAndUpdateButton);
        }
    }

    // バリデーションとボタン状態更新
    function validateAndUpdateButton() {
        let allValid = true;

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                allValid = false;
            }
        });

        // メールアドレスの任意フィールドバリデーション
        if (!validateEmailField()) {
            allValid = false;
        }

        return allValid;
    }

    // 個別フィールドのバリデーション
    function validateField(field) {
        const element = document.getElementById(field.id);
        const errorElement = document.getElementById(field.errorId);
        const fieldContainer = element.closest('.input-field, .confirmation-field-check');

        if (!element || !errorElement) return true;

        let isValid = true;
        let errorMessage = field.message;

        if (element.type === 'checkbox') {
            isValid = element.checked;
        } else {
            isValid = element.value.trim() !== '';
        }

        // エラー表示の制御
        if (isValid) {
            errorElement.style.display = 'none';
            if (fieldContainer) {
                fieldContainer.classList.remove('error');
            }
        } else {
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
            if (fieldContainer) {
                fieldContainer.classList.add('error');
            }
        }

        return isValid;
    }

    // メールアドレスフィールドのバリデーション
    function validateEmailField() {
        const emailField = document.getElementById('email_address');
        const emailError = document.getElementById('email_address-error');
        const fieldContainer = emailField.closest('.input-field');

        if (!emailField || !emailError) return true;

        let isValid = true;
        let errorMessage = '';

        if (emailField.value.trim() && !isValidEmail(emailField.value)) {
            isValid = false;
            errorMessage = 'メールアドレスの形式が正しくありません';
        }

        // エラー表示の制御
        if (isValid) {
            emailError.style.display = 'none';
            if (fieldContainer) {
                fieldContainer.classList.remove('error');
            }
        } else {
            emailError.textContent = errorMessage;
            emailError.style.display = 'block';
            if (fieldContainer) {
                fieldContainer.classList.add('error');
            }
        }

        return isValid;
    }

    // Back button functionality
    if (backButton) {
        backButton.addEventListener('click', function() {
            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            window.scrollTo(0, 0);
        });
    }

    // Submit button functionality with AJAX
    if (submitButton) {
        submitButton.addEventListener('click', async function() {
            const submitBtn = submitButton;
            const originalText = submitBtn.textContent;

            try {
                // ボタンを無効化
                submitBtn.disabled = true;
                submitBtn.textContent = '送信中...';

                // フォームデータを収集
                const formData = new FormData();
                formData.append('action', 'siaes_submit_form');
                formData.append('nonce', window.siaes_ajax?.nonce || '');
                formData.append('page_id', window.siaes_ajax?.page_id || '');

                // 確認画面のデータを使用
                const emailValue = document.getElementById('email_address').value;
                const confirmData = {
                    company_name: document.getElementById('company_name').value,
                    contact_name: document.getElementById('contact_name').value,
                    contact_kana: document.getElementById('contact_kana').value,
                    phone_number: document.getElementById('phone_number').value,
                    website_url: document.getElementById('website_url').value,
                    email_address: emailValue,
                    email: emailValue, // プラグインの自動返信メール用
                    inquiry_details: document.getElementById('inquiry_details').value,
                    agreement: document.getElementById('agreement').checked ? '1' : '0'
                };

                // FormDataに追加
                Object.keys(confirmData).forEach(key => {
                    if (confirmData[key] !== null && confirmData[key] !== undefined) {
                        formData.append(key, confirmData[key]);
                    }
                });

                // AJAX送信
                const response = await fetch(window.siaes_ajax?.ajax_url || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('お問い合わせを承りました。2営業日以内にご連絡させていただきます。');
                    // フォームリセット
                    form.reset();
                    confirmationStep.style.display = 'none';
                    formStep.style.display = 'block';
                    window.scrollTo(0, 0);
                } else {
                    alert('送信に失敗しました。しばらく時間をおいて再度お試しください。');
                }
            } catch (error) {
                console.error('送信エラー:', error);
                alert('送信に失敗しました。しばらく時間をおいて再度お試しください。');
            } finally {
                // ボタンを元に戻す
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});