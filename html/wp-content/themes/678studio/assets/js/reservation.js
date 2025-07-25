
document.addEventListener('DOMContentLoaded', () => {
    console.log('reservation.js loaded');

    // Debug DOM structure
    console.log('contact-search:', document.querySelector('.contact-search'));
    console.log('contact-select:', document.querySelector('.contact-select'));
    console.log('contact-details:', document.querySelector('.contact-details'));
    console.log('contact-image img:', document.querySelector('.contact-image img'));
    console.log('contact-info h3:', document.querySelector('.contact-info h3'));
    console.log('table cells:', document.querySelectorAll('.contact-info table tr td:nth-child(2)'));

    // Initialize shopsData as empty array to prevent undefined errors
    window.shopsData = [];

    async function fetchShops() {
        try {
            // Use local JSON for testing
            const response = await fetch('https://sugamo-navi.com/api/get_all_studio_shop.php');
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
        if (!window.shopsData) {
            console.error('Error: shopsData is undefined');
            return;
        }
        const shop = window.shopsData.find(s => s.id == shopId);
        const imageElement = document.querySelector('.contact-image img');
        const titleElement = document.querySelector('.contact-info h3');
        const tableCells = document.querySelectorAll('.contact-info table tr td:nth-child(2)');

        if (!imageElement || !titleElement || tableCells.length < 5) {
            console.error('Error: One or more DOM elements not found', {
                imageElement, titleElement, tableCellsLength: tableCells.length
            });
            return;
        }

        if (!shop) {
            console.log('No shop selected, resetting to default');
            imageElement.src = '<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg';
            titleElement.textContent = '〇〇店での予約・相談';
            tableCells[0].textContent = '選択された店舗名';
            tableCells[1].textContent = '店舗住所';
            tableCells[2].textContent = '店舗電話番号';
            tableCells[3].textContent = '店舗営業時間';
            tableCells[4].textContent = '店舗定休日';
            return;
        }

        console.log('Updating with shop data:', shop);
        const imageUrl = shop.image_urls && shop.image_urls.length > 0 
            ? shop.image_urls[0] 
            : '<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg';
        imageElement.src = imageUrl;
        titleElement.textContent = `${shop.name}での予約・相談`;
        tableCells[0].textContent = shop.name;
        tableCells[1].textContent = shop.address;
        tableCells[2].textContent = shop.phone;
        tableCells[3].textContent = shop.business_hours;
        tableCells[4].textContent = shop.holidays;
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