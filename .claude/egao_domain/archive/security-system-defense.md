# WAF完全有効化 + セキュリティシステム側対抗策

## 基本戦略: "Defense in Depth"（多層防御）

WAFを全て有効にしたまま、セキュリティシステム側で以下の対抗策を実装：

### 1. リアルタイムファイル監視システム

#### A. inotify基盤の監視システム
```bash
#!/bin/bash
# security-monitor.sh - リアルタイムファイル監視

# 監視対象ディレクトリ
WATCH_DIRS=(
    "/home/xb592942/*/public_html/wp-content/uploads"
    "/home/xb592942/*/public_html/"
    "/home/xb592942/*/public_html/wp-includes"
    "/home/xb592942/*/public_html/wp-admin"
)

# 危険ファイル検出時の即座対応
monitor_files() {
    inotifywait -m -r -e create,modify,moved_to "${WATCH_DIRS[@]}" --format '%w%f %e %T' --timefmt '%Y-%m-%d %H:%M:%S' |
    while read FILE EVENT TIMESTAMP; do
        # 危険なファイル拡張子をチェック
        if [[ $FILE =~ \.(php|phtml|php3|php4|php5|php7|php8|pht|phar|js|jsp|asp|aspx)$ ]]; then
            echo "[$TIMESTAMP] ALERT: Suspicious file detected: $FILE ($EVENT)"

            # 即座に隔離
            quarantine_file "$FILE"

            # 緊急通知
            alert_admin "$FILE" "$EVENT" "$TIMESTAMP"
        fi

        # .htaccessファイルの変更監視
        if [[ $FILE == *".htaccess" ]]; then
            echo "[$TIMESTAMP] ALERT: .htaccess modified: $FILE"
            verify_htaccess "$FILE"
        fi
    done
}

# ファイル隔離機能
quarantine_file() {
    local file="$1"
    local quarantine_dir="/var/quarantine/$(date +%Y%m%d)"

    # 隔離ディレクトリ作成
    mkdir -p "$quarantine_dir"

    # ファイルを隔離
    mv "$file" "$quarantine_dir/$(basename $file).$(date +%H%M%S)"

    # 安全な代替ファイル作成
    echo "<?php // File quarantined for security - $(date) ?>" > "$file"
    chmod 644 "$file"
}

# .htaccess検証機能
verify_htaccess() {
    local htaccess_file="$1"

    # 危険なパターンをチェック
    if grep -E "(FilesMatch.*Allow from all|php_flag engine on|Options.*ExecCGI)" "$htaccess_file"; then
        echo "CRITICAL: Malicious .htaccess detected: $htaccess_file"

        # 即座にバックアップと置換
        cp "$htaccess_file" "$htaccess_file.suspicious.$(date +%s)"

        # 安全な.htaccessに置換
        cat > "$htaccess_file" << 'EOF'
# Security .htaccess - Auto-generated
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|php8|pht|phar|js|jsp|asp|aspx|exe|sh|cgi|pl|py)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
Options -ExecCGI
php_flag engine off
EOF

        alert_admin "$htaccess_file" "MALICIOUS_HTACCESS" "$(date)"
    fi
}

# アラート送信
alert_admin() {
    local file="$1"
    local event="$2"
    local timestamp="$3"

    # メール通知
    {
        echo "緊急セキュリティアラート"
        echo "時刻: $timestamp"
        echo "イベント: $event"
        echo "ファイル: $file"
        echo "サーバー: $(hostname)"
        echo ""
        echo "即座に確認してください。"
    } | mail -s "[SECURITY ALERT] Malware Detection" admin@yourdomain.com

    # Slackやチャットツールへの通知も可能
    # curl -X POST -H 'Content-type: application/json' \
    #     --data '{"text":"Security Alert: '$event' detected in '$file'"}' \
    #     $SLACK_WEBHOOK_URL
}

# メイン実行
monitor_files
```

#### B. 定期的なマルウェアスキャン
```bash
#!/bin/bash
# malware-scanner.sh - 定期スキャン

scan_malware() {
    echo "マルウェアスキャン開始: $(date)"

    # 既知のマルウェアパターン検索
    find /home/xb592942 -name "*.php" -type f -exec grep -l "eval.*base64_decode\|ini_set.*memory_limit.*-1\|wp-confiq" {} \; > /tmp/suspicious_files.txt

    if [ -s /tmp/suspicious_files.txt ]; then
        echo "危険ファイル発見:"
        cat /tmp/suspicious_files.txt

        # 各ファイルを調査・隔離
        while read -r file; do
            analyze_and_quarantine "$file"
        done < /tmp/suspicious_files.txt
    else
        echo "スキャン完了: 問題なし"
    fi
}

analyze_and_quarantine() {
    local file="$1"

    # ファイルの詳細分析
    echo "分析中: $file"

    # 危険パターンの詳細チェック
    if grep -E "(eval\s*\(\s*\\\$|\\\$[a-zA-Z_][a-zA-Z0-9_]*\s*\(\s*\\\$|base64_decode.*eval|ini_set\s*\(\s*['\"]memory_limit['\"])" "$file"; then
        echo "MALWARE CONFIRMED: $file"
        quarantine_file "$file"
    fi
}

# cron設定: 毎時実行
# 0 * * * * /path/to/malware-scanner.sh >> /var/log/malware-scan.log 2>&1
```

### 2. 自動修復システム

#### WordPressコアファイル自動修復
```bash
#!/bin/bash
# wp-auto-repair.sh

repair_wordpress_core() {
    local wp_path="$1"

    echo "WordPress自動修復開始: $wp_path"

    # コアファイルのチェックサム検証
    cd "$wp_path"

    # 主要ファイルの検証と修復
    local core_files=(
        "index.php"
        "wp-blog-header.php"
        "wp-load.php"
        "wp-config-sample.php"
    )

    for file in "${core_files[@]}"; do
        if [ -f "$file" ]; then
            # ファイルの改ざんチェック
            if grep -E "(eval\s*\(|base64_decode)" "$file" > /dev/null; then
                echo "改ざん検出: $file - 修復中..."

                # バックアップ
                cp "$file" "$file.infected.$(date +%s)"

                # WordPress公式から正常ファイルをダウンロード
                download_clean_file "$file" "$wp_path"
            fi
        else
            echo "ファイル欠損: $file - 復元中..."
            download_clean_file "$file" "$wp_path"
        fi
    done
}

download_clean_file() {
    local filename="$1"
    local wp_path="$2"

    # WordPress公式リポジトリから正常ファイルを取得
    case "$filename" in
        "index.php")
            cat > "$wp_path/index.php" << 'EOF'
<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';
EOF
            ;;
        "wp-blog-header.php")
            cat > "$wp_path/wp-blog-header.php" << 'EOF'
<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

if ( ! isset( $wp_did_header ) ) {

	$wp_did_header = true;

	// Load the WordPress library.
	require_once __DIR__ . '/wp-load.php';

	// Set up the WordPress query.
	wp();

	// Load the theme template.
	require_once ABSPATH . WPINC . '/template-loader.php';

}
EOF
            ;;
    esac

    chmod 644 "$wp_path/$filename"
    echo "ファイル修復完了: $filename"
}

# 全WordPressサイトの自動修復
for site in /home/xb592942/*/public_html; do
    if [ -f "$site/wp-config.php" ]; then
        repair_wordpress_core "$site"
    fi
done
```

### 3. プロアクティブ防御システム

#### A. アップロード前検証
```php
<?php
// upload-security.php - WordPressプラグインとして実装

class ProActiveSecuritySystem {

    public function __construct() {
        add_filter('wp_handle_upload_prefilter', array($this, 'scan_upload_file'));
        add_filter('wp_handle_upload', array($this, 'post_upload_scan'));
        add_action('init', array($this, 'monitor_file_changes'));
    }

    // アップロード前の厳格チェック
    public function scan_upload_file($file) {
        $temp_file = $file['tmp_name'];

        // 1. ファイル内容スキャン
        $content = file_get_contents($temp_file);

        // 危険なパターン検出
        $malware_patterns = [
            '/eval\s*\(\s*\$/',
            '/base64_decode\s*\(\s*["\'][A-Za-z0-9+\/=]+["\']/',
            '/ini_set\s*\(\s*["\']memory_limit["\']/',
            '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*\(\s*\$[a-zA-Z_]/',
            '/wp-confiq\.php/',
            '/FilesMatch.*Allow\s+from\s+all/'
        ];

        foreach ($malware_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $file['error'] = '危険なコードが検出されました。アップロードを拒否します。';
                $this->log_security_event('UPLOAD_BLOCKED', $file['name'], $pattern);
                return $file;
            }
        }

        // 2. ファイルタイプ偽装チェック
        $real_mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $temp_file);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'text/plain'];

        if (!in_array($real_mime, $allowed_mimes)) {
            $file['error'] = '許可されていないファイルタイプです。';
            $this->log_security_event('MIME_VIOLATION', $file['name'], $real_mime);
            return $file;
        }

        return $file;
    }

    // アップロード後の監視
    public function post_upload_scan($file) {
        $uploaded_file = $file['file'];

        // アップロードされたファイルの即座スキャン
        $this->quarantine_if_suspicious($uploaded_file);

        return $file;
    }

    // リアルタイムファイル変更監視
    public function monitor_file_changes() {
        // WordPress cron を使用した定期監視
        if (!wp_next_scheduled('security_file_monitor')) {
            wp_schedule_event(time(), 'every_minute', 'security_file_monitor');
        }

        add_action('security_file_monitor', array($this, 'scan_recent_changes'));
    }

    public function scan_recent_changes() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];

        // 過去5分以内に変更されたファイルをスキャン
        $recent_files = shell_exec("find {$uploads_path} -type f -mmin -5 2>/dev/null");

        if ($recent_files) {
            $files = explode("\n", trim($recent_files));
            foreach ($files as $file) {
                if (!empty($file)) {
                    $this->quarantine_if_suspicious($file);
                }
            }
        }
    }

    private function quarantine_if_suspicious($file_path) {
        if (!file_exists($file_path)) return;

        $content = file_get_contents($file_path);

        // マルウェアパターンチェック
        if (preg_match('/eval\s*\(\s*\$|base64_decode.*eval|ini_set.*memory_limit/', $content)) {
            // 即座に隔離
            $quarantine_path = WP_CONTENT_DIR . '/quarantine/' . date('Ymd') . '/';
            wp_mkdir_p($quarantine_path);

            $quarantined_file = $quarantine_path . basename($file_path) . '.' . time();
            rename($file_path, $quarantined_file);

            // 安全なプレースホルダーファイル作成
            file_put_contents($file_path, '<?php // File quarantined for security - ' . date('c') . ' ?>');

            $this->log_security_event('FILE_QUARANTINED', $file_path, 'Malware detected');
            $this->send_alert('ファイル隔離', $file_path);
        }
    }

    private function log_security_event($event_type, $file, $details) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'event' => $event_type,
            'file' => $file,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        // WordPressオプションにログ保存
        $security_log = get_option('proactive_security_log', []);
        array_unshift($security_log, $log_entry);

        // 最新100件のみ保持
        $security_log = array_slice($security_log, 0, 100);
        update_option('proactive_security_log', $security_log);
    }

    private function send_alert($type, $file) {
        wp_mail(
            get_option('admin_email'),
            '[セキュリティアラート] ' . $type,
            "セキュリティイベントが発生しました。\n\nファイル: {$file}\n時刻: " . current_time('mysql')
        );
    }
}

// システム起動
new ProActiveSecuritySystem();
```

### 4. 包括的監視ダッシュボード

```php
<?php
// security-dashboard.php - 管理画面用

class SecurityDashboard {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_security_menu'));
        add_action('wp_ajax_security_scan', array($this, 'ajax_security_scan'));
    }

    public function add_security_menu() {
        add_menu_page(
            'セキュリティ監視',
            'セキュリティ',
            'manage_options',
            'security-monitor',
            array($this, 'render_dashboard'),
            'dashicons-shield-alt'
        );
    }

    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>セキュリティ監視ダッシュボード</h1>

            <div class="security-stats">
                <div class="stat-box">
                    <h3>脅威ブロック数（24時間）</h3>
                    <span class="stat-number"><?php echo $this->get_blocked_threats_count(); ?></span>
                </div>

                <div class="stat-box">
                    <h3>隔離ファイル数</h3>
                    <span class="stat-number"><?php echo $this->get_quarantined_files_count(); ?></span>
                </div>

                <div class="stat-box">
                    <h3>最終スキャン</h3>
                    <span class="stat-time"><?php echo $this->get_last_scan_time(); ?></span>
                </div>
            </div>

            <div class="security-actions">
                <button id="full-scan" class="button button-primary">完全スキャン実行</button>
                <button id="quarantine-review" class="button">隔離ファイル確認</button>
                <button id="security-log" class="button">セキュリティログ</button>
            </div>

            <div id="scan-results"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#full-scan').click(function() {
                $.post(ajaxurl, {
                    action: 'security_scan',
                    scan_type: 'full'
                }, function(response) {
                    $('#scan-results').html(response.data);
                });
            });
        });
        </script>
        <?php
    }
}

new SecurityDashboard();
```

## システム運用方針

### Phase 1: 監視体制確立
1. ファイル監視システム稼働
2. 自動隔離機能有効化
3. アラート通知設定

### Phase 2: プロアクティブ防御
1. アップロード前検証強化
2. リアルタイム修復システム
3. 管理ダッシュボード運用

### Phase 3: AI/機械学習導入
1. 異常パターン学習
2. 予測的脅威検知
3. 自動対応レベル向上

これらのシステムにより、**WAFを完全有効化したまま**でも高度なセキュリティ防御が可能になります。