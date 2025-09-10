document.addEventListener('DOMContentLoaded', () => {
    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            const url = '/wp-content/themes/678studio/api/get_studio_shops.php?v=' + Date.now();
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            
            // APIデータをグローバルに保存（復旧用）
            window.lastApiData = data;
            
            if (data.success) {
                populateDropdown(data.data.shops);
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
            select.innerHTML = '<option value="">店舗を選択してください</option>';
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
            return;
        }
        
        // 店舗詳細を表示
        if (contactDetails) {
            contactDetails.style.display = 'flex';
        }
        
        const imageUrl = shop.main_image 
            ? shop.main_image 
            : (shop.image_urls && shop.image_urls.length > 0 
                ? shop.image_urls[0] 
                : '/wp-content/themes/678studio/assets/images/cardpic-sample.jpg');
        imageElement.src = imageUrl;
        tableCells[0].textContent = shop.name || 'N/A';
        tableCells[1].textContent = shop.address || 'N/A';
        tableCells[2].textContent = shop.phone || 'N/A';
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
        
        // shopsDataが不正な状態の場合、グローバルなshopsDataを再取得
        if (window.shopsData && window.shopsData.length === 1 && shopId) {
            if (window.lastApiData && window.lastApiData.data && window.lastApiData.data.shops) {
                window.shopsData = window.lastApiData.data.shops;
            }
        }
        
        if (shopId && window.shopsData && window.shopsData.length > 0) {
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
                            select.value = currentValue; // Re-set value
                            
                            // Force option selection directly
                            const targetOption = select.querySelector(`option[value="${currentValue}"]`);
                            if (targetOption) {
                                targetOption.selected = true;
                            }
                            
                            // Trigger change event
                            select.dispatchEvent(new Event('change', { bubbles: true }));
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
        } else if (shopId) {
            // データがまだ読み込まれていない場合、少し待ってから再試行
            setTimeout(handleUrlParameters, 500);
        }
    }

    // データ取得完了後にURLパラメータ処理を実行
    fetchShops();
    
    // URLパラメータがある場合の処理を開始（他のスクリプトの実行を待つため遅延）
    setTimeout(handleUrlParameters, 300);
});