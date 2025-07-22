<?php
/*
Template Name: Shop List
*/

// Enqueue Tailwind CSS and Select2
function shop_list_enqueue_scripts() {
    // Tailwind CSS from CDN
    wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
    // Select2 CSS
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    // Select2 JS
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    // Custom JS for search functionality
    wp_enqueue_script('shop-list-js', get_template_directory_uri() . '/shop-list.js', array('jquery', 'select2'), null, true);
}
add_action('wp_enqueue_scripts', 'shop_list_enqueue_scripts');

// Fetch API data
function fetch_shop_data() {
    $response = wp_remote_get('https://sugamo-navi.com/api/get_all_studio_shop.php');
    if (is_wp_error($response)) {
        return [];
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    return isset($data['shops']) ? $data['shops'] : [];
}

get_header();
?>

<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Shop List</h1>

    <!-- Search Dropdowns -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="w-full md:w-1/2">
            <label for="station-search" class="block text-sm font-medium text-gray-700">Search by Station</label>
            <select id="station-search" class="w-full p-2 border rounded">
                <option value="">Select a Station</option>
                <?php
                $shops = fetch_shop_data();
                $stations = array_unique(array_column($shops, 'nearest_station'));
                foreach ($stations as $station) {
                    echo '<option value="' . esc_attr($station) . '">' . esc_html($station) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="w-full md:w-1/2">
            <label for="shop-search" class="block text-sm font-medium text-gray-700">Search by Shop Name</label>
            <select id="shop-search" class="w-full p-2 border rounded">
                <option value="">Select a Shop</option>
                <?php
                $shop_names = array_unique(array_column($shops, 'name'));
                foreach ($shop_names as $name) {
                    echo '<option value="' . esc_attr($name) . '">' . esc_html($name) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Shop List -->
    <div id="shop-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        foreach ($shops as $shop) {
            ?>
            <div class="shop-card bg-white shadow-md rounded-lg p-4" data-station="<?php echo esc_attr($shop['nearest_station']); ?>" data-name="<?php echo esc_attr($shop['name']); ?>">
                <h2 class="text-xl font-semibold"><?php echo esc_html($shop['name']); ?></h2>
                <p><strong>Address:</strong> <?php echo esc_html($shop['address']); ?></p>
                <p><strong>Phone:</strong> <?php echo esc_html($shop['phone']); ?></p>
                <p><strong>Nearest Station:</strong> <?php echo esc_html($shop['nearest_station']); ?></p>
                <p><strong>Business Hours:</strong> <?php echo esc_html($shop['business_hours']); ?></p>
                <p><strong>Holidays:</strong> <?php echo esc_html($shop['holidays']); ?></p>
                <?php if (!empty($shop['image_urls'])) : ?>
                    <div class="mt-2">
                        <?php foreach ($shop['image_urls'] as $image_url) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($shop['name']); ?>" class="w-full h-40 object-cover rounded mb-2">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="<?php echo esc_url($shop['map_url']); ?>" target="_blank" class="text-blue-500 hover:underline">View on Map</a>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Select2 for dropdowns
    $('#station-search').select2({
        placeholder: "Select a Station",
        allowClear: true
    });
    $('#shop-search').select2({
        placeholder: "Select a Shop",
        allowClear: true
    });

    // Search functionality
    function filterShops() {
        var selectedStation = $('#station-search').val();
        var selectedShop = $('#shop-search').val();

        $('.shop-card').each(function() {
            var shopStation = $(this).data('station');
            var shopName = $(this).data('name');
            var show = true;

            if (selectedStation && shopStation !== selectedStation) {
                show = false;
            }
            if (selectedShop && shopName !== selectedShop) {
                show = false;
            }

            $(this).toggle(show);
        });
    }

    $('#station-search, #shop-search').on('change', filterShops);
});
</script>

<?php get_footer(); ?>