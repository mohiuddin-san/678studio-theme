<?php
/*
Plugin Name: Standalone Inquiry to AWS Email
Description: A standalone plugin to handle inquiry and reservation forms with dynamic shop selection and send emails via AWS SES using API-provided emails.
Version: 1.0.56
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
        error_log('[SIAES DEBUG] ' . (is_array($message) ? print_r($message, true) : $message));
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
                <tr>
                    <th><label for="siaes_email_format">Email Format</label></th>
                    <td><textarea name="siaes_email_format" rows="5" class="large-text"><?php echo esc_textarea(get_option('siaes_email_format', 'New inquiry from [name] for [company-name]')); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="siaes_company_subject">Company Email Subject</label></th>
                    <td><input type="text" name="siaes_company_subject" value="<?php echo esc_attr(get_option('siaes_company_subject', 'New Inquiry from [company-name]')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_company_name">Default Company Name</label></th>
                    <td><input type="text" name="siaes_company_name" value="<?php echo esc_attr(get_option('siaes_company_name', 'KOKENSHA')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_fallback_email">Fallback Email</label></th>
                    <td><input type="text" name="siaes_fallback_email" value="<?php echo esc_attr(get_option('siaes_fallback_email', 'info@san-developer.com')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="siaes_user_reply">User Reply Message</label></th>
                    <td><textarea name="siaes_user_reply" rows="5" class="large-text"><?php echo esc_textarea(get_option('siaes_user_reply', 'Thank you for your inquiry!')); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="siaes_append_form_data">Append Form Data</label></th>
                    <td><input type="checkbox" name="siaes_append_form_data" value="1" <?php checked(get_option('siaes_append_form_data', 1)); ?>></td>
                </tr>
                <tr>
                    <th><label for="siaes_language">Language</label></th>
                    <td>
                        <select name="siaes_language">
                            <option value="english" <?php selected(get_option('siaes_language', 'english'), 'english'); ?>>English</option>
                            <option value="japanese" <?php selected(get_option('siaes_language', 'english'), 'japanese'); ?>>Japanese</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings fields
function siaes_register_settings_fields() {
    register_setting('siaes_settings_group', 'siaes_aws_access_key_id', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_aws_secret_access_key', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_aws_region', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_pages', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_email_format', 'sanitize_textarea_field');
    register_setting('siaes_settings_group', 'siaes_company_subject', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_company_name', 'sanitize_text_field');
    register_setting('siaes_settings_group', 'siaes_fallback_email', 'sanitize_email');
    register_setting('siaes_settings_group', 'siaes_user_reply', 'sanitize_textarea_field');
    register_setting('siaes_settings_group', 'siaes_append_form_data', 'intval');
    register_setting('siaes_settings_group', 'siaes_language', 'sanitize_text_field');
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
    $fixed_source_email = 'info@678photo.com'; // Fixed source email for all SES emails

    // Get shop data from local API function instead of HTTP request
    siaes_debug_log("Getting shop data from local API function for shop ID: $shop_id");
    
    // Include the API helper file
    $api_helper_path = WP_PLUGIN_DIR . '/studio-shops-manager/includes/api-helper.php';
    if (file_exists($api_helper_path)) {
        include_once $api_helper_path;
        
        // Call the function directly if it exists
        if (function_exists('get_all_studio_shops')) {
            $shops_data = get_all_studio_shops([]);
            siaes_debug_log("Shop data retrieved from local function");
            
            if (isset($shops_data['shops']) && is_array($shops_data['shops'])) {
                foreach ($shops_data['shops'] as $shop) {
                    if ($shop['id'] == $shop_id) {
                        $company_email = $shop['company_email'];
                        $company_name = $shop['name'];
                        siaes_debug_log("Found shop for ID $shop_id: Email=$company_email, Name=$company_name");
                        break;
                    }
                }
            }
        } else {
            siaes_debug_log("get_all_studio_shops function not found");
        }
    }
    
    // Fallback if no email found
    if (empty($company_email)) {
        siaes_debug_log("No company email found for shop ID: $shop_id. Using fallback.");
        $company_email = get_option('siaes_fallback_email', 'info@san-developer.com');
        $company_name = get_option('siaes_company_name', 'KOKENSHA');
    }

    siaes_debug_log("Using company email (destination): $company_email, company name: $company_name");
    siaes_debug_log("Using fixed source email: $fixed_source_email");

    $email_format = trim(get_option('siaes_email_format', 'New [form_type] from [name] for [company-name]'));
    $company_subject = get_option('siaes_company_subject', 'New [form_type] from [company-name]');
    $default_company_name = get_option('siaes_company_name', 'KOKENSHA');
    $append_form_data = get_option('siaes_append_form_data', 1);
    $language = get_option('siaes_language', 'english');

    $form_type = ($page_slug === 'studio-reservation') ? 'reservation' : 'inquiry';
    $email_format = str_replace('[form_type]', $form_type, $email_format);
    $company_subject = str_replace('[form_type]', $form_type, $company_subject);

    $labels = [
        'english' => [
            'form_data_label' => '==== Form Data ====',
            'thank_you_subject' => 'Thank You for Your ' . ucfirst($form_type),
            'contact_us' => 'We will get back to you soon. Contact us at %s for further assistance.'
        ],
        'japanese' => [
            'form_data_label' => '==== フォームデータ ====',
            'thank_you_subject' => ($form_type === 'reservation') ? 'ご予約ありがとうございます' : 'お問い合わせありがとうございます',
            'contact_us' => '近日中に対応いたします。さらにサポートが必要な場合は、%sまでご連絡ください。'
        ]
    ];

    $company_subject = str_replace('[company-name]', $company_name ?: $default_company_name, $company_subject);
    $company_message = $email_format;
    $company_message = str_replace('[company-name]', $company_name ?: $default_company_name, $company_message);
    $company_message = str_replace('[company-email]', $company_email, $company_message);

    foreach ($form_data as $key => $value) {
        $company_message = str_replace("[$key]", $value, $company_message);
    }

    if ($append_form_data) {
        $company_message .= "\n\n" . $labels[$language]['form_data_label'] . "\n";
        foreach ($form_data as $key => $value) {
            $company_message .= "$key: $value\n";
        }
    }

    $user_reply_raw = get_option('siaes_user_reply', 'Thank you for your [form_type]!');
    $user_reply_final = str_replace('[form_type]', $form_type, $user_reply_raw);
    foreach ($form_data as $key => $value) {
        $user_reply_final = str_replace("[$key]", $value, $user_reply_final);
    }

    $thank_you_message = $user_reply_final;
    if ($append_form_data) {
        $thank_you_message .= "\n\n" . $labels[$language]['form_data_label'] . "\n";
        foreach ($form_data as $key => $value) {
            $thank_you_message .= "$key: $value\n";
        }
    }
    $thank_you_message .= "\n" . sprintf($labels[$language]['contact_us'], $fixed_source_email);

    $autoload_path = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    siaes_debug_log('Autoload path: ' . $autoload_path);
    if (!file_exists($autoload_path)) {
        siaes_debug_log('ERROR: AWS SDK autoload.php not found');
        throw new Exception('AWS SDK not found');
    }
    if (!is_readable($autoload_path)) {
        siaes_debug_log('ERROR: AWS SDK autoload.php not readable');
        throw new Exception('AWS SDK autoload.php not readable');
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
            'Source' => $fixed_source_email, // Use fixed source email
            'Destination' => ['ToAddresses' => [$company_email]], // Dynamic company email as destination
            'Message' => [
                'Subject' => ['Data' => $company_subject . ' (' . $page_slug . ')', 'Charset' => 'UTF-8'],
                'Body' => ['Text' => ['Data' => $company_message, 'Charset' => 'UTF-8']],
            ],
        ]);
        siaes_debug_log('✅ Company email sent successfully. MessageId: ' . $result['MessageId']);

        // Send thank-you email to user if email is provided
        if (!empty($form_data['email'])) {
            siaes_debug_log('🔄 Attempting to send thank-you email to: ' . $form_data['email'] . ' from: ' . $fixed_source_email);
            
            $user_result = $ses_client->sendEmail([
                'Source' => $fixed_source_email, // Use fixed source email (info@678photo.com)
                'Destination' => ['ToAddresses' => [$form_data['email']]],
                'Message' => [
                    'Subject' => ['Data' => $labels[$language]['thank_you_subject'], 'Charset' => 'UTF-8'],
                    'Body' => ['Text' => ['Data' => $thank_you_message, 'Charset' => 'UTF-8']],
                ],
            ]);
            siaes_debug_log('✅ Thank-you email sent successfully. MessageId: ' . $user_result['MessageId']);
        } else {
            siaes_debug_log('⚠️ No user email provided, skipping thank-you email.');
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
        if (empty($target_pages)) {
            echo '<div class="notice notice-warning"><p><strong>SIAES Plugin:</strong> No target pages configured. Please configure form pages in the settings.</p></div>';
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