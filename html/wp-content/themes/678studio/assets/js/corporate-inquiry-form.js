// 法人お問い合わせフォーム初期化の確実な実行
(function() {
    'use strict';

    // デバッグ設定
    const DEBUG_MODE = false; // 本番環境では false に設定
    const debug = DEBUG_MODE ? console.log.bind(console) : () => {};
    const debugError = DEBUG_MODE ? console.error.bind(console) : () => {};

    // DOM読み込み完了の確実な待機
    function ensureDOM(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    ensureDOM(function() {

    // DOM要素の取得
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
        { id: 'email_address', errorId: 'email_address-error', message: '正しいメールアドレスを入力してください' },
        { id: 'agreement', errorId: 'agreement-error', message: '個人情報の取り扱いについて同意してください' }
    ];

    // ユーティリティ関数群
    function hideAllErrors() {
        requiredFields.forEach(field => {
            const errorElement = document.getElementById(field.errorId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        });
    }

    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function validateSingleField(field) {
        const element = document.getElementById(field.id);
        if (!element) {
            console.warn(`⚠️ 要素が見つかりません: ${field.id}`);
            return false;
        }

        let isValid = true;
        let value;

        // フィールド別バリデーション
        if (element.type === 'checkbox') {
            value = element.checked ? 'checked' : 'unchecked';
            isValid = element.checked;
        } else if (element.type === 'email') {
            value = element.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = value !== '' && emailRegex.test(value);
        } else if (element.type === 'url') {
            value = element.value.trim();
            if (value !== '') {
                const urlRegex = /^https?:\/\/.+/;
                isValid = urlRegex.test(value);
            }
        } else {
            value = element.value.trim();
            isValid = value !== '';
        }

        debug(`🔍 ${field.id}: ${isValid ? '✅' : '❌'} "${value}"`);

        // エラー表示は呼び出し元で制御するため、ここでは表示しない
        return isValid;
    }

    function validateAllFields() {
        debug('🔄 全フィールド検証開始');
        hideAllErrors();

        let allValid = true;
        requiredFields.forEach(field => {
            const isValid = validateSingleField(field);
            if (!isValid) {
                allValid = false;
                showError(field.errorId, field.message);
            }
        });

        debug(`📋 検証結果: ${allValid ? '✅ 全て有効' : '❌ エラーあり'}`);
        return allValid;
    }

    // フォームデータ収集
    function collectFormData() {
        const data = {
            company_name: document.getElementById('company_name').value.trim(),
            contact_name: document.getElementById('contact_name').value.trim(),
            contact_kana: document.getElementById('contact_kana').value.trim(),
            phone_number: document.getElementById('phone_number').value.trim(),
            website_url: document.getElementById('website_url').value.trim(),
            email_address: document.getElementById('email_address').value.trim(),
            inquiry_details: document.getElementById('inquiry_details').value.trim()
        };

        debug('📦 収集されたデータ:', data);
        return data;
    }

    function populateConfirmationData(data) {
        debug('📝 確認画面にデータを表示');

        const mappings = [
            { confirmId: 'confirmCompanyName', value: data.company_name || '入力なし' },
            { confirmId: 'confirmContactName', value: data.contact_name },
            { confirmId: 'confirmContactKana', value: data.contact_kana },
            { confirmId: 'confirmPhoneNumber', value: data.phone_number },
            { confirmId: 'confirmWebsiteUrl', value: data.website_url },
            { confirmId: 'confirmEmailAddress', value: data.email_address || '入力なし' },
            { confirmId: 'confirmInquiryDetails', value: data.inquiry_details || '入力なし' }
        ];

        mappings.forEach(mapping => {
            const element = document.getElementById(mapping.confirmId);
            if (element) {
                element.textContent = mapping.value;
            }
        });
    }

    function disableFormFields() {
        // 全てのフォーム入力要素を無効化
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.disabled = true;
            input.setAttribute('readonly', true);
        });
    }

    function enableFormFields() {
        // 全てのフォーム入力要素を有効化
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.disabled = false;
            input.removeAttribute('readonly');
        });
    }

    function showConfirmationStep() {
        debug('📋 確認画面を表示');
        const formData = collectFormData();
        populateConfirmationData(formData);

        // フォームフィールドを無効化して編集を防ぐ
        disableFormFields();

        formStep.style.display = 'none';
        confirmationStep.style.display = 'block';
        window.scrollTo(0, 0);
    }

    // リアルタイムバリデーション用のイベントリスナー設定
    function setupRealtimeValidation() {
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                // input, change, blur イベントでリアルタイム検証
                ['input', 'change', 'blur'].forEach(eventType => {
                    element.addEventListener(eventType, () => {
                        // 単一フィールドのバリデーション
                        const isValid = validateSingleField(field);

                        // 有効な場合はエラーを非表示
                        if (isValid) {
                            const errorElement = document.getElementById(field.errorId);
                            if (errorElement) {
                                errorElement.style.display = 'none';
                                debug(`✅ ${field.id} エラーメッセージを非表示`);
                            }
                        }
                    });
                });
            }
        });
    }

    // 初期化処理
    function initialize() {
        // 全エラーメッセージを非表示
        hideAllErrors();

        // 確認ボタンを強制的に有効化
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.removeAttribute('disabled');
            confirmButton.style.pointerEvents = 'auto';
            debug('確認ボタン有効化');
        } else {
            debugError('❌ 確認ボタンが見つかりません');
        }

        // リアルタイムバリデーションの設定
        setupRealtimeValidation();
    }

    // イベントリスナー設定
    if (confirmButton) {
        confirmButton.addEventListener('click', (e) => {
            e.preventDefault();

            if (validateAllFields()) {
                showConfirmationStep();
            }
        });
    }

    if (backButton) {
        backButton.addEventListener('click', () => {
            // フォームフィールドを再度有効化
            enableFormFields();

            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            window.scrollTo(0, 0);
        });
    }

    if (submitButton) {
        submitButton.addEventListener('click', async () => {
            const submitBtn = submitButton;
            const originalText = submitBtn.textContent;

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = '送信中...';

                const formData = new FormData();
                formData.append('action', 'siaes_submit_form');
                formData.append('nonce', window.siaes_ajax?.nonce || '');
                formData.append('page_id', window.siaes_ajax?.page_id || '');

                // 確認画面のデータを使用
                const confirmData = {
                    company_name: document.getElementById('company_name').value,
                    contact_name: document.getElementById('contact_name').value,
                    contact_kana: document.getElementById('contact_kana').value,
                    phone_number: document.getElementById('phone_number').value,
                    website_url: document.getElementById('website_url').value,
                    email_address: document.getElementById('email_address').value,
                    inquiry_details: document.getElementById('inquiry_details').value,
                    agreement: document.getElementById('agreement').checked ? '1' : '0'
                };

                Object.keys(confirmData).forEach(key => {
                    if (confirmData[key] !== null && confirmData[key] !== undefined) {
                        formData.append(key, confirmData[key]);
                    }
                });

                const response = await fetch(window.siaes_ajax?.ajax_url || 'http://localhost:8080/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('お問い合わせを承りました。2営業日以内にご連絡させていただきます。');

                    // フォームフィールドを有効化してからリセット
                    enableFormFields();
                    form.reset();

                    confirmationStep.style.display = 'none';
                    formStep.style.display = 'block';
                    window.scrollTo(0, 0);
                } else {
                    alert('送信に失敗しました。しばらく時間をおいて再度お試しください。');
                }
            } catch (error) {
                debugError('送信エラー:', error);
                alert('送信に失敗しました。しばらく時間をおいて再度お試しください。');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // 初期化実行
    initialize();

    // デバッグ用：初期化確認
    if (DEBUG_MODE) {
        setTimeout(() => {
            debug('診断: フォーム要素', requiredFields.map(f => f.id + ':' + !!document.getElementById(f.id)).join(', '));
            debug('診断: 確認ボタン', !!confirmButton && !confirmButton.disabled);
        }, 2000);
    }

    // デバッグ用：グローバル関数を追加（確実にwindowに設定）
    if (typeof window !== 'undefined') {
        window.debugCorporateInquiryForm = {
            testValidation: () => {
                debug('🧪 手動バリデーションテスト開始');
                const result = validateAllFields();
                debug('🧪 バリデーション結果:', result);
                return result;
            },
            hideErrors: () => {
                debug('🧪 手動エラー非表示');
                hideAllErrors();
            },
            checkElements: () => {
                debug('🧪 要素チェック');
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    const errorElement = document.getElementById(field.errorId);
                    debug(`${field.id}: 要素=${!!element}, エラー=${!!errorElement}`);
                });
            },
            isLoaded: true,
            version: '1.0.0'
        };
    }

    debug('法人お問い合わせフォーム初期化完了');
    }); // ensureDOM callback end
})(); // IIFE end