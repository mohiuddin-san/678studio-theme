document.addEventListener('DOMContentLoaded', () => {
    console.log('inquiry.js loaded');

    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            const response = await fetch('/api/get_all_studio_shop.php');
            console.log('API response:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            console.log('API data:', data);
            if (data.success) {
                populateDropdown(data.shops);
            } else {
                console.error('API error:', data.message);
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
        
        const shop = window.shopsData.find(s => s.id == shopId);
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

    fetchShops();
});