<?php
/*
Plugin Name: Studio Shops Manager
Description: Manage Studio Shops and their gallery images via API in WordPress admin dashboard.
Version: 1.7.4
Author: Your Name
*/

defined('ABSPATH') or die('No direct access allowed.');

// Add admin menu for Studio Shops
add_action('admin_menu', 'studio_shops_menu');
function studio_shops_menu() {
    add_menu_page(
        'Studio Shops',
        'Studio Shops',
        'manage_options',
        'studio-shops',
        'studio_shops_admin_page',
        'dashicons-store',
        20
    );
}

// Admin page rendering and form handling
function studio_shops_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Studio Shops Manager', 'studio-shops'); ?></h1>
        
        <div>
            <label><input type="checkbox" id="update-mode" name="update_mode"> Update Existing Shop</label>
            <div id="shop-selector" style="display:none; margin-top:10px;">
                <select name="shop_id" id="shop-id-select">
                    <option value="">Select a Shop</option>
                </select>
            </div>
        </div>

        <?php
        // Check if form is submitted
        $is_update_mode = isset($_POST['update_mode']) && $_POST['update_mode'] === 'on';
        
        if (isset($_POST['submit_shop']) && check_admin_referer('studio_shops_save', 'studio_shops_nonce')) {
            $name = sanitize_text_field($_POST['name']);
            $address = sanitize_textarea_field($_POST['address']);
            $phone = sanitize_text_field($_POST['phone']);
            $nearest_station = sanitize_text_field($_POST['nearest_station']);
            $business_hours = sanitize_text_field($_POST['business_hours']);
            $holidays = sanitize_text_field($_POST['holidays']);
            $map_url = wp_kses($_POST['map_url'], [
                'iframe' => [
                    'src' => [],
                    'width' => [],
                    'height' => [],
                    'style' => [],
                    'frameborder' => [],
                    'allowfullscreen' => [],
                    'loading' => []
                ]
            ]);
            $company_email = sanitize_email($_POST['company_email']);
            $category_names = $_POST['category_name'] ?? [];
            $gallery_files = $_FILES['gallery_images'] ?? [];
            $shop_id = isset($_POST['shop_id']) ? sanitize_text_field($_POST['shop_id']) : '';

            // Debug: Log the shop_id to verify it's being received
            error_log('Submitted shop_id: ' . $shop_id);

            // Validate shop_id for update mode
            if ($is_update_mode && empty($shop_id)) {
                echo '<div class="error"><p>' . esc_html__('Error: Shop ID is missing during update.', 'studio-shops') . '</p></div>';
            } else {
                $api_data = [
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone,
                    'nearest_station' => $nearest_station,
                    'business_hours' => $business_hours,
                    'holidays' => $holidays,
                    'map_url' => $map_url,
                    'company_email' => $company_email,
                    'gallery_images' => []
                ];

                // Include shop_id in the API payload for update mode
                if ($is_update_mode && $shop_id) {
                    $api_data['shop_id'] = $shop_id;
                }

                // Debug: Log the API payload
                error_log('API Payload: ' . print_r($api_data, true));

                // Handle main gallery images
                if (!empty($_FILES['gallery_images_flat'])) {
                    $gallery_flat_files = $_FILES['gallery_images_flat'];
                    for ($i = 0; $i < count($gallery_flat_files['name']); $i++) {
                        if ($gallery_flat_files['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp_name = $gallery_flat_files['tmp_name'][$i];
                            $image_data = file_get_contents($tmp_name);
                            $image_type = $gallery_flat_files['type'][$i];
                            $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                            $api_data['gallery_images'][] = $base64_image;
                        }
                    }
                }

                echo '<div id="loader" style="padding:10px; font-weight:bold; color:blue;">Processing shop data, please wait...</div>';

                // Send API request for shop creation or update
                $api_url = $is_update_mode ? 
                    'https://678photo.com/api/update_shop_details.php' : 
                    'https://678photo.com/api/studio_shop.php';

                $response = wp_remote_post($api_url, [
                    'method' => 'POST',
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($api_data),
                    'timeout' => 60
                ]);

                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo '<div class="error"><p>' . esc_html__('API request failed: ' . $error_message, 'studio-shops') . '</p></div>';
                } else {
                    $response_body = json_decode(wp_remote_retrieve_body($response), true);
                    error_log('API Response (Submit): ' . print_r($response_body, true)); // Debug API response
                    if (isset($response_body['success']) && $response_body['success']) {
                        $shop_id = $is_update_mode ? $shop_id : ($response_body['shop_id'] ?? '');
                        
                        if ($shop_id) {
                            // Prepare category gallery payload
                            $category_gallery = [];
                            foreach ($category_names as $cat_index => $cat_name) {
                                $cat_name = sanitize_text_field($cat_name);
                                $category_gallery[$cat_name] = [];

                                if (isset($gallery_files['name'][$cat_index])) {
                                    foreach ($gallery_files['name'][$cat_index] as $img_index => $img_name) {
                                        if ($gallery_files['error'][$cat_index][$img_index] === UPLOAD_ERR_OK) {
                                            $tmp_name = $gallery_files['tmp_name'][$cat_index][$img_index];
                                            $image_data = file_get_contents($tmp_name);
                                            $image_type = $gallery_files['type'][$cat_index][$img_index];
                                            $base64_image = 'data:' . $image_type . ';base64,' . base64_encode($image_data);
                                            $category_gallery[$cat_name][] = $base64_image;
                                        }
                                    }
                                }
                            }

                            $final_payload = [
                                'shop_id' => $shop_id,
                                'gallery' => []
                            ];

                            foreach ($category_gallery as $cat => $images) {
                                if (!empty($images)) {
                                    $final_payload['gallery'][] = [
                                        'category_name' => $cat,
                                        'images' => $images
                                    ];
                                }
                            }

                            // Send category images to the appropriate API
                            $category_api_url = $is_update_mode ? 
                                'https://678photo.com/api/update_shop_category_images.php' : 
                                'https://678photo.com/api/category_image_uploader.php';

                            if (!empty($final_payload['gallery'])) {
                                $image_response = wp_remote_post($category_api_url, [
                                    'method' => 'POST',
                                    'headers' => ['Content-Type' => 'application/json'],
                                    'body' => json_encode($final_payload),
                                    'timeout' => 60
                                ]);

                                if (is_wp_error($image_response)) {
                                    echo '<div class="error"><p>' . esc_html__('Failed to upload category images: ' . $image_response->get_error_message(), 'studio-shops') . '</p></div>';
                                } else {
                                    $image_response_body = json_decode(wp_remote_retrieve_body($image_response), true);
                                    error_log('Category Image API Response: ' . print_r($image_response_body, true));
                                    if (isset($image_response_body['success']) && $image_response_body['success']) {
                                        echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop and category images updated successfully!' : 'Shop created and category images uploaded successfully!', 'studio-shops') . '</p></div>';
                                    } else {
                                        echo '<div class="error"><p>' . esc_html__('Failed to upload category images: ' . ($image_response_body['error'] ?? 'Unknown error'), 'studio-shops') . '</p></div>';
                                    }
                                }
                            } else {
                                echo '<div class="updated"><p>' . esc_html__($is_update_mode ? 'Shop updated successfully!' : 'Shop created successfully!', 'studio-shops') . '</p></div>';
                            }
                        } else {
                            echo '<div class="error"><p>' . esc_html__('Operation failed: Shop ID not returned from API.', 'studio-shops') . '</p></div>';
                        }
                    } else {
                        echo '<div class="error"><p>' . esc_html__('Operation failed: ' . ($response_body['error'] ?? 'Unknown error'), 'studio-shops') . '</p></div>';
                    }
                }
            }
        }
        ?>

        <h2><?php esc_html_e($is_update_mode ? 'Update Shop' : 'Add New Shop', 'studio-shops'); ?></h2>
        <form method="post" enctype="multipart/form-data" id="shop-form">
            <?php wp_nonce_field('studio_shops_save', 'studio_shops_nonce'); ?>
            <input type="hidden" name="update_mode" id="update_mode" value="<?php echo $is_update_mode ? 'on' : 'off'; ?>">
            <input type="hidden" name="shop_id" id="shop_id" value="">
            <table class="form-table">
                <tr><th><label for="name">Name</label></th><td><input type="text" name="name" id="name" required></td></tr>
                <tr><th><label for="address">Address</label></th><td><textarea name="address" id="address" required></textarea></td></tr>
                <tr><th><label for="phone">Phone</label></th><td><input type="text" name="phone" id="phone"></td></tr>
                <tr><th><label for="nearest_station">Nearest Station</label></th><td><input type="text" name="nearest_station" id="nearest_station"></td></tr>
                <tr><th><label for="business_hours">Business Hours</label></th><td><input type="text" name="business_hours" id="business_hours"></td></tr>
                <tr><th><label for="holidays">Holidays</label></th><td><input type="text" name="holidays" id="holidays"></td></tr>
                <tr><th><label for="map_url">Map Embed Code</label></th><td><textarea name="map_url" id="map_url" rows="4" cols="50" placeholder="Paste your map embed code (e.g., Google Maps iframe)"></textarea></td></tr>     
                <tr><th><label for="company_email">Company Email</label></th><td><input type="email" name="company_email" id="company_email"></td></tr>
            </table>

            <h3>Main Gallery (no category)</h3>
            <p><input type="file" name="gallery_images_flat[]" multiple accept="image/*" id="main-gallery-input"></p>
            <div id="main-gallery-preview"></div>

            <h3>Gallery by Category</h3>
            <div id="category-gallery-wrapper">
                <div class="category-gallery-block" data-category-id="">
                    <input type="text" name="category_name[]" placeholder="Category Name" required>
                    <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                    <div class="category-preview" data-index="0"></div>
                    <button type="button" class="delete-category button">Delete Category</button>
                </div>
            </div>
            <p><button type="button" id="add-category-block" class="button">+ Add Category</button></p>

            <p class="submit">
                <input type="submit" name="submit_shop" id="submit_shop" class="button button-primary" value="<?php echo $is_update_mode ? 'Update Shop' : 'Add Shop'; ?>">
            </p>
        </form>

        <style>
            .image-preview {
                display: inline-block;
                position: relative;
                margin: 5px;
            }
            .image-preview img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .remove-image, .delete-category {
                background: red;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 14px;
                width: 20px;
                height: 20px;
                cursor: pointer;
                position: absolute;
                top: -8px;
                right: -8px;
            }
            .delete-category {
                border-radius: 4px;
                width: auto;
                height: auto;
                padding: 5px 10px;
                position: static;
                margin-top: 5px;
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('studio-shops-manager.js loaded');

            // Debug DOM structure
            console.log('update-mode:', document.getElementById('update-mode'));
            console.log('shop-selector:', document.getElementById('shop-selector'));
            console.log('shop-id-select:', document.getElementById('shop-id-select'));
            console.log('shop-form:', document.getElementById('shop-form'));
            console.log('main-gallery-preview:', document.getElementById('main-gallery-preview'));
            console.log('category-gallery-wrapper:', document.getElementById('category-gallery-wrapper'));

            const updateCheckbox = document.getElementById('update-mode');
            const shopSelector = document.getElementById('shop-selector');
            const shopSelect = document.getElementById('shop-id-select');
            const form = document.getElementById('shop-form');
            const mainGalleryPreview = document.getElementById('main-gallery-preview');
            const categoryGalleryWrapper = document.getElementById('category-gallery-wrapper');

            // Initialize shopsData to store shop details
            window.shopsData = [];

            // Function to reset the form completely
            function resetForm() {
                console.log('Resetting form...');
                form.reset();
                document.getElementById('shop_id').value = '';
                document.getElementById('name').value = '';
                document.getElementById('address').value = '';
                document.getElementById('phone').value = '';
                document.getElementById('nearest_station').value = '';
                document.getElementById('business_hours').value = '';
                document.getElementById('holidays').value = '';
                document.getElementById('map_url').value = '';
                document.getElementById('company_email').value = '';
                document.getElementById('main-gallery-input').value = '';
                mainGalleryPreview.innerHTML = '<p>No images selected.</p>';
                categoryGalleryWrapper.innerHTML = `
                    <div class="category-gallery-block" data-category-id="">
                        <input type="text" name="category_name[]" placeholder="Category Name" required>
                        <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                        <div class="category-preview" data-index="0"></div>
                        <button type="button" class="delete-category button">Delete Category</button>
                    </div>
                `;
            }

            // Validate shop_id before form submission
            form.addEventListener('submit', (e) => {
                if (updateCheckbox.checked && !document.getElementById('shop_id').value) {
                    e.preventDefault();
                    alert('Please select a shop to update.');
                }
            });

            // Fetch shop list
            async function fetchShops() {
                try {
                    const response = await fetch('https://678photo.com/api/get_all_studio_shop.php?t=' + new Date().getTime(), {
                        cache: 'no-store'
                    });
                    console.log('Shop List API Response:', response);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    const data = await response.json();
                    console.log('Shop List API Data:', data);
                    if (data.success && data.shops) {
                        window.shopsData = data.shops;
                        populateDropdown(data.shops);
                    } else {
                        console.error('Failed to fetch shops:', data.error || 'No shops found');
                        alert('Failed to load shop list');
                    }
                } catch (error) {
                    console.error('Error fetching shops:', error);
                    alert('Failed to load shop list');
                }
            }

            // Populate shop dropdown
            function populateDropdown(shops) {
                console.log('Populating dropdown with shops:', shops);
                if (!shopSelect) {
                    console.error('Error: shop-id-select not found');
                    return;
                }
                shopSelect.innerHTML = '<option value="">Select a Shop</option>';
                shops.forEach(shop => {
                    const option = document.createElement('option');
                    option.value = shop.id;
                    option.textContent = shop.name;
                    shopSelect.appendChild(option);
                });
                console.log('Dropdown populated:', shopSelect);
            }

            // Populate form with shop details
            function updateShopDetails(shopId) {
                console.log('Updating shop details for shopId:', shopId);
                resetForm(); // Reset form before populating new data
                document.getElementById('shop_id').value = shopId;

                if (!shopId) {
                    console.log('No shop selected, form reset');
                    return;
                }

                const shop = window.shopsData.find(s => s.id == shopId);
                if (!shop) {
                    console.error('Shop not found for ID:', shopId);
                    alert('Shop data not found');
                    return;
                }

                console.log('Populating form with shop data:', shop);
                document.getElementById('name').value = shop.name || '';
                document.getElementById('address').value = shop.address || '';
                document.getElementById('phone').value = shop.phone || '';
                document.getElementById('nearest_station').value = shop.nearest_station || '';
                document.getElementById('business_hours').value = shop.business_hours || '';
                document.getElementById('holidays').value = shop.holidays || '';
                document.getElementById('map_url').value = shop.map_url || '';
                document.getElementById('company_email').value = shop.company_email || '';

                // Populate main gallery images from image_urls
                mainGalleryPreview.innerHTML = '';
                if (shop.image_urls && shop.image_urls.length > 0) {
                    shop.image_urls.forEach((imageUrl, index) => {
                        const div = document.createElement('div');
                        div.classList.add('image-preview');
                        div.innerHTML = `
                            <img src="${imageUrl}" alt="Main Gallery Image">
                            <button type="button" class="remove-image" data-type="main" data-index="${index}">×</button>
                        `;
                        mainGalleryPreview.appendChild(div);
                    });
                } else {
                    mainGalleryPreview.innerHTML = '<p>No main gallery images available.</p>';
                }

                // Populate category gallery from category_images
                categoryGalleryWrapper.innerHTML = '';
                if (shop.category_images && Object.keys(shop.category_images).length > 0) {
                    let index = 0;
                    for (const [categoryName, images] of Object.entries(shop.category_images)) {
                        const block = document.createElement('div');
                        block.classList.add('category-gallery-block');
                        block.dataset.categoryId = ''; // Fetch category IDs if available from API
                        block.innerHTML = `
                            <input type="text" name="category_name[]" value="${categoryName}" placeholder="Category Name" required>
                            <input type="file" name="gallery_images[${index}][]" multiple accept="image/*" class="category-image-input">
                            <div class="category-preview" data-index="${index}"></div>
                            <button type="button" class="delete-category button">Delete Category</button>
                        `;
                        categoryGalleryWrapper.appendChild(block);

                        const preview = block.querySelector('.category-preview');
                        if (images && images.length > 0) {
                            images.forEach((imageUrl, imgIndex) => {
                                const div = document.createElement('div');
                                div.classList.add('image-preview');
                                div.innerHTML = `
                                    <img src="${imageUrl}" alt="Category Image">
                                    <button type="button" class="remove-image" data-type="category" data-index="${imgIndex}" data-image-id="">×</button>
                                `;
                                preview.appendChild(div);
                            });
                        } else {
                            preview.innerHTML = '<p>No images available for this category.</p>';
                        }
                        index++;
                    }
                } else {
                    categoryGalleryWrapper.innerHTML = `
                        <div class="category-gallery-block" data-category-id="">
                            <input type="text" name="category_name[]" placeholder="Category Name" required>
                            <input type="file" name="gallery_images[0][]" multiple accept="image/*" class="category-image-input">
                            <div class="category-preview" data-index="0"></div>
                            <button type="button" class="delete-category button">Delete Category</button>
                        </div>
                    `;
                }
            }

            // Toggle update mode
            updateCheckbox.addEventListener('change', () => {
                console.log('Update mode changed:', updateCheckbox.checked);
                shopSelector.style.display = updateCheckbox.checked ? 'block' : 'none';
                document.getElementById('update_mode').value = updateCheckbox.checked ? 'on' : 'off';
                document.getElementById('submit_shop').value = updateCheckbox.checked ? 'Update Shop' : 'Add Shop';
                shopSelect.value = ''; // Reset shop selection
                updateShopDetails(''); // Reset form
            });

            // Populate form on shop selection
            shopSelect.addEventListener('change', (event) => {
                console.log('Shop selected:', event.target.value);
                updateShopDetails(event.target.value);
            });

            // Add new category block
            document.getElementById('add-category-block').addEventListener('click', () => {
                let index = categoryGalleryWrapper.children.length;
                let block = document.createElement('div');
                block.classList.add('category-gallery-block');
                block.dataset.categoryId = '';
                block.innerHTML = `
                    <input type="text" name="category_name[]" placeholder="Category Name" required>
                    <input type="file" name="gallery_images[${index}][]" multiple accept="image/*" class="category-image-input">
                    <div class="category-preview" data-index="${index}"></div>
                    <button type="button" class="delete-category button">Delete Category</button>
                `;
                categoryGalleryWrapper.appendChild(block);
            });

            // Main gallery preview for new uploads
            document.getElementById('main-gallery-input').addEventListener('change', function () {
                handleFilePreview(this, mainGalleryPreview, 'main');
            });

            // Category gallery preview for new uploads
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('category-image-input')) {
                    const input = e.target;
                    const block = input.closest('.category-gallery-block');
                    const previewContainer = block.querySelector('.category-preview');
                    handleFilePreview(input, previewContainer, 'category');
                }
            });

            // Delete category
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('delete-category')) {
                    const block = e.target.closest('.category-gallery-block');
                    const categoryId = block.dataset.categoryId;
                    
                    if (categoryId) {
                        if (confirm('Are you sure you want to delete this category?')) {
                            fetch('https://678photo.com/api/delete_category.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ category_id: categoryId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    block.remove();
                                    alert('Category deleted successfully');
                                } else {
                                    alert('Failed to delete category: ' + (data.error || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting category:', error);
                                alert('Failed to delete category');
                            });
                        }
                    } else {
                        block.remove();
                    }
                }
            });

            // Delete image
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-image')) {
                    const imageId = e.target.dataset.imageId;
                    const type = e.target.dataset.type;
                    const block = type === 'category' ? e.target.closest('.category-gallery-block') : null;
                    const previewContainer = type === 'main' ? mainGalleryPreview : block.querySelector('.category-preview');

                    if (imageId) {
                        if (confirm('Are you sure you want to delete this image?')) {
                            fetch('https://678photo.com/api/delete_category_image.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ image_id: imageId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    e.target.closest('.image-preview').remove();
                                    alert('Image deleted successfully');
                                } else {
                                    alert('Failed to delete image: ' + (data.error || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting image:', error);
                                alert('Failed to delete image');
                            });
                        }
                        return;
                    }

                    // Handle local removal for uploaded but not yet saved images
                    const input = type === 'main' ? document.getElementById('main-gallery-input') : block.querySelector('.category-image-input');
                    const indexToRemove = e.target.dataset.index;
                    const dt = new DataTransfer();
                    Array.from(input.files).forEach((file, i) => {
                        if (i !== parseInt(indexToRemove)) {
                            dt.items.add(file);
                        }
                    });

                    const newInput = input.cloneNode(true);
                    newInput.files = dt.files;
                    input.replaceWith(newInput);
                    handleFilePreview(newInput, previewContainer, type);
                }
            });

            // Preview handler for new uploads
            function handleFilePreview(input, previewContainer, type) {
                previewContainer.innerHTML = '';
                if (input.files.length === 0) {
                    previewContainer.innerHTML = '<p>No images selected.</p>';
                    return;
                }
                Array.from(input.files).forEach((file, i) => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const div = document.createElement('div');
                        div.classList.add('image-preview');
                        div.innerHTML = `
                            <img src="${event.target.result}" alt="New Image">
                            <button type="button" class="remove-image" data-type="${type}" data-index="${i}">×</button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Fetch shops on page load
            fetchShops();
        });
        </script>
    </div>
    <?php
}

// Load plugin text domain for translations
add_action('init', 'studio_shops_load_textdomain');
function studio_shops_load_textdomain() {
    load_plugin_textdomain('studio-shops', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
?>