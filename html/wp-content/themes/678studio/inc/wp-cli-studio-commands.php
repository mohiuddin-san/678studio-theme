<?php
/**
 * WP-CLI Commands for Studio Shop Management
 * 
 * Usage:
 * wp studio update <shop_id> --json='{"name":"店舗名","address":"住所"}'
 * wp studio update <shop_id> --file=/path/to/shop-data.json
 * wp studio create --json='{"name":"新店舗","address":"東京都..."}'
 * wp studio get <shop_id>
 * wp studio list
 */

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

class Studio_Shop_CLI_Commands {
    
    /**
     * Update an existing studio shop
     * 
     * ## OPTIONS
     * 
     * <shop_id>
     * : The ID of the shop to update
     * 
     * [--json=<json>]
     * : JSON string with shop data
     * 
     * [--file=<file>]
     * : Path to JSON file with shop data
     * 
     * [--images=<path>]
     * : Path to directory containing images to upload
     * 
     * [--category-images=<category:path>]
     * : Category images in format "category:/path/to/dir" (can be used multiple times)
     * 
     * ## EXAMPLES
     * 
     *     wp studio update 1 --json='{"name":"えがお写真館 渋谷店","address":"東京都渋谷区..."}'
     *     wp studio update 1 --file=/path/to/shop-data.json
     *     wp studio update 1 --file=/path/to/shop-data.json --images=/path/to/images/
     *     wp studio update 1 --json='{"name":"店舗名"}' --images=/path/to/main-images/ --category-images=interior:/path/to/interior/ --category-images=staff:/path/to/staff/
     * 
     * @when after_wp_load
     */
    public function update($args, $assoc_args) {
        $shop_id = $args[0];
        
        // Get data from JSON string or file
        $data = $this->get_data_from_args($assoc_args);
        
        if (is_wp_error($data)) {
            WP_CLI::error($data->get_error_message());
        }
        
        // Add shop_id to data
        $data['shop_id'] = $shop_id;
        
        // Handle local image directories
        if (isset($assoc_args['images'])) {
            $images_path = rtrim($assoc_args['images'], '/');
            if (!is_dir($images_path)) {
                WP_CLI::error("Images directory not found: $images_path");
            }
            
            WP_CLI::log("Loading images from: $images_path");
            $data['image_files'] = $this->load_images_from_directory($images_path);
            WP_CLI::log("Found " . count($data['image_files']) . " images");
        }
        
        // Handle category images
        if (isset($assoc_args['category-images'])) {
            $category_images = is_array($assoc_args['category-images']) ? $assoc_args['category-images'] : [$assoc_args['category-images']];
            $data['category_image_files'] = [];
            
            foreach ($category_images as $category_path) {
                list($category, $path) = explode(':', $category_path, 2);
                $path = rtrim($path, '/');
                
                if (!is_dir($path)) {
                    WP_CLI::warning("Category images directory not found: $path");
                    continue;
                }
                
                WP_CLI::log("Loading $category images from: $path");
                $images = $this->load_images_from_directory($path);
                if (!empty($images)) {
                    $data['category_image_files'][$category] = $images;
                    WP_CLI::log("Found " . count($images) . " images for category: $category");
                }
            }
        }
        
        WP_CLI::log("Updating shop $shop_id...");
        
        // Send to API
        $result = $this->send_to_api($data, true);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        } else {
            WP_CLI::success("Shop $shop_id updated successfully!");
            if (!empty($result['message'])) {
                WP_CLI::log("API Response: " . $result['message']);
            }
        }
    }
    
    /**
     * Create a new studio shop
     * 
     * ## OPTIONS
     * 
     * [--json=<json>]
     * : JSON string with shop data
     * 
     * [--file=<file>]
     * : Path to JSON file with shop data
     * 
     * ## EXAMPLES
     * 
     *     wp studio create --json='{"name":"新店舗","address":"東京都..."}'
     *     wp studio create --file=/path/to/new-shop.json
     * 
     * @when after_wp_load
     */
    public function create($args, $assoc_args) {
        $data = $this->get_data_from_args($assoc_args);
        
        if (is_wp_error($data)) {
            WP_CLI::error($data->get_error_message());
        }
        
        WP_CLI::log("Creating new shop...");
        
        // Send to API
        $result = $this->send_to_api($data, false);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        } else {
            WP_CLI::success("Shop created successfully!");
            if (!empty($result['shop_id'])) {
                WP_CLI::log("New shop ID: " . $result['shop_id']);
            }
            if (!empty($result['message'])) {
                WP_CLI::log("API Response: " . $result['message']);
            }
        }
    }
    
    /**
     * Get shop data by ID
     * 
     * ## OPTIONS
     * 
     * <shop_id>
     * : The ID of the shop to retrieve
     * 
     * [--format=<format>]
     * : Output format (json, yaml, table)
     * 
     * ## EXAMPLES
     * 
     *     wp studio get 1
     *     wp studio get 1 --format=json
     * 
     * @when after_wp_load
     */
    public function get($args, $assoc_args) {
        $shop_id = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        // Use existing function to get shop data
        $shop_data = fetch_studio_shop_by_id($shop_id);
        
        if (empty($shop_data['shop'])) {
            WP_CLI::error("Shop with ID $shop_id not found.");
        }
        
        $shop = $shop_data['shop'];
        
        // Format output
        if ($format === 'json') {
            WP_CLI::log(json_encode($shop, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $items = [];
            foreach ($shop as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $items[] = [
                    'Field' => $key,
                    'Value' => $value
                ];
            }
            WP_CLI\Utils\format_items($format, $items, ['Field', 'Value']);
        }
    }
    
    /**
     * List all studio shops
     * 
     * ## OPTIONS
     * 
     * [--format=<format>]
     * : Output format (json, yaml, table, csv)
     * 
     * [--fields=<fields>]
     * : Comma-separated list of fields to display
     * 
     * ## EXAMPLES
     * 
     *     wp studio list
     *     wp studio list --format=json
     *     wp studio list --fields=id,name,address
     * 
     * @when after_wp_load
     */
    public function list($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        $fields = isset($assoc_args['fields']) ? explode(',', $assoc_args['fields']) : ['id', 'name', 'nearest_station', 'address'];
        
        // Get all shops using cached data
        $data = get_cached_studio_data();
        
        if (empty($data['shops'])) {
            WP_CLI::warning("No shops found.");
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($data['shops'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            WP_CLI\Utils\format_items($format, $data['shops'], $fields);
        }
    }
    
    /**
     * Get data from command arguments
     */
    private function get_data_from_args($assoc_args) {
        if (isset($assoc_args['json'])) {
            $data = json_decode($assoc_args['json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error('json_error', 'Invalid JSON: ' . json_last_error_msg());
            }
            return $data;
        }
        
        if (isset($assoc_args['file'])) {
            if (!file_exists($assoc_args['file'])) {
                return new WP_Error('file_error', 'File not found: ' . $assoc_args['file']);
            }
            
            $json_content = file_get_contents($assoc_args['file']);
            $data = json_decode($json_content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error('json_error', 'Invalid JSON in file: ' . json_last_error_msg());
            }
            
            return $data;
        }
        
        return new WP_Error('no_data', 'Please provide shop data using --json or --file');
    }
    
    /**
     * Send data to API
     */
    private function send_to_api($data, $is_update = false) {
        $api_url = 'https://678photo.com/api/add_studio_shop.php';
        
        // Add update flag
        $data['update_mode'] = $is_update ? 'on' : 'off';
        
        // Handle local image files first (highest priority)
        if (!empty($data['image_files'])) {
            $data['gallery_images_flat'] = $data['image_files'];
            unset($data['image_files']);
        }
        // Handle image URLs if no local files provided
        elseif (!empty($data['image_urls']) && is_array($data['image_urls'])) {
            // Convert URLs to base64 if needed
            $data['gallery_images_flat'] = $this->convert_images_to_base64($data['image_urls']);
            unset($data['image_urls']);
        }
        
        // Handle category images from local files first
        if (!empty($data['category_image_files'])) {
            foreach ($data['category_image_files'] as $category => $images) {
                $data['category_name'][] = $category;
                $data['gallery_images'][$category] = $images;
            }
            unset($data['category_image_files']);
        }
        // Handle category images from URLs if no local files
        elseif (!empty($data['category_images']) && is_array($data['category_images'])) {
            foreach ($data['category_images'] as $category => $urls) {
                $data['category_name'][] = $category;
                $data['gallery_images'][$category] = $this->convert_images_to_base64($urls);
            }
            unset($data['category_images']);
        }
        
        // Send to API
        $response = wp_remote_post($api_url, [
            'body' => $data,
            'timeout' => 30,
            'sslverify' => false
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('api_error', 'Invalid API response');
        }
        
        if (!empty($result['error'])) {
            return new WP_Error('api_error', $result['error']);
        }
        
        // Clear cache after successful update
        delete_transient('studio_shops_data');
        
        return $result;
    }
    
    /**
     * Convert image URLs to base64
     */
    private function convert_images_to_base64($urls) {
        $base64_images = [];
        
        foreach ($urls as $url) {
            // If already base64, skip conversion
            if (strpos($url, 'data:image') === 0) {
                $base64_images[] = $url;
                continue;
            }
            
            // Download and convert to base64
            $response = wp_remote_get($url);
            if (!is_wp_error($response)) {
                $image_data = wp_remote_retrieve_body($response);
                $mime_type = wp_remote_retrieve_header($response, 'content-type');
                $base64_images[] = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
            }
        }
        
        return $base64_images;
    }
    
    /**
     * Load images from local directory
     */
    private function load_images_from_directory($directory) {
        $images = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!is_dir($directory)) {
            return $images;
        }
        
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $directory . '/' . $file;
            if (!is_file($file_path)) {
                continue;
            }
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed_extensions)) {
                continue;
            }
            
            // Read file and convert to base64
            $image_data = file_get_contents($file_path);
            $mime_type = mime_content_type($file_path);
            
            if ($image_data !== false) {
                $base64_image = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
                $images[] = $base64_image;
                
                // Log file info
                $size_kb = round(filesize($file_path) / 1024, 2);
                WP_CLI::log("  - $file ($size_kb KB)");
            }
        }
        
        return $images;
    }
}

// Register commands
WP_CLI::add_command('studio', 'Studio_Shop_CLI_Commands');

// Add example JSON template command
WP_CLI::add_command('studio example', function() {
    $example = [
        "name" => "えがお写真館 渋谷店",
        "address" => "〒150-0001 東京都渋谷区神宮前1-2-3 ABCビル4F",
        "phone" => "03-1234-5678",
        "nearest_station" => "JR渋谷駅より徒歩5分",
        "business_hours" => "10:00～19:00",
        "holidays" => "水曜日",
        "map_url" => "<iframe src='https://maps.google.com/...'></iframe>",
        "company_email" => "shibuya@example.com",
        "image_urls" => [
            "https://example.com/image1.jpg",
            "https://example.com/image2.jpg"
        ],
        "category_images" => {
            "interior" => [
                "https://example.com/interior1.jpg",
                "https://example.com/interior2.jpg"
            ],
            "staff" => [
                "https://example.com/staff1.jpg"
            ]
        }
    ];
    
    WP_CLI::log("Example JSON for shop data:");
    WP_CLI::log(json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    WP_CLI::log("\nSave this to a file and use: wp studio update 1 --file=shop-data.json");
});