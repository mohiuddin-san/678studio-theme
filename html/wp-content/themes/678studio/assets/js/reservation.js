
document.addEventListener('DOMContentLoaded', () => {
    console.log('reservation.js loaded');

    // Debug DOM structure
    console.log('contact-search:', document.querySelector('.contact-search'));
    console.log('contact-select:', document.querySelector('.contact-select'));
    console.log('contact-details:', document.querySelector('.contact-details'));
    console.log('contact-image img:', document.querySelector('.contact-image img'));
    console.log('table cells:', document.querySelectorAll('.contact-info table tr td:nth-child(2)'));

    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            console.log('Attempting to fetch from new API endpoint...');
            // Use new ACF-based API endpoint with cache buster
            const response = await fetch('/wp-content/themes/678studio/api/get_studio_shops.php?v=' + Date.now());
            console.log('API response status:', response.status);
            console.log('API response headers:', response.headers);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('API error response:', errorText);
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API data received:', data);
            console.log('Data success flag:', data.success);
            console.log('Number of shops:', data.data?.shops?.length || 'undefined');
            
            if (data.success) {
                console.log('Processing shops data...');
                populateDropdown(data.data.shops);
            } else {
                console.error('API error:', data.data?.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            console.error('Error details:', error.message);
        }
    }

    function populateDropdown(shops) {
        console.log('Populating dropdown with shops:', shops);
        const select = document.querySelector('.contact-select');
        if (!select) {
            console.error('Error: .contact-select not found');
            return;
        }
        console.log('Select element before population:', select);
        try {
            select.innerHTML = '<option value="">ご予約・お問い合わせの店舗をお選びください</option>';
            shops.forEach(shop => {
                const option = document.createElement('option');
                option.value = shop.id;
                option.textContent = shop.name;
                select.appendChild(option);
            });
            console.log('Select element after population:', select);
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
        
        console.log('handleUrlParameters called - shopId:', shopId);
        console.log('window.shopsData:', window.shopsData);
        
        if (shopId && window.shopsData && window.shopsData.length > 0) {
            const select = document.querySelector('.contact-select');
            console.log('Select element found:', select);
            
            if (select) {
                console.log('Available options:', Array.from(select.options).map(opt => ({value: opt.value, text: opt.text})));
                
                // 店舗選択
                console.log('RESERVATION - Before setting - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                select.value = shopId;
                console.log('RESERVATION - After setting - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                console.log('RESERVATION - Selected option text:', select.options[select.selectedIndex]?.textContent);
                
                // 選択が成功したかチェック
                if (select.value === shopId) {
                    console.log('Store selection successful');
                    
                    // 強制的にselected属性を設定
                    Array.from(select.options).forEach(option => {
                        option.removeAttribute('selected');
                        if (option.value === shopId) {
                            option.setAttribute('selected', 'selected');
                            console.log('Selected attribute set on option:', option.textContent);
                        }
                    });
                    
                    // 強制的にブラウザに再レンダリングさせる（複数の方法）
                    setTimeout(() => {
                        console.log('Before forced refresh - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                        
                        // Method 1: Hide/show
                        select.style.display = 'none';
                        select.offsetHeight; // Force reflow
                        select.style.display = 'block';
                        
                        // Method 2: Force re-selection with timeout
                        const currentValue = select.value;
                        select.selectedIndex = -1; // Clear selection
                        setTimeout(() => {
                            // Complete DOM reset approach
                            console.log('Starting aggressive DOM reset...');
                            
                            // First, remove all selected attributes
                            Array.from(select.options).forEach(opt => {
                                opt.removeAttribute('selected');
                                opt.selected = false;
                            });
                            
                            // Find target option and force selection multiple ways
                            const targetOption = select.querySelector(`option[value="${currentValue}"]`);
                            if (targetOption) {
                                // Set selected attribute and property
                                targetOption.setAttribute('selected', 'selected');
                                targetOption.selected = true;
                                
                                // Set select element values
                                select.value = currentValue;
                                select.selectedIndex = Array.from(select.options).indexOf(targetOption);
                                
                                console.log('Aggressive reset - targetOption.selected:', targetOption.selected);
                                console.log('Aggressive reset - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                                console.log('Aggressive reset - option text:', targetOption.textContent);
                                
                                // Force browser repaint by triggering layout
                                select.style.visibility = 'hidden';
                                select.offsetHeight; // Trigger reflow
                                select.style.visibility = 'visible';
                                
                                // Trigger multiple events
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                                select.dispatchEvent(new Event('input', { bubbles: true }));
                                
                                console.log('Final verification - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                                console.log('Final verification - displayed option:', select.options[select.selectedIndex]?.textContent);
                            }
                        }, 50);
                        
                        console.log('After forced refresh - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
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
                    
                    // 隠しフィールドにも店舗IDを設定
                    const hiddenShopId = document.getElementById('hidden-shop-id');
                    if (hiddenShopId) {
                        hiddenShopId.value = shopId;
                    }
                } else {
                    console.log('Store selection failed, trying with string conversion');
                    // 数値型の可能性があるため文字列変換を試行
                    const options = Array.from(select.options);
                    const matchingOption = options.find(opt => opt.value == shopId);
                    if (matchingOption) {
                        select.value = matchingOption.value;
                        console.log('Store selection successful with type conversion:', select.value);
                        
                        // 強制的にselected属性を設定
                        Array.from(select.options).forEach(option => {
                            option.removeAttribute('selected');
                            if (option.value === matchingOption.value) {
                                option.setAttribute('selected', 'selected');
                                console.log('Selected attribute set on option (type conversion):', option.textContent);
                            }
                        });
                        
                        // 強制的にブラウザに再レンダリングさせる（複数の方法）
                        setTimeout(() => {
                            console.log('Before forced refresh (type conversion) - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                            
                            // Method 1: Hide/show
                            select.style.display = 'none';
                            select.offsetHeight; // Force reflow
                            select.style.display = 'block';
                            
                            // Method 2: Force re-selection with timeout
                            const currentValue = select.value;
                            select.selectedIndex = -1; // Clear selection
                            setTimeout(() => {
                                select.value = currentValue; // Re-set value
                                console.log('Re-set value after timeout (type conversion):', select.value, select.selectedIndex);
                                
                                // Method 3: Force option selection directly
                                const targetOption = select.querySelector(`option[value="${currentValue}"]`);
                                if (targetOption) {
                                    targetOption.selected = true;
                                    console.log('Directly set option.selected = true (type conversion) for:', targetOption.textContent);
                                }
                                
                                // Method 4: Trigger change event
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }, 50);
                            
                            console.log('After forced refresh (type conversion) - select.value:', select.value, 'selectedIndex:', select.selectedIndex);
                            console.log('Forced visual refresh completed (type conversion)');
                        }, 100);
                        
                        // エラーメッセージを非表示
                        const storeError = document.getElementById('store-error');
                        const contactSearch = document.querySelector('.contact-search');
                        if (storeError) {
                            storeError.style.display = 'none';
                            console.log('Store error message hidden (type conversion)');
                        }
                        if (contactSearch) {
                            contactSearch.classList.remove('error');
                            console.log('Error class removed from contact-search (type conversion)');
                        }
                        
                        updateContactDetails(select.value);
                        
                        // 隠しフィールドにも店舗IDを設定
                        const hiddenShopId = document.getElementById('hidden-shop-id');
                        if (hiddenShopId) {
                            hiddenShopId.value = select.value;
                        }
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
    
    // URLパラメータがある場合の処理を開始
    setTimeout(handleUrlParameters, 100);
});