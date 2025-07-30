<?php
/*
Plugin Name: Standalone Inquiry to AWS Email
Description: A standalone plugin to handle inquiry and reservation forms with dynamic shop selection and send emails via AWS SES using API-provided emails.
Version: 1.0.61
Author: Your Name
*/

// Prevent direct access and multiple inclusions
if (!defined('ABSPATH') || defined('SIAES_PLUGIN_LOADED')) {
    exit;
}
define('SIAES_PLUGIN_LOADED', true);

// Debug logging function
function siaes_debug_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[SIAES DEBUG ' . date('Y-m-d H:i:s') . '] ' . (is_array($message) ? print_r($message, true) : $message));
    }
}

// Register settings page
function siaes_register_settings() {
    add_options_page(
        'Standalone Inquiry Email',
        'Standalone Inquiry Email',
        'manage_options',
        'siaes-settings',
        'siaes_settings_page'
    );
}
add_action('admin_menu', 'siaes_register_settings');

// Settings page HTML
function siaes_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Target pages
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    if (!$target_pages[0]) $target_pages = [];

    // Page-wise settings
    $page_settings = get_option('siaes_page_settings', []);

    // Field mapping (slug => fields)
    $default_fields = [
        'name', 'kana', 'contact', 'email', 'notes', 'agreement', 'shop-id', 'reservation_date', 'reservation_time_from'
    ];
    $page_fields_map = [];
    foreach ($target_pages as $slug) {
        $page_fields_map[$slug] = $default_fields;
    }
    ?>
    <div class="wrap">
        <h1>Standalone Inquiry Email Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('siaes_settings_group');
            do_settings_sections('siaes-settings');
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="siaes_aws_access_key_id">AWS Access Key ID</label></th>
                    <td><input type="text" name="siaes_aws_access_key_id" value="<?php echo esc_attr(get_option('siaes_aws_access_key_id')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_aws_secret_access_key">AWS Secret Access Key</label></th>
                    <td><input type="password" name="siaes_aws_secret_access_key" value="<?php echo esc_attr(get_option('siaes_aws_secret_access_key')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_aws_region">AWS Region</label></th>
                    <td><input type="text" name="siaes_aws_region" value="<?php echo esc_attr(get_option('siaes_aws_region', 'ap-northeast-1')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_pages">Target Pages (comma-separated slugs)</label></th>
                    <td><input type="text" name="siaes_pages" value="<?php echo esc_attr(get_option('siaes_pages')); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>Page-wise Email Settings</h2>
            <div id="siaes-page-tabs">
                <ul>
                    <?php foreach ($target_pages as $slug): ?>
                        <li><a href="#tab-<?php echo esc_attr($slug); ?>"><?php echo esc_html($slug); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php foreach ($target_pages as $slug): 
                    $fields = $page_fields_map[$slug];
                    $settings = isset($page_settings[$slug]) ? $page_settings[$slug] : [];
                ?>
                <div id="tab-<?php echo esc_attr($slug); ?>">
                    <h3>Fields for <code><?php echo esc_html($slug); ?></code></h3>
                    <div style="margin-bottom:10px;">
                        <?php foreach ($fields as $field): ?>
                            <span style="display:inline-block;background:#f3f3f3;border:1px solid #ccc;padding:2px 8px;margin:2px;border-radius:4px;">
                                [<?php echo esc_html($field); ?>]
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th><label>Company Email Subject</label></th>
                            <td>
                                <input type="text" name="siaes_page_settings[<?php echo esc_attr($slug); ?>][company_subject]" value="<?php echo esc_attr($settings['company_subject'] ?? ''); ?>" class="regular-text">
                                <p class="description">Use fields like <code>[name]</code>, <code>[email]</code>, <code>[company-name]</code>, etc.</p>
                            </td>
                        </tr>
                       
                        <tr>
                            <th><label>Email Format (Company)</label></th>
                            <td>
                                <textarea name="siaes_page_settings[<?php echo esc_attr($slug); ?>][email_format]" rows="4" class="large-text"><?php echo esc_textarea($settings['email_format'] ?? ''); ?></textarea>
                                <p class="description">Use fields above like <code>[name]</code>, <code>[email]</code>, etc.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label>User Email Subject</label></th>
                            <td>
                                <input type="text" name="siaes_page_settings[<?php echo esc_attr($slug); ?>][user_subject]" value="<?php echo esc_attr($settings['user_subject'] ?? ''); ?>" class="regular-text">
                                <p class="description">Use fields like <code>[name]</code>, <code>[email]</code>, <code>[company-name]</code>, <code>[reservation_date]</code>, etc.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label>User Reply Message</label></th>
                            <td>
                                <textarea name="siaes_page_settings[<?php echo esc_attr($slug); ?>][user_reply]" rows="3" class="large-text"><?php echo esc_textarea($settings['user_reply'] ?? ''); ?></textarea>
                                <p class="description">Exactly what you write here will be sent to the user.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($){
        $("#siaes-page-tabs").tabs();
    });
    </script>
    <?php
}

// Register settings fields
function siaes_register_settings_fields() {
    register_setting('siaes_settings_group', 'siaes_aws_access_key_id', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_aws_secret_access_key', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_aws_region', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_pages', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_page_settings');
}
add_action('admin_init', 'siaes_register_settings_fields');

// Enqueue assets
function siaes_enqueue_assets() {
    if (is_admin()) {
        siaes_debug_log('Skipping asset enqueuing: In admin area');
        return;
    }

    $current_page_id = get_the_ID();
    if (!$current_page_id) {
        siaes_debug_log('Skipping asset enqueuing: No page ID available');
        return;
    }

    $version = time();
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    $current_page_slug = get_post_field('post_name', $current_page_id);

    siaes_debug_log("Target pages: " . print_r($target_pages, true));
    siaes_debug_log("Current page ID: $current_page_id, Slug: $current_page_slug");
    siaes_debug_log("Is user logged in: " . (is_user_logged_in() ? 'Yes' : 'No'));

    if (in_array($current_page_slug, $target_pages)) {
        siaes_debug_log("Enqueuing assets for page ID: $current_page_id, Slug: $current_page_slug");

        wp_enqueue_script(
            'siaes-form-handler',
            plugin_dir_url(__FILE__) . 'assets/js/form-handler.js',
            ['jquery'],
            $version,
            true
        );

        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('siaes_form_nonce');
        wp_localize_script('siaes-form-handler', 'siaes_ajax', [
            'ajax_url' => $ajax_url,
            'nonce' => $nonce,
            'page_id' => $current_page_id,
            'api_url' => 'https://678photo.com/api/get_all_studio_shop.php',
            'is_user_logged_in' => is_user_logged_in() ? 1 : 0
        ]);

        siaes_debug_log("AJAX URL set to: $ajax_url");
        siaes_debug_log("Nonce created: $nonce");
    } else {
        siaes_debug_log("Not enqueuing assets: Current page slug '$current_page_slug' not in target pages");
    }
}
add_action('wp_enqueue_scripts', 'siaes_enqueue_assets');

// Handle form submission
function siaes_handle_form_submission() {
    siaes_debug_log('=== AJAX handler triggered ===');
    siaes_debug_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
    siaes_debug_log('Content type: ' . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set'));
    siaes_debug_log('User agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 100) : 'Unknown'));
    siaes_debug_log('Referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Unknown'));
    siaes_debug_log('Is user logged in: ' . (is_user_logged_in() ? 'Yes' : 'No'));
    siaes_debug_log('Current user ID: ' . get_current_user_id());
    siaes_debug_log('POST data received: ' . (!empty($_POST) ? 'Yes' : 'No'));
    siaes_debug_log('POST data keys: ' . (!empty($_POST) ? implode(', ', array_keys($_POST)) : 'None'));

    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        siaes_debug_log('ERROR: Not a proper AJAX request');
        wp_send_json_error('Invalid request type');
        return;
    }

    if (!isset($_POST['action']) || $_POST['action'] !== 'siaes_submit_form') {
        siaes_debug_log('ERROR: Invalid or missing action. Received: ' . (isset($_POST['action']) ? $_POST['action'] : 'None'));
        wp_send_json_error('Invalid action');
        return;
    }

    if (!isset($_POST['nonce'])) {
        siaes_debug_log('ERROR: No nonce provided in request');
        siaes_debug_log('POST data: ' . print_r($_POST, true));
        wp_send_json_error('Security check failed: No nonce provided');
        return;
    }

    siaes_debug_log('Received nonce: ' . $_POST['nonce']);
    if (!wp_verify_nonce($_POST['nonce'], 'siaes_form_nonce')) {
        siaes_debug_log('ERROR: Nonce verification failed for nonce: ' . $_POST['nonce']);
        wp_send_json_error('Security check failed: Invalid nonce');
        return;
    }

    siaes_debug_log('✅ Nonce verification passed');

    $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
    if ($page_id <= 0) {
        siaes_debug_log('ERROR: Invalid page ID: ' . $page_id);
        wp_send_json_error('Invalid page ID');
        return;
    }

    $page_slug = get_post_field('post_name', $page_id);
    if (!$page_slug) {
        siaes_debug_log('ERROR: Could not get page slug for ID: ' . $page_id);
        wp_send_json_error('Invalid page');
        return;
    }

    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    siaes_debug_log("Form submission for page ID: $page_id, Slug: $page_slug");
    siaes_debug_log("Target pages: " . print_r($target_pages, true));

    if (in_array($page_slug, $target_pages)) {
        $form_data = array();
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['action', 'page_id', 'nonce', 'submit'])) {
                $form_data[$key] = sanitize_text_field($value);
            }
        }

        siaes_debug_log("Sanitized form data: " . json_encode($form_data));

        if (empty($form_data)) {
            siaes_debug_log('ERROR: No form data received');
            wp_send_json_error('No form data received');
            return;
        }

        if (!isset($form_data['shop-id']) && !isset($form_data['store'])) {
            siaes_debug_log('ERROR: No shop-id or store provided in form data');
            wp_send_json_error('Shop selection required');
            return;
        }

        if (!isset($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            siaes_debug_log('ERROR: Invalid or no email provided in form data');
            wp_send_json_error('Valid email address required');
            return;
        }

        ob_start();
        try {
            siaes_send_emails($form_data, $page_slug);
            $output = ob_get_clean();
            siaes_debug_log('✅ Emails sent successfully');
            wp_send_json_success('Form submitted successfully!' . ($output ? ' | Output: ' . $output : ''));
        } catch (Exception $e) {
            $output = ob_get_clean();
            siaes_debug_log('❌ Error in siaes_send_emails: ' . $e->getMessage());
            siaes_debug_log('❌ Error trace: ' . $e->getTraceAsString());
            wp_send_json_error('Server error: ' . $e->getMessage() . ($output ? ' | Output: ' . $output : ''));
        }
    } else {
        siaes_debug_log("❌ Invalid page. Page slug: $page_slug not in target pages");
        wp_send_json_error('Invalid page: ' . $page_slug);
    }
}
add_action('wp_ajax_siaes_submit_form', 'siaes_handle_form_submission');
add_action('wp_ajax_nopriv_siaes_submit_form', 'siaes_handle_form_submission');

// Handle nonce refresh
function siaes_get_fresh_nonce() {
    siaes_debug_log('=== siaes_get_fresh_nonce triggered ===');
    siaes_debug_log('POST data: ' . print_r($_POST, true));
    siaes_debug_log('User agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 100) : 'Unknown'));
    siaes_debug_log('Referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Unknown'));

    if (!isset($_POST['page_id'])) {
        siaes_debug_log('ERROR: Page ID required for nonce refresh');
        wp_send_json_error('Page ID required');
        return;
    }

    $page_id = intval($_POST['page_id']);
    siaes_debug_log('Generating fresh nonce for page ID: ' . $page_id);
    $new_nonce = wp_create_nonce('siaes_form_nonce');
    siaes_debug_log('Generated nonce: ' . $new_nonce);

    wp_send_json_success([
        'nonce' => $new_nonce
    ]);
}
add_action('wp_ajax_siaes_get_fresh_nonce', 'siaes_get_fresh_nonce');
add_action('wp_ajax_nopriv_siaes_get_fresh_nonce', 'siaes_get_fresh_nonce');

// Test AJAX endpoint
function siaes_test_ajax() {
    siaes_debug_log('Test AJAX endpoint called');
    siaes_debug_log('POST data: ' . print_r($_POST, true));

    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        siaes_debug_log('WARNING: Not a proper AJAX request');
    }

    if (!isset($_POST['action']) || $_POST['action'] !== 'siaes_test') {
        siaes_debug_log('ERROR: Invalid action in test endpoint');
        wp_send_json_error('Invalid action');
        return;
    }

    wp_send_json_success([
        'message' => 'AJAX is working correctly!',
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'is_logged_in' => is_user_logged_in()
    ]);
}
add_action('wp_ajax_siaes_test', 'siaes_test_ajax');
add_action('wp_ajax_nopriv_siaes_test', 'siaes_test_ajax');

// Send emails via AWS SES
function siaes_send_emails($form_data, $page_slug) {
    siaes_debug_log("Sending emails for page slug: $page_slug");
    $shop_id = isset($form_data['shop-id']) ? intval($form_data['shop-id']) : (isset($form_data['store']) ? intval($form_data['store']) : 0);
    siaes_debug_log("Shop ID received (shop-id or store): $shop_id");

    $company_email = '';
    $company_name = '';
    $company_phone = '';
    $company_address = '';
    $company_hours = '';
    $fixed_source_email = 'info@678photo.com';

    // Get shop data from local API function
    $api_helper_path = WP_PLUGIN_DIR . '/studio-shops-manager/includes/api-helper.php';
    if (file_exists($api_helper_path)) {
        include_once $api_helper_path;
        if (function_exists('get_all_studio_shops')) {
            $shops_data = get_all_studio_shops([]);
            siaes_debug_log("Shop data retrieved from local function");
            if (isset($shops_data['shops']) && is_array($shops_data['shops'])) {
                foreach ($shops_data['shops'] as $shop) {
                    if ($shop['id'] == $shop_id) {
                        $company_email = $shop['company_email'];
                        $company_name = $shop['name'];
                        $company_phone = $shop['phone'];
                        $company_address = $shop['address'];
                        $company_hours = $shop['business_hours'];

                        // Add to $form_data for shortcode replacement
                        $form_data['company_name'] = $company_name;
                        $form_data['company_phone'] = $company_phone;
                        $form_data['company_address'] = $company_address;
                        $form_data['company_hours'] = $company_hours;
                        $form_data['company_email'] = $company_email;
                        siaes_debug_log("Found shop for ID $shop_id: Email=$company_email, Name=$company_name");
                        break;
                    }
                }
            }
        }
    }

    if (empty($company_email)) {
        siaes_debug_log("No company email found for shop ID: $shop_id. Using fallback.");
        $company_email = get_option('siaes_fallback_email', 'info@san-developer.com');
        $company_name = get_option('siaes_company_name', 'KOKENSHA');
    }

    // Validate page slug
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    if (!in_array($page_slug, $target_pages)) {
        siaes_debug_log("ERROR: Invalid page slug '$page_slug' in siaes_send_emails");
        throw new Exception("Invalid page slug: $page_slug");
    }

    // Page-wise settings
    $page_settings = get_option('siaes_page_settings', []);
    if (!is_array($page_settings)) {
        siaes_debug_log("WARNING: siaes_page_settings is not an array. Initializing as empty array.");
        $page_settings = [];
    }
    $settings = isset($page_settings[$page_slug]) ? $page_settings[$page_slug] : [];
    siaes_debug_log("Settings for page slug '$page_slug': " . json_encode($settings));

    // Company email subject, user email subject, format, user reply
    $company_subject = !empty($settings['company_subject']) ? $settings['company_subject'] : 'New Inquiry from ' . $page_slug;
    $user_subject = !empty($settings['user_subject']) ? $settings['user_subject'] : 'Thank You for Your Inquiry';
    $company_message = $settings['email_format'] ?? '';
    $user_reply_final = $settings['user_reply'] ?? '';

    siaes_debug_log("Before shortcode replacement - Company subject: $company_subject");
    siaes_debug_log("Before shortcode replacement - User subject: $user_subject");
    siaes_debug_log("Form data for shortcode replacement: " . json_encode($form_data));

    // Replace shortcodes in subjects and company message
    foreach ($form_data as $key => $value) {
        $company_subject = str_replace("[$key]", $value, $company_subject);
        $user_subject = str_replace("[$key]", $value, $user_subject);
        $company_message = str_replace("[$key]", $value, $company_message);
        $user_reply_final = str_replace("[$key]", $value, $user_reply_final);
    }
    $company_subject = str_replace('[company-name]', $company_name, $company_subject);
    $user_subject = str_replace('[company-name]', $company_name, $user_subject);
    $company_message = str_replace('[company-name]', $company_name, $company_message);
    $user_reply_final = str_replace('[company-name]', $company_name, $user_reply_final);

    // Log after replacement
    siaes_debug_log("After shortcode replacement - Company subject: $company_subject");
    siaes_debug_log("After shortcode replacement - User subject: $user_subject");

    // Fallback if subjects are empty
    if (empty($company_subject)) {
        siaes_debug_log("ERROR: Company subject is empty after processing. Using fallback.");
        $company_subject = 'New Inquiry';
    }
    if (empty($user_subject)) {
        siaes_debug_log("ERROR: User subject is empty after processing. Using fallback.");
        $user_subject = 'Thank You for Your Inquiry';
    }

    // User reply message
    $thank_you_message = $user_reply_final;
    $append_form_data = false; // Set to true if you want to append form data
    if ($append_form_data) {
        $thank_you_message .= "\n\nSubmitted Data:\n";
        foreach ($form_data as $key => $value) {
            $thank_you_message .= "$key: $value\n";
        }
    }
    
    $autoload_path = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    siaes_debug_log('Autoload path: ' . $autoload_path);
    if (!file_exists($autoload_path)) {
        siaes_debug_log('ERROR: AWS SDK autoload.php not found');
        throw new Exception('AWS SDK not found');
    }
    require_once $autoload_path;

    try {
        $ses_client = new Aws\Ses\SesClient([
            'version' => 'latest',
            'region' => get_option('siaes_aws_region', 'ap-northeast-1'),
            'credentials' => [
                'key' => get_option('siaes_aws_access_key_id'),
                'secret' => get_option('siaes_aws_secret_access_key'),
            ],
        ]);

        // Send email to company
        siaes_debug_log('🔄 Attempting to send company email to: ' . $company_email . ' from: ' . $fixed_source_email);
        siaes_debug_log('📧 Company email subject: ' . $company_subject . ' (' . $page_slug . ')');
        siaes_debug_log('📝 Company email body preview: ' . substr($company_message, 0, 200) . '...');

        $result = $ses_client->sendEmail([
            'Source' => $fixed_source_email,
            'Destination' => ['ToAddresses' => [$company_email]],
            'Message' => [
                'Subject' => ['Data' => $company_subject . ' (' . $page_slug . ')', 'Charset' => 'UTF-8'],
                'Body' => ['Text' => ['Data' => $company_message, 'Charset' => 'UTF-8']],
            ],
        ]);
        siaes_debug_log('✅ Company email sent successfully. MessageId: ' . $result['MessageId']);

        // Send thank-you email to user if email is provided
        if (!empty($form_data['email']) && filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            siaes_debug_log('🔄 Attempting to send thank-you email to: ' . $form_data['email'] . ' from: ' . $fixed_source_email);
            siaes_debug_log('📧 User email subject: ' . $user_subject);
            siaes_debug_log('📝 User email body preview: ' . substr($thank_you_message, 0, 200) . '...');

            $user_result = $ses_client->sendEmail([
                'Source' => $fixed_source_email,
                'Destination' => ['ToAddresses' => [$form_data['email']]],
                'Message' => [
                    'Subject' => ['Data' => $user_subject, 'Charset' => 'UTF-8'],
                    'Body' => ['Text' => ['Data' => $thank_you_message, 'Charset' => 'UTF-8']],
                ],
            ]);
            siaes_debug_log('✅ Thank-you email sent successfully. MessageId: ' . $user_result['MessageId']);
        } else {
            siaes_debug_log('⚠️ Invalid or no user email provided: ' . ($form_data['email'] ?? 'Not set'));
        }
    } catch (Exception $e) {
        siaes_debug_log('❌ Failed to send email: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
        throw new Exception('Failed to send email: ' . $e->getMessage());
    }
}

// Admin notices for debugging
function siaes_admin_notices() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $target_pages = get_option('siaes_pages', '');
        $page_settings = get_option('siaes_page_settings', []);
        if (empty($target_pages)) {
            echo '<div class="notice notice-warning"><p><strong>SIAES Plugin:</strong> No target pages configured. Please configure form pages in the settings.</p></div>';
        }
        foreach (array_map('trim', explode(',', $target_pages)) as $slug) {
            if (empty($page_settings[$slug]['company_subject'])) {
                echo '<div class="notice notice-warning"><p><strong>SIAES Plugin:</strong> No company email subject configured for page slug: ' . esc_html($slug) . '</p></div>';
            }
            if (empty($page_settings[$slug]['user_subject'])) {
                echo '<div class="notice notice-warning"><p><strong>SIAES Plugin:</strong> No user email subject configured for page slug: ' . esc_html($slug) . '</p></div>';
            }
        }
        $aws_key = get_option('siaes_aws_access_key_id');
        if (empty($aws_key)) {
            echo '<div class="notice notice-warning"><p><strong>SIAES Plugin:</strong> AWS credentials not configured. Please configure AWS settings.</p></div>';
        }
        echo '<div class="notice notice-info"><p><strong>SIAES Debug:</strong> AJAX URL: ' . admin_url('admin-ajax.php') . '</p></div>';
    }
}
add_action('admin_notices', 'siaes_admin_notices');

// Debug registered actions
function siaes_debug_actions() {
    siaes_debug_log('Registered AJAX actions check:');
    siaes_debug_log('wp_ajax_siaes_submit_form exists: ' . (has_action('wp_ajax_siaes_submit_form') ? 'Yes' : 'No'));
    siaes_debug_log('wp_ajax_nopriv_siaes_submit_form exists: ' . (has_action('wp_ajax_nopriv_siaes_submit_form') ? 'Yes' : 'No'));
    siaes_debug_log('wp_ajax_siaes_test exists: ' . (has_action('wp_ajax_siaes_test') ? 'Yes' : 'No'));
    siaes_debug_log('wp_ajax_nopriv_siaes_test exists: ' . (has_action('wp_ajax_nopriv_siaes_test') ? 'Yes' : 'No'));
    siaes_debug_log('wp_ajax_siaes_get_fresh_nonce exists: ' . (has_action('wp_ajax_siaes_get_fresh_nonce') ? 'Yes' : 'No'));
    siaes_debug_log('wp_ajax_nopriv_siaes_get_fresh_nonce exists: ' . (has_action('wp_ajax_nopriv_siaes_get_fresh_nonce') ? 'Yes' : 'No'));
}
add_action('wp_loaded', 'siaes_debug_actions');

// Test SES credentials
function siaes_test_ses_credentials() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }

    $autoload_path = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    siaes_debug_log('Autoload path: ' . $autoload_path);
    if (!file_exists($autoload_path)) {
        siaes_debug_log('ERROR: AWS SDK autoload.php not found');
        wp_send_json_error('AWS SDK not found');
        return;
    }
    require_once $autoload_path;

    try {
        $ses_client = new Aws\Ses\SesClient([
            'version' => 'latest',
            'region' => get_option('siaes_aws_region', 'ap-northeast-1'),
            'credentials' => [
                'key' => get_option('siaes_aws_access_key_id'),
                'secret' => get_option('siaes_aws_secret_access_key'),
            ],
        ]);

        $result = $ses_client->getSendQuota();
        siaes_debug_log('SES credentials valid. Quota: ' . print_r($result->get('SendQuota'), true));
        wp_send_json_success('SES credentials valid. Quota: ' . print_r($result->get('SendQuota'), true));
    } catch (Exception $e) {
        siaes_debug_log('SES error: ' . $e->getMessage());
        wp_send_json_error('SES error: ' . $e->getMessage());
    }
}
add_action('wp_ajax_siaes_test_ses', 'siaes_test_ses_credentials');

// Test email sending
function siaes_test_email_sending() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    try {
        $form_data = [
            'name' => 'Test User',
            'kana' => 'テストユーザー',
            'contact' => '123-456-7890',
            'email' => 'test@example.com',
            'notes' => 'Test notes',
            'shop-id' => 1,
            'reservation_date' => '2025-08-01',
            'reservation_time_from' => '10:00'
        ];
        $page_slug = 'test-page';
        siaes_send_emails($form_data, $page_slug);
        wp_send_json_success('Test email sent successfully');
    } catch (Exception $e) {
        siaes_debug_log('Test email error: ' . $e->getMessage());
        wp_send_json_error('Test email failed: ' . $e->getMessage());
    }
}
add_action('wp_ajax_siaes_test_email', 'siaes_test_email_sending');
?>
