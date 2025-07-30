jQuery(document).ready(function($) {
    // form-handler.jsとの競合を防ぐため、少し遅延させる
    setTimeout(function() {

    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];
    
    // Fetch shops data from new API
    async function fetchShops() {
        try {
            const response = await fetch('/api/get_all_studio_shop.php');
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success && data.shops) {
                window.shopsData = data.shops;
            } else {
                console.error('API error:', data.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    // Initialize API call
    fetchShops();

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

    // 初期状態では確認ボタンを無効化
    if (confirmButton) {
        confirmButton.disabled = true;
    }

    // リアルタイムバリデーションの設定
    setupRealTimeValidation();

    // フォーム送信時の処理
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopPropagation(); // イベントの伝播を停止

            // バリデーション
            if (validateForm()) {
                window.showInquiryConfirmation();
            }
        });
        
        // 確認ボタンのクリック処理を削除
        // form-handler.jsが処理するため、ここでは何もしない
    }

    // バリデーション関数（フォーム送信時用）
    function validateForm() {
        return validateAndUpdateButton();
    }

    // 確認画面を表示（グローバルに公開）
    window.showInquiryConfirmation = function() {

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
        if (select && select.value && window.shopsData) {
            const selectedShop = window.shopsData.find(shop => shop.id == select.value);
            return selectedShop ? selectedShop.name : '';
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
    // 注意: 実際の送信処理はform-handler.jsが担当します
    // ここではUI状態の管理のみを行います
    if (submitButton) {
        // form-handler.jsの送信完了イベントをリスニング
        $(document).on('siaes:submission:success', function(e, data) {
            // フォームをリセット
            form.reset();
            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            
            // 店舗詳細を非表示にする
            const contactDetails = document.querySelector('.contact-details');
            if (contactDetails) {
                contactDetails.style.display = 'none';
            }
            
            window.scrollTo(0, 0);
        });
        
        $(document).on('siaes:submission:error', function(e, data) {
            // エラー時の処理はform-handler.jsが担当
            console.error('Submission error:', data);
        });
    }

    // リアルタイムバリデーションの設定
    function setupRealTimeValidation() {
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                if (element.type === 'checkbox') {
                    element.addEventListener('change', validateAndUpdateButton);
                } else {
                    element.addEventListener('input', validateAndUpdateButton);
                    element.addEventListener('blur', () => validateField(field));
                }
            }
        });

        // 店舗選択の特別処理
        const storeSelect = document.getElementById('store-select');
        if (storeSelect) {
            storeSelect.addEventListener('change', validateAndUpdateButton);
            
            // Populate store select options when shops data is available
            const populateStoreOptions = () => {
                if (window.shopsData && window.shopsData.length > 0) {
                    storeSelect.innerHTML = '<option value="">店舗を選択してください</option>';
                    window.shopsData.forEach(shop => {
                        const option = document.createElement('option');
                        option.value = shop.id;
                        option.textContent = shop.name;
                        storeSelect.appendChild(option);
                    });
                } else {
                    // Retry after a short delay if data is not loaded yet
                    setTimeout(populateStoreOptions, 500);
                }
            };
            
            populateStoreOptions();
        }
    }

    // 個別フィールドのバリデーション
    function validateField(field) {
        const element = document.getElementById(field.id);
        const errorElement = document.getElementById(field.errorId);
        const fieldContainer = element.closest('.input-field, .textarea-field, .contact-search, .confirmation-field-check');

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

    // 全フィールドのバリデーションと確認ボタンの状態更新
    function validateAndUpdateButton() {
        let allValid = true;

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                allValid = false;
            }
        });

        // 確認ボタンの有効/無効制御
        if (confirmButton) {
            confirmButton.disabled = !allValid;
        }

        return allValid;
    }

    }, 100); // 100ms遅延
});