// 予約フォーム初期化の確実な実行
(function() {
    'use strict';

    console.log('🎯 予約フォームスクリプト読み込み開始');

    // DOM読み込み完了の確実な待機
    function ensureDOM(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    ensureDOM(function() {
        console.log('🎯 予約フォーム初期化開始');

    // DOM要素の取得
    const form = document.getElementById('reservationForm');
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
        { id: 'reservation_date_1', errorId: 'reservation_date_1-error', message: '第1撮影希望日を選択してください' },
        { id: 'reservation_time_1', errorId: 'reservation_time_1-error', message: '第1撮影希望時間を選択してください' },
        { id: 'agreement', errorId: 'agreement-error', message: '個人情報の取り扱いについて同意してください' }
    ];

    // ユーティリティ関数群
    function hideAllErrors() {
        console.log('🔧 全エラーメッセージを非表示');
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
        } else if (element.tagName === 'SELECT') {
            value = element.value;
            isValid = value !== '' && value !== null && value !== '0';
        } else {
            value = element.value.trim();
            isValid = value !== '';
        }

        console.log(`🔍 検証中: ${field.id} = "${value}" (type: ${element.type || element.tagName})`);
        console.log(`📊 ${field.id}: ${isValid ? '✅ 有効' : '❌ 無効'}`);

        // エラー表示は呼び出し元で制御するため、ここでは表示しない
        return isValid;
    }

    function validateAllFields() {
        console.log('🔄 全フィールド検証開始');
        hideAllErrors();

        let allValid = true;
        requiredFields.forEach(field => {
            const isValid = validateSingleField(field);
            if (!isValid) {
                allValid = false;
                showError(field.errorId, field.message);
            }
        });

        console.log(`📋 検証結果: ${allValid ? '✅ 全て有効' : '❌ エラーあり'}`);
        return allValid;
    }

    // フォームデータ収集
    function collectFormData() {
        const data = {
            name: document.getElementById('name').value.trim(),
            kana: document.getElementById('kana').value.trim(),
            contact: document.getElementById('contact').value.trim(),
            email: document.getElementById('email').value.trim(),
            reservation_date_1: document.getElementById('reservation_date_1').value,
            reservation_time_1: document.getElementById('reservation_time_1').value,
            reservation_date_2: document.getElementById('reservation_date_2').value,
            reservation_time_2: document.getElementById('reservation_time_2').value,
            reservation_date_3: document.getElementById('reservation_date_3').value,
            reservation_time_3: document.getElementById('reservation_time_3').value,
            notes: document.getElementById('notes').value.trim(),
            store: getSelectedStoreName()
        };

        console.log('📦 収集されたデータ:', data);
        return data;
    }

    function getSelectedStoreName() {
        const select = document.getElementById('store-select');
        if (select && select.value) {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value !== '') {
                return selectedOption.textContent;
            }
        }
        return '';
    }

    function formatDateTime(date, time) {
        if (!date || !time) return '';
        const dateObj = new Date(date);
        const year = dateObj.getFullYear();
        const month = dateObj.getMonth() + 1;
        const day = dateObj.getDate();
        return `${year}年${month}月${day}日 ${time}`;
    }

    function populateConfirmationData(data) {
        console.log('📝 確認画面にデータを表示');

        const mappings = [
            { confirmId: 'confirmName', value: data.name },
            { confirmId: 'confirmKana', value: data.kana },
            { confirmId: 'confirmContact', value: data.contact || '入力なし' },
            { confirmId: 'confirmEmail', value: data.email },
            { confirmId: 'confirmStore', value: data.store || '選択なし' },
            { confirmId: 'confirmDateTime1', value: formatDateTime(data.reservation_date_1, data.reservation_time_1) },
            { confirmId: 'confirmDateTime2', value: formatDateTime(data.reservation_date_2, data.reservation_time_2) || '未設定' },
            { confirmId: 'confirmDateTime3', value: formatDateTime(data.reservation_date_3, data.reservation_time_3) || '未設定' },
            { confirmId: 'confirmNotes', value: data.notes || '入力なし' }
        ];

        mappings.forEach(mapping => {
            const element = document.getElementById(mapping.confirmId);
            if (element) {
                element.textContent = mapping.value;
            }
        });
    }

    function disableFormFields() {
        console.log('🔒 フォームフィールドを無効化');

        // 全てのフォーム入力要素を無効化
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.disabled = true;
            input.setAttribute('readonly', true);
        });
    }

    function enableFormFields() {
        console.log('🔓 フォームフィールドを有効化');

        // 全てのフォーム入力要素を有効化
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.disabled = false;
            input.removeAttribute('readonly');
        });
    }

    function showConfirmationStep() {
        console.log('📋 確認画面を表示');
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
        console.log('🔄 リアルタイムバリデーション設定開始');

        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                // input, change, blur イベントでリアルタイム検証
                ['input', 'change', 'blur'].forEach(eventType => {
                    element.addEventListener(eventType, () => {
                        console.log(`📝 ${field.id} フィールドが変更されました (${eventType})`);

                        // 単一フィールドのバリデーション
                        const isValid = validateSingleField(field);

                        // 有効な場合はエラーを非表示
                        if (isValid) {
                            const errorElement = document.getElementById(field.errorId);
                            if (errorElement) {
                                errorElement.style.display = 'none';
                                console.log(`✅ ${field.id} エラーメッセージを非表示`);
                            }
                        }
                    });
                });
                console.log(`🎧 ${field.id} のリアルタイムバリデーション設定完了`);
            }
        });
    }

    // 初期化処理
    function initialize() {
        console.log('🚀 フォーム初期化');

        // 全エラーメッセージを非表示
        hideAllErrors();

        // 確認ボタンを強制的に有効化
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.removeAttribute('disabled');
            confirmButton.style.pointerEvents = 'auto';
            console.log('✅ 確認ボタン強制有効化');
        } else {
            console.error('❌ 確認ボタンが見つかりません');
        }

        // リアルタイムバリデーションの設定
        setupRealtimeValidation();
    }

    // イベントリスナー設定
    if (confirmButton) {
        confirmButton.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 確認ボタンがクリックされました');

            if (validateAllFields()) {
                showConfirmationStep();
            }
        });
        console.log('🎧 確認ボタンのイベントリスナー設定完了');
    }

    if (backButton) {
        backButton.addEventListener('click', () => {
            console.log('⬅️ 戻るボタンがクリックされました');

            // フォームフィールドを再度有効化
            enableFormFields();

            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            window.scrollTo(0, 0);
        });
        console.log('🎧 戻るボタンのイベントリスナー設定完了');
    }

    if (submitButton) {
        submitButton.addEventListener('click', async () => {
            console.log('📤 送信ボタンがクリックされました');
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
                    name: document.getElementById('confirmName').textContent,
                    kana: document.getElementById('confirmKana').textContent,
                    contact: document.getElementById('confirmContact').textContent,
                    email: document.getElementById('confirmEmail').textContent,
                    'shop-id': document.getElementById('store-select').value,
                    reservation_date_1: document.getElementById('reservation_date_1').value,
                    reservation_time_1: document.getElementById('reservation_time_1').value,
                    reservation_date_2: document.getElementById('reservation_date_2').value,
                    reservation_time_2: document.getElementById('reservation_time_2').value,
                    reservation_date_3: document.getElementById('reservation_date_3').value,
                    reservation_time_3: document.getElementById('reservation_time_3').value,
                    notes: document.getElementById('notes').value,
                    agreement: document.getElementById('agreement').checked ? '1' : '0'
                };

                Object.keys(confirmData).forEach(key => {
                    if (confirmData[key] !== null && confirmData[key] !== undefined) {
                        formData.append(key, confirmData[key]);
                    }
                });

                const response = await fetch(window.siaes_ajax?.ajax_url || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('ご予約相談を承りました。2営業日以内にご連絡させていただきます。');

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
                console.error('送信エラー:', error);
                alert('送信に失敗しました。しばらく時間をおいて再度お試しください。');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
        console.log('🎧 送信ボタンのイベントリスナー設定完了');
    }

    // 初期化実行
    initialize();

    // デバッグ用：5秒後に状態を診断（フォーム操作後の状態確認用）
    setTimeout(() => {
        console.log('🔬 === 診断開始 ===');

        // DOM要素の存在確認
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            const errorElement = document.getElementById(field.errorId);
            console.log(`📋 ${field.id}:`, {
                '要素存在': !!element,
                'エラー要素存在': !!errorElement,
                '現在の値': element ? (element.type === 'checkbox' ? element.checked : element.value) : 'N/A',
                'エラー表示': errorElement ? errorElement.style.display : 'N/A'
            });
        });

        // 確認ボタンの状態
        console.log(`🔘 確認ボタン:`, {
            '存在': !!confirmButton,
            '無効化': confirmButton ? confirmButton.disabled : 'N/A',
            'クラス': confirmButton ? confirmButton.className : 'N/A'
        });

        // 他のスクリプトとの競合チェック
        console.log('🔍 グローバル変数:', {
            'window.shopsData': !!window.shopsData,
            'window.siaes_ajax': !!window.siaes_ajax,
            'jQuery': !!window.jQuery
        });

        console.log('🔬 === 診断終了 ===');
    }, 5000);

    // デバッグ用：グローバル関数を追加（確実にwindowに設定）
    if (typeof window !== 'undefined') {
        window.debugReservationForm = {
            testValidation: () => {
                console.log('🧪 手動バリデーションテスト開始');
                const result = validateAllFields();
                console.log('🧪 バリデーション結果:', result);
                return result;
            },
            hideErrors: () => {
                console.log('🧪 手動エラー非表示');
                hideAllErrors();
            },
            checkElements: () => {
                console.log('🧪 要素チェック');
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    const errorElement = document.getElementById(field.errorId);
                    console.log(`${field.id}: 要素=${!!element}, エラー=${!!errorElement}`);
                });
            },
            isLoaded: true,
            version: '1.0.0'
        };
        console.log('✅ debugReservationForm グローバル関数設定完了');
    }

    console.log('🎉 予約フォーム初期化完了');
    }); // ensureDOM callback end
})(); // IIFE end