<?php
/**
 * WP-CLI Commands for Studio Shop Manager Testing
 */

if (defined('WP_CLI') && WP_CLI) {
    
    class Studio_Shop_CLI {
        
        /**
         * Test Studio Shop Manager functionality
         * 
         * ## EXAMPLES
         *
         *     wp studio-shop test-category-upload
         *     wp studio-shop test-new-category
         *     wp studio-shop list-shops
         *     wp studio-shop test-main-gallery
         *
         * @subcommand test-category-upload
         */
        public function test_category_upload($args, $assoc_args) {
            WP_CLI::line('Testing Category Image Upload...');
            
            // Include API helper
            require_once ABSPATH . 'wp-content/plugins/studio-shops-manager/includes/api-helper.php';
            
            // Create test data
            $test_data = [
                'shop_id' => 1,
                'gallery' => [
                    [
                        'category_name' => 'WP-CLI Test Category',
                        'images' => [
                            $this->create_test_base64_image()
                        ]
                    ]
                ]
            ];
            
            WP_CLI::line('Calling upload_category_images function...');
            WP_CLI::line('Shop ID: ' . $test_data['shop_id']);
            WP_CLI::line('Categories: ' . count($test_data['gallery']));
            WP_CLI::line('Images in first category: ' . count($test_data['gallery'][0]['images']));
            
            // Call the function
            $result = upload_category_images($test_data);
            
            WP_CLI::line('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
            
            if ($result['success']) {
                WP_CLI::success('Category image upload test completed successfully!');
            } else {
                WP_CLI::error('Category image upload test failed: ' . $result['error']);
            }
        }
        
        /**
         * Test new category creation
         * 
         * @subcommand test-new-category
         */
        public function test_new_category($args, $assoc_args) {
            $category_name = isset($assoc_args['name']) ? $assoc_args['name'] : 'New Test Category ' . time();
            $shop_id = isset($assoc_args['shop-id']) ? intval($assoc_args['shop-id']) : 1;
            
            WP_CLI::line('Testing New Category Creation: ' . $category_name);
            WP_CLI::line('Target Shop ID: ' . $shop_id);
            
            // Include API helper
            require_once ABSPATH . 'wp-content/plugins/studio-shops-manager/includes/api-helper.php';
            
            // Create test data for new category
            $test_data = [
                'shop_id' => $shop_id,
                'gallery' => [
                    [
                        'category_name' => $category_name,
                        'images' => [
                            $this->create_test_base64_image(),
                            $this->create_test_base64_image()
                        ]
                    ]
                ]
            ];
            
            WP_CLI::line('Calling upload_category_images function...');
            WP_CLI::line('Shop ID: ' . $test_data['shop_id']);
            WP_CLI::line('New Category: ' . $category_name);
            WP_CLI::line('Images: ' . count($test_data['gallery'][0]['images']));
            
            // Call the function
            $result = upload_category_images($test_data);
            
            WP_CLI::line('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
            
            if ($result['success']) {
                WP_CLI::success('New category creation test completed successfully!');
                WP_CLI::line('Now checking if the category appears in the list...');
                $this->list_shops([], []);
            } else {
                WP_CLI::error('New category creation test failed: ' . $result['error']);
            }
        }
        
        /**
         * Test main gallery image upload
         * 
         * @subcommand test-main-gallery
         */
        public function test_main_gallery($args, $assoc_args) {
            WP_CLI::line('Testing Main Gallery Image Upload...');
            
            // Include API helper
            require_once ABSPATH . 'wp-content/plugins/studio-shops-manager/includes/api-helper.php';
            
            // Create test data
            $test_data = [
                'shop_id' => 1,
                'name' => 'Test Shop Update',
                'address' => 'Test Address for WP-CLI',
                'gallery_images' => [
                    $this->create_test_base64_image()
                ]
            ];
            
            WP_CLI::line('Calling update_studio_shop function...');
            WP_CLI::line('Shop ID: ' . $test_data['shop_id']);
            WP_CLI::line('Gallery images: ' . count($test_data['gallery_images']));
            
            // Call the function
            $result = update_studio_shop($test_data);
            
            WP_CLI::line('Result: ' . json_encode($result, JSON_PRETTY_PRINT));
            
            if ($result['success']) {
                WP_CLI::success('Main gallery image upload test completed successfully!');
            } else {
                WP_CLI::error('Main gallery image upload test failed: ' . $result['error']);
            }
        }
        
        /**
         * List all shops
         * 
         * @subcommand list-shops
         */
        public function list_shops($args, $assoc_args) {
            global $wpdb;
            WP_CLI::line('Listing all shops...');
            
            // Query database directly
            $shops = $wpdb->get_results("
                SELECT s.*, 
                    GROUP_CONCAT(DISTINCT si.image_url) as image_urls
                FROM studio_shops s
                LEFT JOIN studio_shop_images si ON s.id = si.shop_id
                GROUP BY s.id
            ");
            
            if (!$shops) {
                WP_CLI::line('No shops found');
                return;
            }
            
            WP_CLI::line('Found ' . count($shops) . ' shops:');
            
            foreach ($shops as $shop) {
                WP_CLI::line('');
                WP_CLI::line('Shop ID: ' . $shop->id);
                WP_CLI::line('Name: ' . $shop->name);
                WP_CLI::line('Address: ' . $shop->address);
                
                $image_urls = $shop->image_urls ? explode(',', $shop->image_urls) : [];
                WP_CLI::line('Main Gallery Images: ' . count($image_urls));
                
                if (!empty($image_urls)) {
                    WP_CLI::line('  Main Images:');
                    foreach ($image_urls as $url) {
                        WP_CLI::line('    - ' . trim($url));
                    }
                }
                
                // Get category images
                $cat_images = $wpdb->get_results($wpdb->prepare("
                    SELECT c.category_name, ci.image_url
                    FROM studio_shop_catgorie_images ci
                    JOIN studio_shop_categories c ON ci.category_id = c.id
                    WHERE ci.shop_id = %d
                ", $shop->id));
                
                WP_CLI::line('Category Images: ' . count($cat_images));
                
                if (!empty($cat_images)) {
                    WP_CLI::line('  Categories:');
                    $categories = [];
                    foreach ($cat_images as $cat_img) {
                        $categories[$cat_img->category_name][] = $cat_img->image_url;
                    }
                    
                    foreach ($categories as $category => $images) {
                        WP_CLI::line('    ' . $category . ': ' . count($images) . ' images');
                        foreach ($images as $url) {
                            WP_CLI::line('      - ' . $url);
                        }
                    }
                }
            }
            
            WP_CLI::success('Shop listing completed!');
        }
        
        /**
         * Test category image deletion
         * 
         * @subcommand test-delete-category-image
         */
        public function test_delete_category_image($args, $assoc_args) {
            $image_id = isset($assoc_args['image-id']) ? intval($assoc_args['image-id']) : 3; // デフォルトで最後の画像
            
            WP_CLI::line('Testing Category Image Deletion...');
            WP_CLI::line('Image ID to delete: ' . $image_id);
            
            // Include API helper
            require_once ABSPATH . 'wp-content/plugins/studio-shops-manager/includes/api-helper.php';
            
            // Call the deletion function
            $result = delete_category_image(['image_id' => $image_id]);
            
            WP_CLI::line('Deletion Result: ' . json_encode($result, JSON_PRETTY_PRINT));
            
            if ($result['success']) {
                WP_CLI::success('Category image deleted successfully!');
                WP_CLI::line('Now checking the updated shop list...');
                $this->list_shops([], []);
            } else {
                WP_CLI::error('Category image deletion failed: ' . $result['error']);
            }
        }
        
        /**
         * Check database tables
         * 
         * @subcommand check-db
         */
        public function check_db($args, $assoc_args) {
            global $wpdb;
            
            WP_CLI::line('Checking Studio Shop database tables...');
            
            $tables = [
                'studio_shops' => 'Shops',
                'studio_shop_images' => 'Main Gallery Images',
                'studio_shop_categories' => 'Categories',
                'studio_shop_catgorie_images' => 'Category Images'
            ];
            
            foreach ($tables as $table => $description) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
                WP_CLI::line($description . ': ' . $count . ' records');
                
                if ($table === 'studio_shops') {
                    $shops = $wpdb->get_results("SELECT id, name FROM {$table}");
                    foreach ($shops as $shop) {
                        WP_CLI::line('  - Shop ' . $shop->id . ': ' . $shop->name);
                    }
                }
            }
            
            WP_CLI::success('Database check completed!');
        }
        
        /**
         * Create a test base64 image (small PNG)
         */
        private function create_test_base64_image() {
            // Create a simple 10x10 red PNG image
            $width = 10;
            $height = 10;
            
            $image = imagecreate($width, $height);
            $red = imagecolorallocate($image, 255, 0, 0);
            imagefill($image, 0, 0, $red);
            
            ob_start();
            imagepng($image);
            $image_data = ob_get_clean();
            imagedestroy($image);
            
            return 'data:image/png;base64,' . base64_encode($image_data);
        }
    }
    
    WP_CLI::add_command('studio-shop', 'Studio_Shop_CLI');
}
?>