document.addEventListener('DOMContentLoaded', () => {
    console.log('inquiry-form.js loaded');

    const form = document.getElementById('inquiryForm');
    const formStep = document.getElementById('formStep');
    const confirmationStep = document.getElementById('confirmationStep');
    const backButton = document.getElementById('backButton');
    const submitButton = document.getElementById('submitButton');

    // フォーム送信時の処理
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            console.log('Form submitted');

            // バリデーション
            if (validateForm()) {
                showConfirmationStep();
            }
        });
    }

    // バリデーション関数
    function validateForm() {
        const requiredFields = [
            { id: 'name', label: 'お名前' },
            { id: 'kana', label: 'フリガナ' },
            { id: 'email', label: 'メールアドレス' },
            { name: 'agreement', label: '個人情報の取り扱いについて同意' }
        ];

        let isValid = true;
        const errors = [];

        requiredFields.forEach(field => {
            let element;
            if (field.id) {
                element = document.getElementById(field.id);
            } else if (field.name) {
                element = document.querySelector(`[name="${field.name}"]`);
            }

            if (!element) {
                console.error(`Element not found: ${field.id || field.name}`);
                return;
            }

            // チェックボックスの場合
            if (element.type === 'checkbox') {
                if (!element.checked) {
                    errors.push(`${field.label}が必要です`);
                    isValid = false;
                }
            } else {
                // テキストフィールドの場合
                if (!element.value.trim()) {
                    errors.push(`${field.label}を入力してください`);
                    isValid = false;
                }
            }
        });

        // メールアドレスの形式チェック
        const email = document.getElementById('email');
        if (email && email.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value.trim())) {
                errors.push('メールアドレスの形式が正しくありません');
                isValid = false;
            }
        }

        if (!isValid) {
            alert('入力内容に不備があります:\\n\\n' + errors.join('\\n'));
        }

        return isValid;
    }

    // 確認画面を表示
    function showConfirmationStep() {
        console.log('Showing confirmation step');

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

        console.log('Collected form data:', data);
        return data;
    }

    // 選択された店舗名を取得
    function getSelectedStoreName() {
        const select = document.querySelector('.contact-select');
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
            console.log('Back button clicked');
            confirmationStep.style.display = 'none';
            formStep.style.display = 'block';
            window.scrollTo(0, 0);
        });
    }

    // 送信ボタンの処理
    if (submitButton) {
        submitButton.addEventListener('click', () => {
            console.log('Submit button clicked');
            
            // ローディング状態にする
            submitButton.textContent = '送信中...';
            submitButton.disabled = true;

            // 実際の送信処理（ここではアラートで代用）
            setTimeout(() => {
                alert('お問い合わせを受け付けました。\\nありがとうございます。');
                
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
});