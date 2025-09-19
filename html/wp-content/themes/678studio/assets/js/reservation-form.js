document.addEventListener('DOMContentLoaded', () => {
    // reservation.jsがshopsDataを管理するので、ここでは初期化しない
    // window.shopsDataはreservation.jsによって設定される

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
            reservation_date_1: document.getElementById('reservation_date_1').value,
            reservation_time_1: document.getElementById('reservation_time_1').value,
            reservation_date_2: document.getElementById('reservation_date_2').value,
            reservation_time_2: document.getElementById('reservation_time_2').value,
            reservation_date_3: document.getElementById('reservation_date_3').value,
            reservation_time_3: document.getElementById('reservation_time_3').value,
            notes: document.getElementById('notes').value.trim(),
            store: getSelectedStoreName()
        };

        return data;
    }

    // 選択された店舗名を取得
    function getSelectedStoreName() {
        const select = document.getElementById('store-select');
        
        if (select && select.value) {
            // 選択されたオプションのテキストを直接取得
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

    // 日時をフォーマット（日付と時間を組み合わせ）
    function formatDateTime(date, time) {
        if (!date || !time) return '';
        const dateObj = new Date(date);
        const year = dateObj.getFullYear();
        const month = dateObj.getMonth() + 1;
        const day = dateObj.getDate();
        return `${year}年${month}月${day}日 ${time}`;
    }

    // 確認画面にデータを表示
    function populateConfirmationData(data) {
        document.getElementById('confirmName').textContent = data.name;
        document.getElementById('confirmKana').textContent = data.kana;
        document.getElementById('confirmContact').textContent = data.contact || '入力なし';
        document.getElementById('confirmEmail').textContent = data.email;
        document.getElementById('confirmStore').textContent = data.store || '選択なし';

        // 複数の撮影希望日時を表示
        document.getElementById('confirmDateTime1').textContent = formatDateTime(data.reservation_date_1, data.reservation_time_1);
        document.getElementById('confirmDateTime2').textContent = formatDateTime(data.reservation_date_2, data.reservation_time_2) || '未設定';
        document.getElementById('confirmDateTime3').textContent = formatDateTime(data.reservation_date_3, data.reservation_time_3) || '未設定';

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

    // 送信ボタンの処理（最終送信時）
    if (submitButton) {
        submitButton.addEventListener('click', () => {
            // 最終送信時はform-handler.jsに委譲
            // この時点では確認画面が表示されているので、form-handler.jsの送信処理が実行される
        });
    }

    // リアルタイムバリデーションの設定
    function setupRealTimeValidation() {
        // リアルタイムバリデーションを削除し、確認ボタンクリック時のみバリデーションを実行
        // requiredFields.forEach(field => {
        //     const element = document.getElementById(field.id);
        //     if (element) {
        //         if (element.type === 'checkbox') {
        //             element.addEventListener('change', validateAndUpdateButton);
        //         } else {
        //             element.addEventListener('input', validateAndUpdateButton);
        //             element.addEventListener('blur', () => validateField(field));
        //         }
        //     }
        // });

        // 店舗選択の特別処理（バリデーションなし）
        // 店舗選択のドロップダウンはreservation.jsが管理
        // ここでは何もしない（reservation.jsがすべて処理）
    }

    // URLパラメータからの店舗選択はreservation.jsが処理するため、ここでは不要
    // reservation.jsがすべてのデータ取得と選択を管理します

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