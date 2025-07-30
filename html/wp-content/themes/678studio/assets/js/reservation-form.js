document.addEventListener('DOMContentLoaded', () => {

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
        { id: 'reservation_date', errorId: 'reservation_date-error', message: '撮影希望日を選択してください' },
        { id: 'reservation_time', errorId: 'reservation_time-error', message: '開始時間を選択してください' },
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

            // バリデーション
            if (validateForm()) {
                showConfirmationStep();
            }
        });
    }

    // バリデーション関数（フォーム送信時用）
    function validateForm() {
        return validateAndUpdateButton();
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
            reservation_date: document.getElementById('reservation_date').value,
            reservation_time: document.getElementById('reservation_time').value,
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

    // 日付をフォーマット
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}年${month}月${day}日`;
    }

    // 時間をフォーマット
    function formatTime(timeString) {
        if (!timeString) return '';
        return timeString;
    }

    // 確認画面にデータを表示
    function populateConfirmationData(data) {
        document.getElementById('confirmName').textContent = data.name;
        document.getElementById('confirmKana').textContent = data.kana;
        document.getElementById('confirmContact').textContent = data.contact || '入力なし';
        document.getElementById('confirmEmail').textContent = data.email;
        document.getElementById('confirmStore').textContent = data.store || '選択なし';
        document.getElementById('confirmDate').textContent = formatDate(data.reservation_date);
        document.getElementById('confirmTime').textContent = formatTime(data.reservation_time);
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
        submitButton.addEventListener('click', () => {
            
            // ローディング状態にする
            submitButton.textContent = '送信中...';
            submitButton.disabled = true;

            // 実際の送信処理（ここではアラートで代用）
            setTimeout(() => {
                alert('ご予約を受け付けました。\\nありがとうございます。');
                
                // フォームをリセット
                form.reset();
                confirmationStep.style.display = 'none';
                formStep.style.display = 'block';
                
                // ボタンを元に戻す
                submitButton.textContent = '送信する';
                submitButton.disabled = false;
                
                // 店舗詳細を非表示にする
                const contactDetails = document.querySelector('.contact-details');
                if (contactDetails) {
                    contactDetails.style.display = 'none';
                }
                
                window.scrollTo(0, 0);
            }, 2000);
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
});