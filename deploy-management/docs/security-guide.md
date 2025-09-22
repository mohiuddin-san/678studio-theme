# 678 Studio デプロイメント セキュリティガイド

## 🔒 セキュリティ概要

このガイドでは、678 Studio のデプロイメントシステムにおけるセキュリティベストプラクティスを説明します。

## 🔑 認証とアクセス制御

### SSH公開鍵認証

#### 鍵ペアの生成
```bash
# RSA 4096bit 鍵の生成（推奨）
ssh-keygen -t rsa -b 4096 -C "678studio-deploy@yourdomain.com" -f ~/.ssh/678studio_deploy_rsa

# Ed25519 鍵の生成（より安全）
ssh-keygen -t ed25519 -C "678studio-deploy@yourdomain.com" -f ~/.ssh/678studio_deploy_ed25519
```

#### 鍵ファイルの権限設定
```bash
# 秘密鍵の権限（必須）
chmod 600 ~/.ssh/678studio_deploy_rsa

# 公開鍵の権限
chmod 644 ~/.ssh/678studio_deploy_rsa.pub

# .ssh ディレクトリの権限
chmod 700 ~/.ssh/
```

#### SSH設定の強化
```bash
# ~/.ssh/config の設定例
Host 678studio-prod
    HostName your-server.com
    User deploy_user
    Port 22
    IdentityFile ~/.ssh/678studio_deploy_rsa
    IdentitiesOnly yes
    StrictHostKeyChecking yes
    UserKnownHostsFile ~/.ssh/known_hosts
    ServerAliveInterval 60
    ServerAliveCountMax 3
    Compression yes
```

### アクセス制御

#### IPアドレス制限
```bash
# サーバー側での IP 制限設定例
# /etc/hosts.allow
sshd: 123.456.789.0/24 : allow

# /etc/hosts.deny
sshd: ALL : deny
```

#### ポート変更
```bash
# SSH ポートの変更（デフォルト22から変更）
# /etc/ssh/sshd_config
Port 2222

# ファイアウォール設定
ufw allow 2222/tcp
ufw deny 22/tcp
```

## 🛡️ データ保護

### 機密情報の管理

#### 環境変数による設定管理
```bash
# .env.deploy ファイルの権限
chmod 600 .env.deploy

# Git から除外
echo ".env.deploy" >> .gitignore
echo ".env.deploy.*" >> .gitignore
```

#### 暗号化された設定ファイル
```bash
# GPG による設定ファイルの暗号化
gpg --symmetric --cipher-algo AES256 .env.deploy

# 復号化
gpg --decrypt .env.deploy.gpg > .env.deploy
```

### データベースセキュリティ

#### データベース認証情報の保護
```bash
# 専用データベースユーザーの作成
CREATE USER 'deploy_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON wordpress_db.* TO 'deploy_user'@'localhost';
FLUSH PRIVILEGES;
```

#### データベースバックアップの暗号化
```bash
# 暗号化バックアップの作成
mysqldump wordpress_db | gzip | gpg --symmetric --cipher-algo AES256 > backup_encrypted.sql.gz.gpg

# 復号化
gpg --decrypt backup_encrypted.sql.gz.gpg | gunzip | mysql wordpress_db
```

## 🔍 監査とログ

### デプロイメントログ

#### ログの有効化
```bash
# deploy-config.json での設定
{
  "logging": {
    "enabled": true,
    "location": "../logs/deploy-operations.log",
    "level": "INFO",
    "includeTimestamp": true,
    "includeUser": true,
    "includeSource": true
  }
}
```

#### ログの保護
```bash
# ログファイルの権限設定
chmod 640 logs/deploy-operations.log
chown deploy_user:deploy_group logs/deploy-operations.log

# ログローテーション
logrotate -d /etc/logrotate.d/deploy-logs
```

### セキュリティ監査

#### 定期的なセキュリティチェック
```bash
# SSH 接続ログの確認
grep "Failed password" /var/log/auth.log

# 不正アクセスの検出
fail2ban-client status sshd

# ファイル権限の確認
find /path/to/wordpress -type f \( -perm -o+w -o -perm -g+w \) -exec ls -la {} \;
```

## 🚨 インシデント対応

### セキュリティインシデント対応手順

#### 1. 即座の対応
```bash
# 不正アクセス発見時の緊急対応
# 1. SSH アクセスの一時停止
sudo systemctl stop sshd

# 2. 不正なプロセスの確認
ps aux | grep -E "(php|apache|nginx)" | grep -v grep

# 3. ネットワーク接続の確認
netstat -tulpn | grep LISTEN
```

#### 2. 調査とログ収集
```bash
# アクセスログの分析
tail -1000 /var/log/apache2/access.log | grep -E "(POST|GET)" | sort | uniq -c | sort -nr

# エラーログの確認
tail -500 /var/log/apache2/error.log

# WordPress ログの確認
tail -200 wp-content/debug.log
```

#### 3. 復旧手順
```bash
# 1. バックアップからの復旧
./deploy-management/scripts/deploy-manager.sh rollback

# 2. パスワードの変更
# WordPress 管理者パスワード
# データベースパスワード
# SSH 鍵の再生成

# 3. セキュリティ強化
# ファイル権限の再設定
# プラグインの更新
# WordPress コアの更新
```

## ⚙️ セキュリティ設定

### WordPress セキュリティ

#### wp-config.php の強化
```php
// セキュリティキーの設定
define('AUTH_KEY',         'your-unique-auth-key');
define('SECURE_AUTH_KEY',  'your-unique-secure-auth-key');
define('LOGGED_IN_KEY',    'your-unique-logged-in-key');
define('NONCE_KEY',        'your-unique-nonce-key');
define('AUTH_SALT',        'your-unique-auth-salt');
define('SECURE_AUTH_SALT', 'your-unique-secure-auth-salt');
define('LOGGED_IN_SALT',   'your-unique-logged-in-salt');
define('NONCE_SALT',       'your-unique-nonce-salt');

// ファイル編集の無効化
define('DISALLOW_FILE_EDIT', true);

// 自動更新の設定
define('WP_AUTO_UPDATE_CORE', 'minor');

// デバッグ情報の非表示（本番環境）
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
```

#### .htaccess によるセキュリティ強化
```apache
# WordPress セキュリティ設定

# 管理ディレクトリの保護
<Files wp-config.php>
order allow,deny
deny from all
</Files>

# ディレクトリブラウジングの無効化
Options -Indexes

# PHP ファイルの実行制限（uploads ディレクトリ）
<Directory "wp-content/uploads">
    <Files "*.php">
        Order Deny,Allow
        Deny from all
    </Files>
</Directory>

# XMLRPCの制限
<Files xmlrpc.php>
order deny,allow
deny from all
</Files>

# セキュリティヘッダーの設定
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
```

### サーバーセキュリティ

#### ファイアウォール設定
```bash
# UFW ファイアウォールの設定
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

#### 自動セキュリティ更新
```bash
# 自動更新の有効化
echo 'Unattended-Upgrade::Automatic-Reboot "false";' >> /etc/apt/apt.conf.d/50unattended-upgrades
echo 'Unattended-Upgrade::Remove-Unused-Dependencies "true";' >> /etc/apt/apt.conf.d/50unattended-upgrades
```

## 📋 セキュリティチェックリスト

### デプロイ前チェック

- [ ] SSH 鍵の権限確認（600）
- [ ] 環境変数ファイルの暗号化
- [ ] データベース認証情報の確認
- [ ] バックアップの暗号化確認
- [ ] ファイル除外設定の確認
- [ ] セキュリティプラグインの更新確認

### デプロイ後チェック

- [ ] ファイル権限の確認（755/644）
- [ ] wp-config.php の保護確認
- [ ] 管理ユーザーアクセスの確認
- [ ] セキュリティヘッダーの確認
- [ ] SSL証明書の確認
- [ ] ログファイルの確認

### 定期メンテナンス

- [ ] SSH ログの定期確認（週次）
- [ ] WordPress コア/プラグイン更新（月次）
- [ ] バックアップの整合性確認（月次）
- [ ] セキュリティスキャン実行（月次）
- [ ] アクセスキーのローテーション（四半期）
- [ ] セキュリティ設定の見直し（半年）

## 🆘 緊急連絡先

### セキュリティインシデント発生時

1. **即座の対応**: システム管理者への連絡
2. **ログ保全**: 証拠の保全とバックアップ
3. **影響範囲確認**: 被害状況の把握
4. **復旧作業**: バックアップからの復旧
5. **再発防止**: セキュリティ強化の実装

### 連絡先情報

- **システム管理者**: [連絡先]
- **ホスティング会社サポート**: [連絡先]
- **セキュリティ専門家**: [連絡先]

---

**重要**: このセキュリティガイドは定期的に更新し、最新の脅威に対応できるよう維持してください。