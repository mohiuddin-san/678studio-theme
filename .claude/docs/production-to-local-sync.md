# 🔄 本番サイト → ローカル環境 完全同期ガイド

## 📋 概要

SSH接続が利用できない環境でも、本番サイトから必要なデータを効率的にローカル環境に同期する方法をまとめています。

## 🚀 方法1: 管理画面ワンクリック同期

### 設定手順

#### ステップ1: 同期用コードを追加
本番サイトの `functions.php` または新規プラグインファイルに以下のコードを追加：

```php
<?php
/**
 * 678 Studio Site Sync
 * 管理画面から本番データを簡単ダウンロード
 */

add_action('admin_menu', function() {
    add_menu_page(
        'Site Sync',
        'Site Sync',
        'manage_options',
        'site-sync',
        'site_sync_page',
        'dashicons-download'
    );
});

function site_sync_page() {
    if (isset($_GET['action']) && current_user_can('administrator')) {
        switch ($_GET['action']) {
            case 'db':
                download_database();
                break;
            case 'theme':
                download_theme_files();
                break;
            case 'uploads':
                download_uploads();
                break;
            case 'plugins':
                download_plugins();
                break;
            case 'complete':
                download_complete_backup();
                break;
        }
    }

    render_sync_page();
}

function download_database() {
    $dump = shell_exec(sprintf(
        'mysqldump --single-transaction -u%s -p%s %s',
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASSWORD),
        escapeshellarg(DB_NAME)
    ));

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="678photo-db-' . date('Y-m-d-H-i') . '.sql"');
    echo $dump;
    exit;
}

function download_theme_files() {
    create_and_download_zip('wp-content/themes', '678photo-themes-' . date('Y-m-d-H-i') . '.zip');
}

function download_uploads() {
    create_and_download_zip('wp-content/uploads', '678photo-uploads-' . date('Y-m-d-H-i') . '.zip');
}

function download_plugins() {
    create_and_download_zip('wp-content/plugins', '678photo-plugins-' . date('Y-m-d-H-i') . '.zip');
}

function download_complete_backup() {
    $zip = new ZipArchive();
    $filename = '678photo-complete-' . date('Y-m-d-H-i') . '.zip';
    $filepath = sys_get_temp_dir() . '/' . $filename;

    if ($zip->open($filepath, ZipArchive::CREATE) === TRUE) {
        // データベースダンプ
        $db_dump = shell_exec(sprintf(
            'mysqldump --single-transaction -u%s -p%s %s',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASSWORD),
            escapeshellarg(DB_NAME)
        ));
        $zip->addFromString('database.sql', $db_dump);

        // wp-contentディレクトリを追加
        add_directory_to_zip($zip, ABSPATH . 'wp-content', 'wp-content');

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath);
        exit;
    }
}

function create_and_download_zip($source_dir, $filename) {
    $zip = new ZipArchive();
    $filepath = sys_get_temp_dir() . '/' . $filename;

    if ($zip->open($filepath, ZipArchive::CREATE) === TRUE) {
        add_directory_to_zip($zip, ABSPATH . $source_dir, basename($source_dir));
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath);
        exit;
    }
}

function add_directory_to_zip($zip, $source, $destination = '') {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        $file_path = $file->getRealPath();
        $relative_path = $destination . '/' . $iterator->getSubPathName();

        if ($file->isDir()) {
            $zip->addEmptyDir($relative_path);
        } elseif ($file->isFile()) {
            $zip->addFile($file_path, $relative_path);
        }
    }
}

function render_sync_page() {
    ?>
    <div class="wrap">
        <h1>🔄 678 Studio Site Sync</h1>
        <p>本番サイトのデータをローカル環境に同期するためのダウンロード機能</p>

        <div class="card" style="max-width: 600px;">
            <h2>📦 個別ダウンロード</h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong>データベース</strong></td>
                        <td>全ての投稿、設定、ユーザー情報</td>
                        <td><a href="?page=site-sync&action=db" class="button button-primary">ダウンロード</a></td>
                    </tr>
                    <tr>
                        <td><strong>テーマファイル</strong></td>
                        <td>全テーマ（678studioを含む）</td>
                        <td><a href="?page=site-sync&action=theme" class="button button-primary">ダウンロード</a></td>
                    </tr>
                    <tr>
                        <td><strong>アップロード</strong></td>
                        <td>画像、動画、メディアファイル</td>
                        <td><a href="?page=site-sync&action=uploads" class="button button-primary">ダウンロード</a></td>
                    </tr>
                    <tr>
                        <td><strong>プラグイン</strong></td>
                        <td>インストール済み全プラグイン</td>
                        <td><a href="?page=site-sync&action=plugins" class="button button-primary">ダウンロード</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <h2>🚀 完全バックアップ</h2>
            <p>データベース + 全ファイルを一括ダウンロード</p>
            <p>
                <a href="?page=site-sync&action=complete" class="button button-primary button-hero">
                    📁 完全バックアップをダウンロード
                </a>
            </p>
            <p><em>注意: ファイルサイズが大きくなる可能性があります（数分〜数十分）</em></p>
        </div>
    </div>

    <style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    .widefat td {
        padding: 10px;
        vertical-align: middle;
    }
    </style>
    <?php
}

// セキュリティ: 管理者のみアクセス可能
add_action('admin_init', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'site-sync') {
        if (!current_user_can('administrator')) {
            wp_die('権限が不足しています。');
        }
    }
});
?>
```

#### ステップ2: 管理画面でダウンロード
1. WordPress管理画面にログイン
2. サイドメニューに「Site Sync」が追加される
3. 必要なデータを選択してダウンロード

## 🌐 方法2: URL直接アクセス方式

### セキュアダウンローダーの設置

#### sync.php ファイルを作成
本番サイトのルートディレクトリに `sync.php` を設置：

```php
<?php
/**
 * 678 Studio Secure Sync API
 * URL: https://678photo.com/sync.php
 */

// セキュリティキー（必ず変更してください）
$SECRET_KEY = 'your-unique-secret-key-678studio-2024';

// 認証チェック
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);
    die('Access denied');
}

// WordPressを読み込み
require_once 'wp-config.php';

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'db':
        download_database_dump();
        break;
    case 'theme':
        download_theme_archive();
        break;
    case 'uploads':
        download_uploads_archive();
        break;
    case 'plugins':
        download_plugins_archive();
        break;
    default:
        show_api_help();
        break;
}

function download_database_dump() {
    $filename = '678photo-db-' . date('Y-m-d-H-i') . '.sql';

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $command = sprintf(
        'mysqldump --single-transaction -u%s -p%s %s 2>/dev/null',
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASSWORD),
        escapeshellarg(DB_NAME)
    );

    passthru($command);
    exit;
}

function download_theme_archive() {
    $filename = '678photo-themes-' . date('Y-m-d-H-i') . '.tar.gz';

    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $command = 'cd ' . dirname(__FILE__) . '/wp-content && tar czf - themes/';
    passthru($command);
    exit;
}

function download_uploads_archive() {
    $filename = '678photo-uploads-' . date('Y-m-d-H-i') . '.tar.gz';

    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $command = 'cd ' . dirname(__FILE__) . '/wp-content && tar czf - uploads/';
    passthru($command);
    exit;
}

function download_plugins_archive() {
    $filename = '678photo-plugins-' . date('Y-m-d-H-i') . '.tar.gz';

    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $command = 'cd ' . dirname(__FILE__) . '/wp-content && tar czf - plugins/';
    passthru($command);
    exit;
}

function show_api_help() {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>678 Studio Sync API</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .endpoint { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
            code { background: #e8e8e8; padding: 2px 5px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <h1>🔄 678 Studio Sync API</h1>
        <p>利用可能なエンドポイント:</p>

        <div class="endpoint">
            <h3>📊 データベース</h3>
            <code>?key=YOUR_KEY&type=db</code>
        </div>

        <div class="endpoint">
            <h3>🎨 テーマファイル</h3>
            <code>?key=YOUR_KEY&type=theme</code>
        </div>

        <div class="endpoint">
            <h3>📁 アップロードファイル</h3>
            <code>?key=YOUR_KEY&type=uploads</code>
        </div>

        <div class="endpoint">
            <h3>🔌 プラグイン</h3>
            <code>?key=YOUR_KEY&type=plugins</code>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
```

### ローカルでの使用方法

```bash
# 環境変数設定
export SYNC_KEY="your-unique-secret-key-678studio-2024"
export SITE_URL="https://678photo.com"

# データベースダウンロード
curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=db" -o database.sql

# テーマダウンロード
curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=theme" -o themes.tar.gz
tar xzf themes.tar.gz

# アップロードダウンロード
curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=uploads" -o uploads.tar.gz
tar xzf uploads.tar.gz

# プラグインダウンロード
curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=plugins" -o plugins.tar.gz
tar xzf plugins.tar.gz
```

## 🔧 ローカル環境への適用

### データベース適用
```bash
# 1. ローカルデータベースにインポート
docker exec -i mysql_container mysql -u root -ppassword 678studio < database.sql

# 2. URL置換（wp-cliを使用）
docker exec wordpress_container wp search-replace 'https://678photo.com' 'http://localhost:8080' --allow-root

# 3. ローカル管理者作成（必要に応じて）
docker exec wordpress_container wp user create localadmin admin@local.test --role=administrator --user_pass=admin123 --allow-root
```

### ファイル適用
```bash
# テーマファイル
cp -r themes/* ./html/wp-content/themes/

# アップロードファイル
cp -r uploads/* ./html/wp-content/uploads/

# プラグイン
cp -r plugins/* ./html/wp-content/plugins/

# 権限修正
sudo chown -R $USER:$USER ./html/wp-content/
chmod -R 755 ./html/wp-content/
```

## 🚨 セキュリティ注意事項

### 必須対策
1. **秘密キーの変更**: デフォルトキーは絶対に使用しない
2. **ファイル削除**: 同期完了後は `sync.php` を削除
3. **アクセス制限**: `.htaccess` でIP制限を追加
4. **HTTPS必須**: 暗号化された接続のみ使用

### 推奨 .htaccess 設定
```apache
# sync.php アクセス制限
<Files "sync.php">
    # 特定IPのみ許可（あなたのIPに変更）
    Order deny,allow
    Deny from all
    Allow from 123.456.789.0

    # または基本認証
    AuthType Basic
    AuthName "Sync Access"
    AuthUserFile /path/to/.htpasswd
    Require valid-user
</Files>
```

## 📋 トラブルシューティング

### よくある問題と解決策

#### 1. ダウンロードが途中で止まる
```bash
# PHP設定を確認・調整
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
set_time_limit(0);
```

#### 2. ZIPファイルが作成されない
- サーバーの一時ディレクトリの権限確認
- PHP ZipArchive 拡張の有効化確認

#### 3. データベースダンプエラー
- MySQL権限の確認
- mysqldump コマンドの存在確認

## 📈 使用例・ワークフロー

### 日次同期ワークフロー
```bash
#!/bin/bash
# daily-sync.sh

echo "🔄 Daily sync starting..."

# 1. データベース同期
echo "📊 Syncing database..."
curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=db" -o "db-$(date +%Y%m%d).sql"

# 2. 必要に応じてファイル同期
if [ "$1" = "full" ]; then
    echo "📁 Full file sync..."
    curl "${SITE_URL}/sync.php?key=${SYNC_KEY}&type=uploads" -o "uploads-$(date +%Y%m%d).tar.gz"
fi

echo "✅ Sync completed!"
```

## 🔒 セキュリティベストプラクティス

1. **定期的なキー変更**: 月1回は秘密キーを変更
2. **アクセスログ監視**: 不正アクセスの検出
3. **ファイル整理**: 不要な同期ファイルの定期削除
4. **バックアップ**: ローカル環境のバックアップも忘れずに

---

**このドキュメントを参考に、セキュアで効率的な同期環境を構築してください。**