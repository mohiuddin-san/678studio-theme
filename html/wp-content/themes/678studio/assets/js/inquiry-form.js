document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('inquiryForm');
    const formStep = document.getElementById('formStep');
    const confirmationStep = document.getElementById('confirmationStep');
    const backButton = document.getElementById('backButton');
    const submitButton = document.getElementById('submitButton');
    const confirmButton = document.querySelector('.confirm-button');

    // 必須フィールドの定義
    const requiredFields = [
        { id: 'name', errorId: 'name-error', message: 'お名前を入力してください' },
        { id: 'kana', errorId: 'kana-error', message: 'フリガナを入力してください' },
        { id: 'email', errorId: 'email-error', message: '正しいメールアドレスを入力してください' },
        { id: 'store-select', errorId: 'store-error', message: '店舗を選択してください' },
        { id: 'agreement', errorId: 'agreement-error', message: '個人情報の取り扱いについて同意してください' }
    ];

    // 確認ボタンクリック時の処理
    if (confirmButton) {
        confirmButton.addEventListener('click', (e) => {
            e.preventDefault();

            // バリデーション
            if (validateForm()) {
                showConfirmationStep();
            }
        });
    }

    // バリデーション関数
    function validateForm() {
        let allValid = true;

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                allValid = false;
            }
        });

        return allValid;
    }

    // 確認画面を表示
    function showConfirmationStep() {
        // フォームデータを収集
        const formData = collectFormData();

        // 確認画面にデータを表示
        populateConfirmationData(formData);

        // 画面切り替え
        formStep.style.display = 'none';
        confirmationStep.style.display = 'block';

        // スクロールを一番上に
        window.scrollTo(0, 0);
    }

    // フォームデータを収集
    function collectFormData() {
        const data = {
            name: document.getElementById('name').value.trim(),
            kana: document.getElementById('kana').value.trim(),
            contact: document.getElementById('contact').value.trim(),
            email: document.getElementById('email').value.trim(),
            notes: document.getElementById('notes').value.trim(),
            store: getSelectedStoreName()
        };

        return data;
    }

    // 選択された店舗名を取得
    function getSelectedStoreName() {
        const select = document.getElementById('store-select');

        if (select && select.value) {
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption && selectedOption.value !== '') {
                return selectedOption.textContent;
            }

            // フォールバック: window.shopsDataからも試す
            if (window.shopsData) {
                const selectedShop = window.shopsData.find(shop => shop.id == select.value);
                if (selectedShop) {
                    return selectedShop.name;
                }
            }
        }
        return '';
    }

    // 確認画面にデータを表示
    function populateConfirmationData(data) {
        document.getElementById('confirmName').textContent = data.name;
        document.getElementById('confirmKana').textContent = data.kana;
        document.getElementById('confirmContact').textContent = data.contact || '入力なし';
        document.getElementById('confirmEmail').textContent = data.email;
        document.getElementById('confirmStore').textContent = data.store || '選択なし';
        document.getElementById('confirmNotes').textContent = data.notes || '入力なし';
    }

    // 戻るボタンの処理
    if (backButton) {
        backButton.addEventListener('click', () => {
            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            window.scrollTo(0, 0);
        });
    }

    // 送信ボタンの処理
    if (submitButton) {
        submitButton.addEventListener('click', async () => {
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
                const confirmData = {
                    name: document.getElementById('confirmName').textContent,
                    kana: document.getElementById('confirmKana').textContent,
                    contact: document.getElementById('confirmContact').textContent,
                    email: document.getElementById('confirmEmail').textContent,
                    'shop-id': document.getElementById('store-select').value,
                    notes: document.getElementById('notes').value,
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

    // 個別フィールドのバリデーション
    function validateField(field) {
        const element = document.getElementById(field.id);
        const errorElement = document.getElementById(field.errorId);
        const fieldContainer = element.closest('.input-field, .contact-search, .confirmation-field-check');

        if (!element || !errorElement) return true;

        let isValid = true;
        let errorMessage = field.message;

        if (element.type === 'checkbox') {
            isValid = element.checked;
        } else if (element.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!element.value.trim()) {
                isValid = false;
                errorMessage = 'メールアドレスを入力してください';
            } else if (!emailRegex.test(element.value.trim())) {
                isValid = false;
                errorMessage = 'メールアドレスの形式が正しくありません';
            }
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
});