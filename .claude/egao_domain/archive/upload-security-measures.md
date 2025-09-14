# ファイルアップロードセキュリティ対策ガイド

## 今回の攻撃で判明した問題

### 攻撃手口
1. **サブディレクトリ作成** - wp-content/uploads/wp/ を作成
2. **.htaccess改ざん** - 独自の.htaccessで制限を回避
3. **特定ファイル名許可** - 自分のマルウェアファイルのみ実行許可

## 根本的防御策

### 1. ディレクトリレベルでのPHP実行完全禁止
```apache
# wp-content/uploads/.htaccess
<FilesMatch "\.">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# すべてのPHPエンジンを無効化
php_flag engine off
AddHandler txt .php .php3 .phtml .pht .php5 .php7 .php8

# .htaccessファイル作成を禁止
<FilesMatch "^\.ht">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### 2. WordPress wp-config.php設定
```php
// ファイル編集機能を無効化
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

// アップロードファイル制限
define('ALLOW_UNFILTERED_UPLOADS', false);
```

### 3. サーバーレベル制限（推奨）
```bash
# アップロードディレクトリで.htaccess作成を禁止
find /path/to/wp-content/uploads -name ".htaccess" -delete
chmod -R 755 /path/to/wp-content/uploads
chattr +i /path/to/wp-content/uploads/.htaccess  # 変更不可能に
```

### 4. プラグインレベル制限
```php
// functions.phpに追加
function restrict_file_upload($file) {
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');

    if (!in_array($file['type'], $allowed_types)) {
        $file['error'] = 'このファイル形式は許可されていません。';
    }

    // ファイル内容もチェック
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);

    if (!in_array($mime, $allowed_types)) {
        $file['error'] = 'ファイル内容が形式と一致しません。';
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'restrict_file_upload');
```

### 5. ファイルアップロード監視
```php
// アップロード時の監視ログ
function log_file_upload($file) {
    $log = date('[Y-m-d H:i:s] ') .
           "Upload: {$file['name']} " .
           "Size: {$file['size']} " .
           "Type: {$file['type']} " .
           "IP: {$_SERVER['REMOTE_ADDR']}\n";

    file_put_contents('/path/to/upload.log', $log, FILE_APPEND | LOCK_EX);
}
add_action('wp_handle_upload', 'log_file_upload');
```

## 緊急実装すべき対策

### 1. 即座実行
```bash
# 既存の悪性.htaccessファイルを削除
find /home/xb592942/*/public_html/wp-content/uploads -name ".htaccess" -path "*/wp/*" -delete

# uploadsディレクトリの権限見直し
chmod 755 /home/xb592942/*/public_html/wp-content/uploads
```

### 2. セキュリティプラグイン導入
- **Wordfence Security** - リアルタイムスキャン
- **Sucuri Security** - ファイル整合性監視
- **iThemes Security** - ファイルアップロード制限

### 3. WAF（Web Application Firewall）設定
```apache
# mod_security規則例
SecRule FILES "@detectXSS" \
    "id:1001, \
    phase:2, \
    msg:'Malicious file upload detected', \
    deny, \
    status:403"

SecRule FILES "@detectSQLi" \
    "id:1002, \
    phase:2, \
    msg:'SQL injection in uploaded file', \
    deny, \
    status:403"
```

## 監視体制

### 1. 自動監視スクリプト
```bash
#!/bin/bash
# ファイル監視スクリプト
inotifywait -m -r -e create,modify /path/to/wp-content/uploads --format '%w%f %e' |
while read FILE EVENT; do
    if [[ $FILE =~ \.(php|phtml|js)$ ]]; then
        echo "ALERT: Suspicious file uploaded: $FILE" | mail -s "Security Alert" admin@domain.com
        rm -f "$FILE"  # 即座に削除
    fi
done
```

### 2. 定期チェック（cron）
```bash
# 毎時実行
0 * * * * find /path/to/wp-content/uploads -name "*.php" -delete && echo "Cleaned PHP files" >> /var/log/security-cleanup.log
```

## まとめ

**今回の攻撃の教訓**:
1. .htaccess改ざんによる制限回避が主要な攻撃手法
2. サブディレクトリ作成による制限迂回
3. 特定ファイル名への例外設定悪用

**最重要対策**:
1. アップロードディレクトリでの.htaccess作成禁止
2. PHP実行の完全無効化
3. リアルタイムファイル監視システム導入