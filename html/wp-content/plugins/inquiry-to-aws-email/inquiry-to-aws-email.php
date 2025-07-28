<?php
/*
Plugin Name: Standalone Inquiry to AWS Email
Description: A standalone plugin to handle inquiry forms with dynamic shop selection and send emails via AWS SES using API-provided emails.
Version: 1.0.50
Author: Your Name
*/

// Register settings page
function siaes_register_settings() {
    add_options_page('Standalone Inquiry Email', 'Standalone Inquiry Email', 'manage_options', 'siaes-settings', 'siaes_settings_page');
}
add_action('admin_menu', 'siaes_register_settings');

// Settings page HTML
function siaes_settings_page() {
    global $wpdb;
    try {
        $pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
        $fields_by_page = array();
        $excluded_fields = ['viewport', 'robots', 'generator', 'action', 'page_id', 'nonce', 'submit', 'shop-id'];

        foreach ($pages as $page_slug) {
            $page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page'", $page_slug));
            if ($page_id) {
                $post = get_post($page_id);
                if ($post) {
                    error_log("Checking page: $page_slug, ID: $page_id, Content: " . substr($post->post_content, 0, 100));
                    $fields = [];
                    $rendered_content = '';

                    // Check if the page uses a specific template
                    $template_slug = get_page_template_slug($page_id);
                    error_log("Template slug for $page_slug (ID $page_id): $template_slug");

                    if ($page_slug === 'studio-inquery') {
                        // Explicitly render the inquery.php template
                        ob_start();
                        get_template_part('template-parts/sections/inquery');
                        $rendered_content = ob_get_clean();
                        error_log("Rendered inquery.php for $page_slug: " . substr($rendered_content, 0, 100));
                    } elseif ($template_slug) {
                        $template_path = get_stylesheet_directory() . '/' . $template_slug;
                        if (file_exists($template_path)) {
                            ob_start();
                            include $template_path;
                            $rendered_content = ob_get_clean();
                            error_log("Successfully rendered template for $page_slug");
                        } else {
                            error_log("Template file not found at: $template_path");
                            $rendered_content = apply_filters('the_content', $post->post_content);
                        }
                    } else {
                        $rendered_content = apply_filters('the_content', $post->post_content);
                        error_log("Fallback to post content for $page_slug");
                    }

                    // Extract form fields
                    preg_match_all('/name=["\']([^"\']+)["\']/i', $rendered_content, $matches);
                    $all_fields = array_unique($matches[1]);
                    foreach ($all_fields as $field) {
                        if (!in_array($field, $excluded_fields)) {
                            $fields[] = $field;
                        }
                    }
                    $fields_by_page[$page_slug] = $fields;
                    error_log("Detected fields for $page_slug: " . json_encode($fields));
                } else {
                    error_log("No post found for page ID: $page_id");
                }
            } else {
                error_log("No page ID found for slug: $page_slug");
            }
        }

        $language = get_option('siaes_language', 'english');
        $labels = [
            'english' => [
                'title' => 'Standalone Inquiry Email Settings',
                'aws_access_key' => 'AWS Access Key ID',
                'aws_secret_key' => 'AWS Secret Access Key',
                'aws_region' => 'AWS Region',
                'form_pages' => 'Form Pages (comma-separated slugs)',
                'email_format' => 'Email Format (Plain Text/HTML)',
                'email_format_help' => 'Format for company email. Use [field-name]: [value] placeholders (e.g., [user-name]: [user-name]) and [company-name] for shop name.',
                'company_subject' => 'Company Email Subject',
                'company_subject_help' => 'Custom subject line for company email. Use [company-name] placeholder.',
                'company_name' => 'Default Company Name',
                'company_name_help' => 'Default name of the company to be used if not selected.',
                'user_reply' => 'User Reply Message',
                'user_reply_help' => 'Custom initial message for user reply email. Use placeholders like [field-name] if needed.',
                'select_fields' => 'Select Fields for Company Email',
                'append_form_data' => 'Append Form Data to Emails',
                'no_fields_detected' => 'No fields detected. Please ensure your form has input or textarea fields with "name" attributes in the page content.',
                'language' => 'Language',
                'submit' => 'Save Changes'
            ],
            'japanese' => [
                'title' => 'スタンドアロンお問い合わせメール設定',
                'aws_access_key' => 'AWSアクセスキーID',
                'aws_secret_key' => 'AWSシークレットアクセスキー',
                'aws_region' => 'AWSリージョン',
                'form_pages' => 'フォームページ（カンマ区切りのスラッグ）',
                'email_format' => 'メール形式（プレーンテキスト/HTML）',
                'email_format_help' => '会社メールの形式。[field-name]: [value] プレースホルダー（例: [user-name]: [user-name]）および[company-name]を会社名に使用します。',
                'company_subject' => '会社メールの件名',
                'company_subject_help' => '会社メールのカスタム件名。[company-name]プレースホルダーを使用してください。',
                'company_name' => 'デフォルト会社名',
                'company_name_help' => '選択されていない場合に使用されるデフォルトの会社名。',
                'user_reply' => 'ユーザー返信メッセージ',
                'user_reply_help' => 'ユーザー返信メールのカスタム初期メッセージ。必要に応じて[field-name]などのプレースホルダーを使用してください。',
                'select_fields' => '会社メールのフィールド選択',
                'append_form_data' => 'メールにフォームデータを追加',
                'no_fields_detected' => 'フィールドが検出されませんでした。「inquery」ページのコンテンツに「name」属性を持つ入力またはテキストエリアフィールドがあることを確認してください。',
                'language' => '言語',
                'submit' => '変更を保存'
            ]
        ];
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($labels[$language]['title']); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('siaes_settings_group'); ?>
                <?php do_settings_sections('siaes_settings_group'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="siaes_language"><?php echo esc_html($labels[$language]['language']); ?></label></th>
                        <td>
                            <select name="siaes_language">
                                <option value="english" <?php selected($language, 'english'); ?>>English</option>
                                <option value="japanese" <?php selected($language, 'japanese'); ?>>日本語</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="siaes_aws_access_key_id"><?php echo esc_html($labels[$language]['aws_access_key']); ?></label></th>
                        <td><input type="text" name="siaes_aws_access_key_id" value="<?php echo esc_attr(get_option('siaes_aws_access_key_id')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="siaes_aws_secret_access_key"><?php echo esc_html($labels[$language]['aws_secret_key']); ?></label></th>
                        <td><input type="password" name="siaes_aws_secret_access_key" value="<?php echo esc_attr(get_option('siaes_aws_secret_access_key')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="siaes_aws_region"><?php echo esc_html($labels[$language]['aws_region']); ?></label></th>
                        <td><input type="text" name="siaes_aws_region" value="<?php echo esc_attr(get_option('siaes_aws_region')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="siaes_pages"><?php echo esc_html($labels[$language]['form_pages']); ?></label></th>
                        <td><input type="text" name="siaes_pages" value="<?php echo esc_attr(get_option('siaes_pages')); ?>" class="regular-text" placeholder="studio-inquery,contact,support"></td>
                    </tr>
                    <tr>
                        <th><label for="siaes_email_format"><?php echo esc_html($labels[$language]['email_format']); ?></label></th>
                        <td><textarea name="siaes_email_format" rows="10" cols="50" class="large-text"><?php echo esc_textarea(get_option('siaes_email_format')); ?></textarea>
                            <p><?php echo esc_html($labels[$language]['email_format_help']); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="siaes_company_subject"><?php echo esc_html($labels[$language]['company_subject']); ?></label></th>
                        <td><input type="text" name="siaes_company_subject" value="<?php echo esc_attr(get_option('siaes_company_subject', 'New Inquiry from [company-name]')); ?>" class="regular-text">
                            <p><?php echo esc_html($labels[$language]['company_subject_help']); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="siaes_company_name"><?php echo esc_html($labels[$language]['company_name']); ?></label></th>
                        <td><input type="text" name="siaes_company_name" value="<?php echo esc_attr(get_option('siaes_company_name', 'KOKENSHA')); ?>" class="regular-text">
                            <p><?php echo esc_html($labels[$language]['company_name_help']); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="siaes_user_reply"><?php echo esc_html($labels[$language]['user_reply']); ?></label></th>
                        <td><textarea name="siaes_user_reply" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('siaes_user_reply', 'Thank you for your inquiry!')); ?></textarea>
                            <p><?php echo esc_html($labels[$language]['user_reply_help']); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="siaes_append_form_data"><?php echo esc_html($labels[$language]['append_form_data']); ?></label></th>
                        <td><input type="checkbox" name="siaes_append_form_data" value="1" <?php checked(get_option('siaes_append_form_data', 1)); ?>></td>
                    </tr>
                    <tr>
                        <th><label><?php echo esc_html($labels[$language]['select_fields']); ?></label></th>
                        <td>
                            <label><input type="checkbox" name="siaes_email_fields[all]" value="all" <?php echo in_array('all', (array)get_option('siaes_email_fields', array())) ? 'checked' : ''; ?>> All</label><br>
                            <?php
                            $selected_fields = (array)get_option('siaes_email_fields', array());
                            $all_fields = [];
                            foreach ($fields_by_page as $page_fields) {
                                $all_fields = array_unique(array_merge($all_fields, $page_fields));
                            }
                            if (!empty($all_fields)) {
                                foreach ($all_fields as $field) {
                                    $checked = in_array($field, $selected_fields) || in_array('all', $selected_fields) ? 'checked' : '';
                                    echo '<label><input type="checkbox" name="siaes_email_fields[' . esc_attr($field) . ']" value="' . esc_attr($field) . '" ' . $checked . '> ' . esc_html($field) . '</label><br>';
                                }
                            } else {
                                echo '<p>' . esc_html($labels[$language]['no_fields_detected']) . '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button($labels[$language]['submit']); ?>
            </form>
        </div>
        <?php
    } catch (Exception $e) {
        error_log("Error in siaes_settings_page: " . $e->getMessage());
        echo '<div class="error"><p>Error rendering settings page: ' . esc_html($e->getMessage()) . '</p></div>';
    }
}

// Register settings
function siaes_register_settings_fields() {
    register_setting('siaes_settings_group', 'siaes_aws_access_key_id');
    register_setting('siaes_settings_group', 'siaes_aws_secret_access_key');
    register_setting('siaes_settings_group', 'siaes_aws_region');
    register_setting('siaes_settings_group', 'siaes_pages');
    register_setting('siaes_settings_group', 'siaes_email_format');
    register_setting('siaes_settings_group', 'siaes_company_subject', array('default' => 'New Inquiry from [company-name]'));
    register_setting('siaes_settings_group', 'siaes_company_name', array('default' => 'KOKENSHA'));
    register_setting('siaes_settings_group', 'siaes_user_reply', array('default' => 'Thank you for your inquiry!'));
    register_setting('siaes_settings_group', 'siaes_email_fields', array('default' => array()));
    register_setting('siaes_settings_group', 'siaes_append_form_data', array('default' => 1));
    register_setting('siaes_settings_group', 'siaes_language', array('default' => 'english'));
}
add_action('admin_init', 'siaes_register_settings_fields');

// Enqueue JS for custom form
function siaes_enqueue_assets() {
    $current_page_id = get_the_ID();
    if (!$current_page_id) {
        error_log("Skipping asset enqueuing: No page ID available");
        return;
    }
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    $current_page_slug = get_post_field('post_name', $current_page_id);
    error_log("Target pages: " . print_r($target_pages, true));
    error_log("Current page ID: $current_page_id, Slug: $current_page_slug");

    if (in_array($current_page_slug, $target_pages) || $current_page_id == 19) {
        error_log("Enqueuing assets for page ID: $current_page_id, Slug: $current_page_slug");
        wp_enqueue_script('siaes-form-handler', plugin_dir_url(__FILE__) . 'assets/js/form-handler.js', ['jquery'], '1.0.50', true);
        wp_localize_script('siaes-form-handler', 'siaes_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('siaes_form_nonce'),
            'page_id' => $current_page_id,
            'api_url' => 'https://678photo.com/api/get_all_studio_shop.php'
        ]);
    } else {
        error_log("Not enqueuing assets: Current page slug '$current_page_slug' not in target pages: " . print_r($target_pages, true));
    }
}
add_action('wp_enqueue_scripts', 'siaes_enqueue_assets');

// Handle form submission
function siaes_handle_form_submission() {
    error_log('AJAX handler triggered');
    error_log('Received nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'None'));

    // Verify nonce
    check_ajax_referer('siaes_form_nonce', 'nonce');

    $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
    $page_slug = get_post_field('post_name', $page_id);
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    error_log("Form submission for page ID: $page_id, Slug: $page_slug");
    error_log("Target pages: " . print_r($target_pages, true));
    error_log("Form data: " . print_r($_POST, true));

    if (in_array($page_slug, $target_pages) || $page_id == 19) {
        $form_data = array();
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['action', 'page_id', 'nonce', 'submit'])) {
                $form_data[$key] = sanitize_text_field($value);
            }
        }
        error_log("Sanitized form data: " . json_encode($form_data));
        try {
            siaes_send_emails($form_data, $page_slug);
            wp_send_json_success('Inquiry submitted successfully!');
        } catch (Exception $e) {
            error_log('Error in siaes_send_emails: ' . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    } else {
        error_log("Invalid page. Page slug: $page_slug, Target pages: " . print_r($target_pages, true));
        wp_send_json_error('Invalid page: ' . $page_slug . ' not in target pages: ' . implode(', ', $target_pages));
    }
}
add_action('wp_ajax_siaes_submit_form', 'siaes_handle_form_submission');
add_action('wp_ajax_nopriv_siaes_submit_form', 'siaes_handle_form_submission');

function siaes_send_emails($form_data, $page_slug) {
    error_log("Sending emails for page slug: $page_slug");
    $shop_id = isset($form_data['shop-id']) ? intval($form_data['shop-id']) : 0;
    $company_email = '';
    $company_name = '';

    // Fetch shop email from API
    $api_url = 'https://678photo.com/api/get_all_studio_shop.php';
    error_log("Fetching shop data from API: $api_url");
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        error_log("API request failed: " . $response->get_error_message());
        $company_email = 'info@san-creation.com';
        $company_name = get_option('siaes_company_name', 'KOKENSHA');
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            error_log("API response: " . print_r($data, true));
            if (isset($data['shops']) && is_array($data['shops'])) {
                foreach ($data['shops'] as $shop) {
                    if ($shop['id'] == $shop_id) {
                        $company_email = $shop['company_email'];
                        $company_name = $shop['name'];
                        break;
                    }
                }
            }
            if (empty($company_email)) {
                error_log("No company email found for shop ID: $shop_id. Falling back to default.");
                $company_email = 'info@san-creation.com';
                $company_name = get_option('siaes_company_name', 'KOKENSHA');
            }
        } else {
            error_log("API request failed with HTTP code: $response_code");
            $company_email = 'info@san-creation.com';
            $company_name = get_option('siaes_company_name', 'KOKENSHA');
        }
    }

    error_log("Using company email: $company_email, company name: $company_name");

    $email_format = trim(get_option('siaes_email_format', 'New inquiry from [name] for [company-name]'));
    $company_subject = get_option('siaes_company_subject', 'New Inquiry from [company-name]');
    $default_company_name = get_option('siaes_company_name', 'KOKENSHA');
    $append_form_data = get_option('siaes_append_form_data', 1);
    $language = get_option('siaes_language', 'english');

    $labels = [
        'english' => [
            'form_data_label' => '==== Inquiry Form Data ====',
            'thank_you_subject' => 'Thank You for Your Inquiry',
            'contact_us' => 'We will get back to you soon. Contact us at %s for further assistance.'
        ],
        'japanese' => [
            'form_data_label' => '==== お問い合わせフォームデータ ====',
            'thank_you_subject' => 'お問い合わせありがとうございます',
            'contact_us' => '近日中に対応いたします。さらにサポートが必要な場合は、%sまでご連絡ください。'
        ]
    ];

    // Replace placeholders in subject and message
    $company_subject = str_replace('[company-name]', $company_name ?: $default_company_name, $company_subject);
    $company_message = $email_format;
    $company_message = str_replace('[company-name]', $company_name ?: $default_company_name, $company_message);
    $company_message = str_replace('[company-email]', $company_email, $company_message);

    // Replace form data placeholders
    foreach ($form_data as $key => $value) {
        $company_message = str_replace("[$key]", $value, $company_message);
    }

    if ($append_form_data) {
        $company_message .= "\n\n" . $labels[$language]['form_data_label'] . "\n";
        foreach ($form_data as $key => $value) {
            $company_message .= "$key: $value\n";
        }
    }

    // Prepare user thank-you message
    $user_reply_raw = get_option('siaes_user_reply', 'Thank you for your inquiry!');
    $user_reply_final = $user_reply_raw;
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
    $thank_you_message .= "\n" . sprintf($labels[$language]['contact_us'], $company_email);

    // Load AWS SES SDK
    $autoload_path = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        error_log('AWS SDK autoload.php not found at: ' . $autoload_path);
        throw new Exception('AWS SDK not found. Please install the AWS SDK.');
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

        // Send company email
        $ses_client->sendEmail([
            'Source' => $company_email,
            'Destination' => ['ToAddresses' => [$company_email]],
            'Message' => [
                'Subject' => ['Data' => $company_subject . ' (' . $page_slug . ')', 'Charset' => 'UTF-8'],
                'Body' => ['Text' => ['Data' => $company_message, 'Charset' => 'UTF-8']],
            ],
        ]);
        error_log('✅ Company email sent to: ' . $company_email);

        // Send user thank-you email
        if (!empty($form_data['email'])) {
            $ses_client->sendEmail([
                'Source' => $company_email,
                'Destination' => ['ToAddresses' => [$form_data['email']]],
                'Message' => [
                    'Subject' => ['Data' => $labels[$language]['thank_you_subject'], 'Charset' => 'UTF-8'],
                    'Body' => ['Text' => ['Data' => $thank_you_message, 'Charset' => 'UTF-8']],
                ],
            ]);
            error_log('✅ Thank-you email sent to user: ' . $form_data['email']);
        } else {
            error_log('⚠️ No user email provided, skipping thank-you email.');
        }
    } catch (Exception $e) {
        error_log('❌ Failed to send email: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
        throw new Exception('Failed to send email: ' . $e->getMessage());
    }
}