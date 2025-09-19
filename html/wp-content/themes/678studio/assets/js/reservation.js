
document.addEventListener('DOMContentLoaded', () => {
    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            // Use new ACF-based API endpoint with cache buster
            const response = await fetch('/wp-content/themes/678studio/api/get_studio_shops.php?v=' + Date.now());
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                populateDropdown(data.data.shops);
                // データ取得完了後、即座にURLパラメータを処理
                handleUrlParameters();
            } else {
                console.error('API error:', data.data?.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    function populateDropdown(shops) {
        const select = document.querySelector('.contact-select');
        if (!select) {
            console.error('Error: .contact-select not found');
            return;
        }
        
        try {
            select.innerHTML = '<option value="">ご予約・お問い合わせの店舗をお選びください</option>';
            shops.forEach(shop => {
                const option = document.createElement('option');
                option.value = shop.id;
                option.textContent = shop.name;
                select.appendChild(option);
            });
            window.shopsData = shops;
        } catch (error) {
            console.error('Error populating dropdown:', error);
        }
    }

    function updateContactDetails(shopId) {
        const contactDetails = document.querySelector('.contact-details');
        const phoneHeader = document.querySelector('.contact-phone-header');

        if (!window.shopsData) {
            console.error('Error: shopsData is undefined');
            return;
        }

        const shop = window.shopsData.find(s => s.id == shopId);
        const imageElement = document.querySelector('.contact-image img');
        const tableCells = document.querySelectorAll('.contact-info table tr td:nth-child(2)');

        if (!imageElement || tableCells.length < 5) {
            console.error('Error: One or more DOM elements not found');
            return;
        }

        if (!shop || shopId === '') {
            if (contactDetails) {
                contactDetails.style.display = 'none';
            }
            if (phoneHeader) {
                phoneHeader.style.display = 'none';
            }
            return;
        }

        // 店舗詳細と電話相談ヘッダーを表示
        if (contactDetails) {
            contactDetails.style.display = 'flex';
        }
        if (phoneHeader) {
            phoneHeader.style.display = 'block';
        }
        
        const imageUrl = shop.main_image 
            ? shop.main_image 
            : (shop.image_urls && shop.image_urls.length > 0 
                ? shop.image_urls[0] 
                : '/wp-content/themes/678studio/assets/images/cardpic-sample.jpg');
        imageElement.src = imageUrl;
        tableCells[0].textContent = shop.name || 'N/A';
        tableCells[1].textContent = shop.address || 'N/A';

        // Make phone number clickable
        if (shop.phone && shop.phone !== 'N/A') {
            tableCells[2].innerHTML = `<a href="tel:${shop.phone}" style="color: inherit; text-decoration: none;">${shop.phone}</a>`;
        } else {
            tableCells[2].textContent = 'N/A';
        }

        tableCells[3].textContent = shop.business_hours || 'N/A';
        tableCells[4].textContent = shop.holidays || 'N/A';
    }

    const select = document.querySelector('.contact-select');
    if (!select) {
        console.error('Error: .contact-select not found for event listener');
    } else {
        select.addEventListener('change', (event) => {
            const shopId = event.target.value;
            updateContactDetails(shopId);
            
            // 隠しフィールドにも店舗IDを設定
            const hiddenShopId = document.getElementById('hidden-shop-id');
            if (hiddenShopId) {
                hiddenShopId.value = shopId;
            }
        });
    }

    // URLパラメータ処理用の関数
    function handleUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const shopId = urlParams.get('shop_id');
        
        // shopIdがない場合は何もしない
        if (!shopId) {
            return;
        }
        
        // データが準備できている場合のみ処理
        if (window.shopsData && window.shopsData.length > 0) {
            const select = document.querySelector('.contact-select');
            
            if (select) {
                // 店舗選択
                select.value = shopId;
                
                // 選択が成功したかチェック
                if (select.value === shopId) {
                    // 強制的にselected属性を設定
                    Array.from(select.options).forEach(option => {
                        option.removeAttribute('selected');
                        if (option.value === shopId) {
                            option.setAttribute('selected', 'selected');
                        }
                    });
                    
                    // 強制的にブラウザに再レンダリングさせる
                    setTimeout(() => {
                        const currentValue = select.value;
                        select.selectedIndex = -1; // Clear selection
                        setTimeout(() => {
                            // Complete DOM reset approach
                            Array.from(select.options).forEach(opt => {
                                opt.removeAttribute('selected');
                                opt.selected = false;
                            });
                            
                            // Find target option and force selection
                            const targetOption = select.querySelector(`option[value="${currentValue}"]`);
                            if (targetOption) {
                                targetOption.setAttribute('selected', 'selected');
                                targetOption.selected = true;
                                select.value = currentValue;
                                select.selectedIndex = Array.from(select.options).indexOf(targetOption);
                                
                                // Force browser repaint
                                select.style.visibility = 'hidden';
                                select.offsetHeight; // Trigger reflow
                                select.style.visibility = 'visible';
                                
                                // Trigger change event
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }, 50);
                    }, 100);
                    
                    // エラーメッセージを非表示
                    const storeError = document.getElementById('store-error');
                    const contactSearch = document.querySelector('.contact-search');
                    if (storeError) {
                        storeError.style.display = 'none';
                    }
                    if (contactSearch) {
                        contactSearch.classList.remove('error');
                    }
                    
                    // 店舗詳細を更新
                    updateContactDetails(shopId);
                    
                    // 隠しフィールドにも店舗IDを設定
                    const hiddenShopId = document.getElementById('hidden-shop-id');
                    if (hiddenShopId) {
                        hiddenShopId.value = shopId;
                    }
                } else {
                    // 数値型の可能性があるため文字列変換を試行
                    const options = Array.from(select.options);
                    const matchingOption = options.find(opt => opt.value == shopId);
                    if (matchingOption) {
                        select.value = matchingOption.value;
                        
                        // エラーメッセージを非表示
                        const storeError = document.getElementById('store-error');
                        const contactSearch = document.querySelector('.contact-search');
                        if (storeError) {
                            storeError.style.display = 'none';
                        }
                        if (contactSearch) {
                            contactSearch.classList.remove('error');
                        }
                        
                        updateContactDetails(select.value);
                        
                        // 隠しフィールドにも店舗IDを設定
                        const hiddenShopId = document.getElementById('hidden-shop-id');
                        if (hiddenShopId) {
                            hiddenShopId.value = select.value;
                        }
                    }
                }
            }
        }
        // データがまだ読み込まれていない場合の再試行は、fetchShops内で処理されるため不要
    }

    // フォーム送信処理
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // バリデーション
            if (validateForm()) {
                showConfirmation();
            }
        });
    }

    // 戻るボタン処理
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function() {
            showForm();
        });
    }

    // 送信ボタン処理
    const submitButton = document.getElementById('submitButton');
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            // 実際のフォーム送信処理をここに追加
            alert('フォームが送信されました（実装予定）');
        });
    }

    function validateForm() {
        let isValid = true;

        // 店舗選択チェック
        const storeSelect = document.getElementById('store-select');
        const storeError = document.getElementById('store-error');
        if (!storeSelect.value) {
            storeError.style.display = 'block';
            storeSelect.closest('.contact-search').classList.add('error');
            isValid = false;
        } else {
            storeError.style.display = 'none';
            storeSelect.closest('.contact-search').classList.remove('error');
        }

        // 必須フィールドのチェック
        const requiredFields = [
            { id: 'name', errorId: 'name-error' },
            { id: 'kana', errorId: 'kana-error' },
            { id: 'email', errorId: 'email-error' },
            { id: 'reservation_date_1', errorId: 'reservation_date_1-error' },
            { id: 'reservation_time_1', errorId: 'reservation_time_1-error' },
            { id: 'agreement', errorId: 'agreement-error' }
        ];

        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            const errorElement = document.getElementById(field.errorId);

            if (!element.value || (element.type === 'checkbox' && !element.checked)) {
                if (errorElement) errorElement.style.display = 'block';
                element.closest('.input-field, .confirmation-field-check').classList.add('error');
                isValid = false;
            } else {
                if (errorElement) errorElement.style.display = 'none';
                element.closest('.input-field, .confirmation-field-check').classList.remove('error');
            }
        });

        return isValid;
    }

    function showConfirmation() {
        // フォーム情報を確認画面に表示
        document.getElementById('confirmName').textContent = document.getElementById('name').value;
        document.getElementById('confirmKana').textContent = document.getElementById('kana').value;
        document.getElementById('confirmContact').textContent = document.getElementById('contact').value || '未入力';
        document.getElementById('confirmEmail').textContent = document.getElementById('email').value;

        // 店舗名を取得
        const storeSelect = document.getElementById('store-select');
        const storeName = storeSelect.options[storeSelect.selectedIndex].text;
        document.getElementById('confirmStore').textContent = storeName;

        // 撮影希望日時を表示
        const dateTime1 = formatDateTime(
            document.getElementById('reservation_date_1').value,
            document.getElementById('reservation_time_1').value
        );
        document.getElementById('confirmDateTime1').textContent = dateTime1;

        const dateTime2 = formatDateTime(
            document.getElementById('reservation_date_2').value,
            document.getElementById('reservation_time_2').value
        );
        document.getElementById('confirmDateTime2').textContent = dateTime2 || '未設定';

        const dateTime3 = formatDateTime(
            document.getElementById('reservation_date_3').value,
            document.getElementById('reservation_time_3').value
        );
        document.getElementById('confirmDateTime3').textContent = dateTime3 || '未設定';

        document.getElementById('confirmNotes').textContent = document.getElementById('notes').value || '未入力';

        // 画面を切り替え
        document.getElementById('formStep').style.display = 'none';
        document.getElementById('confirmationStep').style.display = 'block';
    }

    function showForm() {
        document.getElementById('formStep').style.display = 'block';
        document.getElementById('confirmationStep').style.display = 'none';
    }

    function formatDateTime(date, time) {
        if (!date || !time) return '';

        const dateObj = new Date(date);
        const year = dateObj.getFullYear();
        const month = dateObj.getMonth() + 1;
        const day = dateObj.getDate();

        return `${year}年${month}月${day}日 ${time}`;
    }

    // 日付フィールドの色変更を強制的に適用
    function setupDateFieldColorChange() {
        const dateFields = document.querySelectorAll('input[type="date"]');

        dateFields.forEach(field => {
            // 初期値チェック
            updateDateFieldColor(field);

            // 値が変更された時
            field.addEventListener('change', () => updateDateFieldColor(field));
            field.addEventListener('input', () => updateDateFieldColor(field));
        });
    }

    function updateDateFieldColor(field) {
        if (field.value) {
            field.classList.add('has-value');
        } else {
            field.classList.remove('has-value');
        }
    }

    // 日付フィールドの色変更を初期化
    setupDateFieldColorChange();

    // データ取得を開始（完了後に自動的にhandleUrlParametersが呼ばれる）
    fetchShops();
});