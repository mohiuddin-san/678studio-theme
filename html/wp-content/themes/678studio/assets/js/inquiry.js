document.addEventListener('DOMContentLoaded', () => {
    console.log('=== inquiry.js loaded ===');
    console.log('Current URL:', window.location.href);

    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            console.log('=== inquiry.js fetchShops called ===');
            const url = '/wp-content/themes/678studio/api/get_studio_shops.php?v=' + Date.now();
            console.log('Fetching from URL:', url);
            const response = await fetch(url);
            console.log('API response:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Raw API data:', data);
            console.log('Shops data:', data.data?.shops);
            
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
        console.log('Populating dropdown with shops:', shops);
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
        console.log('Updating contact details for shopId:', shopId);
        const contactDetails = document.querySelector('.contact-details');
        
        if (!window.shopsData) {
            console.error('Error: shopsData is undefined');
            return;
        }
        
        console.log('Shops data IDs:', window.shopsData.map(s => ({id: s.id, type: typeof s.id, name: s.name})));
        console.log('Looking for shopId:', shopId, 'type:', typeof shopId);
        const shop = window.shopsData.find(s => s.id == shopId);
        console.log('Found shop:', shop);
        const imageElement = document.querySelector('.contact-image img');
        const tableCells = document.querySelectorAll('.contact-info table tr td:nth-child(2)');

        if (!imageElement || tableCells.length < 5) {
            console.error('Error: One or more DOM elements not found', {
                imageElement, tableCellsLength: tableCells.length
            });
            return;
        }

        if (!shop || shopId === '') {
            console.log('No shop selected, hiding contact details');
            if (contactDetails) {
                contactDetails.style.display = 'none';
            }
            return;
        }

        console.log('Updating with shop data:', shop);
        
        // 店舗詳細を表示
        if (contactDetails) {
            contactDetails.style.display = 'flex';
        }
        
        // メイン画像を使用（main_imageフィールドがある場合はそれを、ない場合はimage_urls[0]を使用）
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
        console.log('Attaching event listener to select:', select);
        select.addEventListener('change', (event) => {
            updateContactDetails(event.target.value);
        });
    }

    // URLパラメータ処理用の関数
    function handleUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const shopId = urlParams.get('shop_id');
        
        console.log('inquiry.js handleUrlParameters called - shopId:', shopId);
        console.log('window.shopsData:', window.shopsData);
        
        // shopsDataが不正な状態の場合、グローバルなshopsDataを再取得
        if (window.shopsData && window.shopsData.length === 1 && shopId) {
            console.log('WARNING: shopsData appears to be corrupted, attempting to restore from API data');
            // 最後に取得したAPIデータを再利用
            if (window.lastApiData && window.lastApiData.data && window.lastApiData.data.shops) {
                window.shopsData = window.lastApiData.data.shops;
                console.log('Restored shopsData from lastApiData:', window.shopsData);
            }
        }
        
        if (shopId && window.shopsData && window.shopsData.length > 0) {
            const select = document.querySelector('.contact-select');
            console.log('Select element found:', select);
            
            if (select) {
                console.log('Available options:', Array.from(select.options).map(opt => ({value: opt.value, text: opt.text})));
                
                // 店舗選択
                console.log('Before setting - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                select.value = shopId;
                console.log('After setting - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                console.log('Selected option text:', select.options[select.selectedIndex]?.textContent);
                
                // 選択が成功したかチェック
                if (select.value === shopId) {
                    console.log('Store selection successful');
                    
                    // 強制的にDOM更新とchangeイベント発火
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log('Change event dispatched');
                    
                    // 強制的にselected属性を設定
                    Array.from(select.options).forEach(option => {
                        option.removeAttribute('selected');
                        if (option.value === shopId) {
                            option.setAttribute('selected', 'selected');
                            console.log('Selected attribute set on option:', option.textContent);
                        }
                    });
                    
                    // 少し遅延してからDOMの状態を再確認と強制再描画
                    setTimeout(() => {
                        console.log('Final DOM state - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                        console.log('Final selected option:', select.options[select.selectedIndex]?.textContent);
                        
                        // 強制的にブラウザに再レンダリングさせる
                        select.style.display = 'none';
                        select.offsetHeight; // Force reflow
                        select.style.display = 'block';
                        console.log('Forced visual refresh completed');
                    }, 100);
                    
                    // エラーメッセージを非表示
                    const storeError = document.getElementById('store-error');
                    const contactSearch = document.querySelector('.contact-search');
                    if (storeError) {
                        storeError.style.display = 'none';
                        console.log('Store error message hidden');
                    }
                    if (contactSearch) {
                        contactSearch.classList.remove('error');
                        console.log('Error class removed from contact-search');
                    }
                    
                    // 店舗詳細を更新
                    updateContactDetails(shopId);
                } else {
                    console.log('Store selection failed, trying alternative methods');
                    
                    // Method 1: selectedIndex による選択
                    const options = Array.from(select.options);
                    const matchingIndex = options.findIndex(opt => opt.value == shopId);
                    if (matchingIndex > -1) {
                        select.selectedIndex = matchingIndex;
                        console.log('Store selection successful with selectedIndex:', select.value, 'index:', matchingIndex);
                        
                        // エラーメッセージを非表示
                        const storeError = document.getElementById('store-error');
                        const contactSearch = document.querySelector('.contact-search');
                        if (storeError) {
                            storeError.style.display = 'none';
                            console.log('Store error message hidden (selectedIndex method)');
                        }
                        if (contactSearch) {
                            contactSearch.classList.remove('error');
                            console.log('Error class removed from contact-search (selectedIndex method)');
                        }
                        
                        updateContactDetails(select.value);
                    } else {
                        console.error('No matching store option found for shopId:', shopId);
                    }
                }
            }
        } else if (shopId) {
            console.log('Retrying handleUrlParameters - data not ready yet');
            // データがまだ読み込まれていない場合、少し待ってから再試行
            setTimeout(handleUrlParameters, 500);
        }
    }

    // データ取得完了後にURLパラメータ処理を実行
    fetchShops();
    
    // URLパラメータがある場合の処理を開始（他のスクリプトの実行を待つため遅延）
    setTimeout(handleUrlParameters, 300);
});