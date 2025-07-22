<?php
/*
Plugin Name: Standalone Inquiry to AWS Email
Description: A standalone plugin to handle inquiry forms and send emails via AWS SES without Contact Form 7 for any inquiry page.
Version: 1.0.46
Author: Your Name
*/

// Register settings page
function siaes_register_settings()
{
    add_options_page('Standalone Inquiry Email', 'Standalone Inquiry Email', 'manage_options', 'siaes-settings', 'siaes_settings_page');
}
add_action('admin_menu', 'siaes_register_settings');

// Settings page HTML
function siaes_settings_page()
{
    global $wpdb;
    $pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    $fields_by_page = array();
    $excluded_fields = ['viewport', 'robots', 'generator', 'action', 'page_id', 'nonce', 'submit'];

    foreach ($pages as $page_slug) {
        $page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page'", $page_slug));
        if ($page_id) {
            $post = get_post($page_id);
            if ($post) {
                error_log("Checking page: $page_slug, ID: $page_id, Content: " . $post->post_content);
                $fields = [];
                $rendered_content = apply_filters('the_content', $post->post_content);
                error_log("Rendered content for $page_slug: " . $rendered_content);
                if (empty(trim($rendered_content))) {
                    $template_slug = get_page_template_slug($page_id);
                    error_log("Template slug for $page_slug (ID $page_id): $template_slug");

                    if ($template_slug) {
                        $template_path = get_stylesheet_directory() . '/' . $template_slug;
                        if (file_exists($template_path)) {
                            ob_start();
                            include $template_path;
                            $rendered_content = ob_get_clean();
                            error_log("Successfully rendered template for $page_slug");
                        } else {
                            error_log("Template file not found at: $template_path");
                            $rendered_content = '';
                        }
                    } else {
                        $rendered_content = apply_filters('the_content', $post->post_content);
                        error_log("Fallback to post content for $page_slug");
                    }
                }

                preg_match_all('/name=["\']([^"\']+)["\']/i', $rendered_content, $matches);
                $all_fields = array_unique($matches[1]);
                foreach ($all_fields as $field) {
                    if (!in_array($field, $excluded_fields)) {
                        $fields[] = $field;
                    }
                }
                $fields_by_page[$page_slug] = $fields;
                error_log("Detected fields for $page_slug: " . json_encode($fields));
            }
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
            'company_emails' => 'Company Emails (page slug:email)',
            'company_emails_help' => 'Format: page-slug:email@example.com, e.g., inquery:info@san-creation.com',
            'email_format' => 'Email Format (Plain Text/HTML)',
            'email_format_help' => 'Format for company email. Use [field-name]: [value] placeholders (e.g., [user-name]: [user-name]) and [company-name] for company name.',
            'company_subject' => 'Company Email Subject',
            'company_subject_help' => 'Custom subject line for company email. Use [company-name] placeholder.',
            'company_name' => 'Company Name',
            'company_name_help' => 'Name of the company to be used in emails.',
            'user_reply' => 'User Reply Message',
            'user_reply_help' => 'Custom initial message for user reply email. Use placeholders like [field-name] if needed.',
            'select_fields' => 'Select Fields for Company Email',
            'append_form_data' => 'Append Form Data to Emails',
            'no_fields_detected' => 'No fields detected. Please ensure your form has input or textarea fields with "name" attributes in the "inquery" page content.',
            'language' => 'Language',
            'submit' => 'Save Changes'
        ],
        'japanese' => [
            'title' => 'スタンドアロンお問い合わせメール設定',
            'aws_access_key' => 'AWSアクセスキーID',
            'aws_secret_key' => 'AWSシークレットアクセスキー',
            'aws_region' => 'AWSリージョン',
            'form_pages' => 'フォームページ（カンマ区切りのスラッグ）',
            'company_emails' => '会社メール（ページスラッグ:メールアドレス）',
            'company_emails_help' => '形式: ページスラッグ:メールアドレス@example.com（例: inquery:info@san-creation.com）',
            'email_format' => 'メール形式（プレーンテキスト/HTML）',
            'email_format_help' => '会社メールの形式。[field-name]: [value] プレースホルダー（例: [user-name]: [user-name]）および[company-name]を会社名に使用します。',
            'company_subject' => '会社メールの件名',
            'company_subject_help' => '会社メールのカスタム件名。[company-name]プレースホルダーを使用してください。',
            'company_name' => '会社名',
            'company_name_help' => 'メールで使用する会社名。',
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
            <?php settings_fields('siaes_settings_group');
            do_settings_sections('siaes_settings_group'); ?>
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
                    <td><input type="text" name="siaes_pages" value="<?php echo esc_attr(get_option('siaes_pages')); ?>" class="regular-text" placeholder="inquery,contact,support"></td>
                </tr>
                <tr>
                    <th><label for="siaes_company_emails"><?php echo esc_html($labels[$language]['company_emails']); ?></label></th>
                    <td><textarea name="siaes_company_emails" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('siaes_company_emails')); ?></textarea>
                        <p><?php echo esc_html($labels[$language]['company_emails_help']); ?></p>
                    </td>
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
                                echo '<label><input type="checkbox" name="siaes_email_fields[' . $field . ']" value="' . $field . '" ' . $checked . '> ' . $field . '</label><br>';
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
}

// Register settings
function siaes_register_settings_fields()
{
    register_setting('siaes_settings_group', 'siaes_aws_access_key_id');
    register_setting('siaes_settings_group', 'siaes_aws_secret_access_key');
    register_setting('siaes_settings_group', 'siaes_aws_region');
    register_setting('siaes_settings_group', 'siaes_pages');
    register_setting('siaes_settings_group', 'siaes_company_emails');
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
function siaes_enqueue_assets()
{
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));
    if (is_page($target_pages)) {
        wp_enqueue_script('siaes-form-handler', plugin_dir_url(__FILE__) . 'assets/js/form-handler.js', array('jquery'), '1.0.46', true);
        wp_localize_script('siaes-form-handler', 'siaes_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('siaes_form_nonce'),
            'page_id' => get_the_ID()
        ));
        error_log("Enqueueing assets for page ID: " . get_the_ID() . ", Slug: " . get_post_field('post_name', get_the_ID()));
    }
}
add_action('wp_enqueue_scripts', 'siaes_enqueue_assets');

// Handle form submission
function siaes_handle_form_submission()
{
    check_ajax_referer('siaes_form_nonce', 'nonce');
    $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
    $page_slug = get_post_field('post_name', $page_id);
    $target_pages = array_map('trim', explode(',', get_option('siaes_pages', '')));

    if (in_array($page_slug, $target_pages)) {
        $form_data = array();
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['action', 'page_id', 'nonce', 'submit'])) {
                $form_data[$key] = sanitize_text_field($value);
            }
        }
        error_log("Form data received for $page_slug: " . json_encode($form_data));
        siaes_send_emails($form_data, $page_slug);
        wp_send_json_success('Inquiry submitted successfully!');
    } else {
        wp_send_json_error('Invalid page or nonce verification failed.');
    }
}
add_action('wp_ajax_siaes_submit_form', 'siaes_handle_form_submission');
add_action('wp_ajax_nopriv_siaes_submit_form', 'siaes_handle_form_submission');

// Send emails via AWS SES
function siaes_send_emails($form_data, $page_slug)
{
    $company_emails = get_option('siaes_company_emails', '');
    $company_email = '';
    foreach (explode("\n", $company_emails) as $line) {
        $parts = array_map('trim', explode(':', $line));
        if (count($parts) === 2) {
            list($slug, $email) = $parts;
            if ($slug === $page_slug) {
                $company_email = $email;
                break;
            }
        }
    }

    if (empty($company_email)) {
        error_log("No company email configured for page: $page_slug. Please check siaes_company_emails setting.");
        return;
    }

    $email_format = trim(get_option('siaes_email_format', '')); 
    $company_subject = get_option('siaes_company_subject', 'New Inquiry from [company-name]');
    $company_name = get_option('siaes_company_name', 'KOKENSHA');
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

    // Replace company placeholders in subject and message
    $company_subject = str_replace('[company-name]', $company_name, $company_subject);

    // Replace placeholders in company message
    $company_message = $email_format;
    $company_message = str_replace('[company-name]', $company_name, $company_message);
    $company_message = str_replace('[company-email]', $company_email, $company_message);

    // Replace any [field-name] placeholders in company_message with actual form data values
    preg_match_all('/\[(.*?)\]/', $company_message, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $field_key) {
            if (isset($form_data[$field_key])) {
                $company_message = str_replace("[$field_key]", $form_data[$field_key], $company_message);
            } else {
                $company_message = str_replace("[$field_key]", '', $company_message);
            }
        }
    }

    // Append form data if enabled
    if ($append_form_data) {
        $company_message .= "\n\n" . $labels[$language]['form_data_label'] . "\n";
        foreach ($form_data as $key => $value) {
            $company_message .= "$key: $value\n";
        }
    }

    // Prepare thank-you message for user with placeholder replacement
    $user_reply_raw = get_option('siaes_user_reply', 'Thank you for your inquiry!');
    $user_reply_final = $user_reply_raw;

    // Replace all [field-name] placeholders in user reply message
    preg_match_all('/\[(.*?)\]/', $user_reply_raw, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $field_key) {
            if (isset($form_data[$field_key])) {
                $user_reply_final = str_replace("[$field_key]", $form_data[$field_key], $user_reply_final);
            } else {
                $user_reply_final = str_replace("[$field_key]", '', $user_reply_final);
            }
        }
    }

    $thank_you_message = $user_reply_final;
    if ($append_form_data) {
        $thank_you_message .= "\n\n" . $labels[$language]['form_data_label'] . "\n";
        foreach ($form_data as $key => $value) {
            $thank_you_message .= "$key: $value\n";
        }
    }
    $thank_you_message .= "\n" . sprintf($labels[$language]['contact_us'], $company_email);

    // AWS SES SDK
    if (!file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
        error_log('AWS SDK autoload.php not found');
        return;
    }
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

    $ses_client = new Aws\Ses\SesClient([
        'version' => 'latest',
        'region' => get_option('siaes_aws_region', 'ap-northeast-1'),
        'credentials' => [
            'key' => get_option('siaes_aws_access_key_id'),
            'secret' => get_option('siaes_aws_secret_access_key'),
        ],
    ]);

    try {
        $ses_client->sendEmail([
            'Source' => $company_email,
            'Destination' => ['ToAddresses' => [$company_email]],
            'Message' => [
                'Subject' => ['Data' => $company_subject . ' (' . $page_slug . ')', 'Charset' => 'UTF-8'],
                'Body' => ['Text' => ['Data' => $company_message, 'Charset' => 'UTF-8']],
            ],
        ]);
        error_log('✅ Company email sent to: ' . $company_email);
    } catch (Exception $e) {
        error_log('❌ Failed to send company email: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
    }

    try {
        if (!empty($form_data['user-email'])) {
            $ses_client->sendEmail([
                'Source' => $company_email,
                'Destination' => ['ToAddresses' => [$form_data['user-email']]],
                'Message' => [
                    'Subject' => ['Data' => $labels[$language]['thank_you_subject'], 'Charset' => 'UTF-8'],
                    'Body' => ['Text' => ['Data' => $thank_you_message, 'Charset' => 'UTF-8']],
                ],
            ]);
            error_log('✅ Thank-you email sent to user: ' . $form_data['user-email']);
        } else {
            error_log('⚠️ No user-email found, so no thank-you email sent.');
        }
    } catch (Exception $e) {
        error_log('❌ Failed to send thank-you email: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
    }
}
?>