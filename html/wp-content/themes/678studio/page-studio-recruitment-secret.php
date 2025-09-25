<?php
/**
 * Template Name: Studio Recruitment Secret Page
 * Description: シークレット版スタジオ募集ページ（パスワード保護）
 */

// セッション開始
if (!session_id()) {
    session_start();
}

// パスワード設定を取得
// SECURITY: デフォルトパスワードは本番環境では必ず変更してください
$secret_password = get_option('studio_secret_password', 'recruit2024special');
$password_hash = wp_hash($secret_password);

// パスワード認証処理
$is_authenticated = false;
$error_message = '';
$max_attempts = 5;
$lockout_time = 1800; // 30分

// 試行回数チェック（IP偽装対策）
function get_real_ip_address() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

$client_ip = get_real_ip_address();
$attempt_key = 'secret_attempts_' . md5($client_ip);
$attempts = get_transient($attempt_key) ?: 0;

if ($_POST && isset($_POST['secret_password'])) {
    // CSRFトークン検証
    if (!wp_verify_nonce($_POST['secret_nonce'], 'secret_access_nonce')) {
        $error_message = '不正なアクセスです。';
    } elseif ($attempts >= $max_attempts) {
        $error_message = 'アクセス制限により、しばらく時間をおいてからお試しください。';
    } else {
        $input_password = sanitize_text_field($_POST['secret_password']);

        if (wp_hash($input_password) === $password_hash) {
            // 認証成功
            $_SESSION['studio_secret_auth'] = true;
            $_SESSION['studio_secret_time'] = time();
            delete_transient($attempt_key);
            $is_authenticated = true;

            // アクセスログ記録
            $log_data = array(
                'timestamp' => current_time('mysql'),
                'ip_address' => $client_ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'status' => 'success'
            );
            // ログの無限蓄積防止（最新500件まで保持）
            $existing_logs = (array)get_option('studio_secret_access_log', []);
            $existing_logs[] = $log_data;
            if (count($existing_logs) > 500) {
                $existing_logs = array_slice($existing_logs, -500);
            }
            update_option('studio_secret_access_log', $existing_logs);
        } else {
            // 認証失敗
            $attempts++;
            set_transient($attempt_key, $attempts, $lockout_time);
            $error_message = 'パスワードが正しくありません。';

            // 失敗ログ記録
            $log_data = array(
                'timestamp' => current_time('mysql'),
                'ip_address' => $client_ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'status' => 'failed'
            );
            // ログの無限蓄積防止（最新500件まで保持）
            $existing_logs = (array)get_option('studio_secret_access_log', []);
            $existing_logs[] = $log_data;
            if (count($existing_logs) > 500) {
                $existing_logs = array_slice($existing_logs, -500);
            }
            update_option('studio_secret_access_log', $existing_logs);
        }
    }
}


// セッション確認
if (isset($_SESSION['studio_secret_auth']) && $_SESSION['studio_secret_auth'] === true) {
    // セッション有効期限チェック（24時間）
    if (time() - $_SESSION['studio_secret_time'] < 86400) {
        $is_authenticated = true;
    } else {
        // セッション期限切れ
        unset($_SESSION['studio_secret_auth']);
        unset($_SESSION['studio_secret_time']);
    }
}

get_header(); ?>

<?php if (!$is_authenticated): ?>
<!-- パスワード認証画面 -->
<div class="secret-auth-wrapper">
    <div class="secret-auth-container">
        <div class="secret-auth-content">
            <div class="secret-auth-logo">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="678 Logo">
            </div>
            <h1 class="secret-auth-title">限定募集ページ</h1>
            <p class="secret-auth-description">
                このページは特別な方のみアクセス可能です。<br>
                お伝えしたパスワードを入力してください。
            </p>

            <?php if ($error_message): ?>
                <div class="secret-auth-error">
                    <?php echo esc_html($error_message); ?>
                    <?php if ($attempts >= $max_attempts): ?>
                        <p>連続してパスワードを間違えたため、アクセスが制限されています。</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>


            <?php if ($attempts < $max_attempts): ?>
                <form method="post" class="secret-auth-form">
                    <?php wp_nonce_field('secret_access_nonce', 'secret_nonce'); ?>
                    <div class="secret-auth-field">
                        <label for="secret_password">パスワード</label>
                        <input type="password" id="secret_password" name="secret_password" required autocomplete="off" maxlength="50">
                    </div>
                    <button type="submit" class="secret-auth-submit">ページにアクセス</button>
                </form>
            <?php endif; ?>

            <div class="secret-auth-contact">
                <p>パスワードが不明な場合は、担当者にお問い合わせください。</p>
            </div>
        </div>
    </div>
</div>

<!-- スタイルはSCSSファイル（_studio-recruitment-secret.scss）で管理 -->

<?php else: ?>
<!-- メインコンテンツ（認証後） -->
<?php
// シークレットページ識別用のフラグを設定
set_query_var('is_secret_page', true);

// 既存のstudio-recruitmentテンプレートを使用
get_template_part('template-parts/sections/studio-recruitment');
?>

<!-- シークレットページ専用のJavaScript -->
<?php
// recruitment-form.jsを確実に読み込み
wp_enqueue_script(
    'recruitment-form-script',
    get_template_directory_uri() . '/assets/js/recruitment-form.js',
    array('jquery'),
    WP_DEBUG ? filemtime(get_template_directory() . '/assets/js/recruitment-form.js') : '1.0.0',
    true
);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // フォーム送信時にシークレットフラグを追加
    const recruitmentForm = document.getElementById('recruitmentForm');
    if (recruitmentForm) {
        // シークレットページ識別用の hidden field を追加
        const secretField = document.createElement('input');
        secretField.type = 'hidden';
        secretField.name = 'source_type';
        secretField.value = 'secret';
        recruitmentForm.appendChild(secretField);

        // ページタイトルに識別マーク追加
        const pageTitle = document.querySelector('.recruitment-explanation__title');
        if (pageTitle) {
            pageTitle.innerHTML = '🔐 ' + pageTitle.innerHTML + ' <span style="font-size: 0.7em; color: #e74c3c;">(限定募集)</span>';
        }
    }
});
</script>

<?php endif; ?>

<?php get_footer(); ?>