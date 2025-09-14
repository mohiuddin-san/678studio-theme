# 🔑 SSH接続クイックリファレンス

## egao-salon.jp サーバー接続

### **基本接続**
```bash
ssh -i ~/.ssh/egao-salon_rsa -p 10022 xb592942@sv504.xbiz.ne.jp
```

### **重要なディレクトリ**
```bash
# WordPressルート
cd /home/xb592942/egao-salon.jp/public_html/wordpress/

# サイトマップディレクトリ
cd /home/xb592942/egao-salon.jp/public_html/sitemaps/

# セキュリティシステム
cd /home/xb592942/security_monitor/

# 自動修復システム
cd /home/xb592942/security-system/
```

### **よく使うコマンド**

#### **サイトマップ関連**
```bash
# 手動でサイトマップ生成
cd /home/xb592942/egao-salon.jp/public_html/wordpress
/usr/bin/php7.4 auto-sitemap-generator.php generate

# サイトマップログ確認
tail -f /home/xb592942/egao-salon.jp/public_html/sitemaps/sitemap.log

# サイトマップ一覧確認
ls -la /home/xb592942/egao-salon.jp/public_html/sitemaps/
```

#### **セキュリティ関連**
```bash
# セキュリティログ確認
cd /home/xb592942/security_monitor
ls -la

# マルウェアスキャン実行
cd /home/xb592942/security-system
./malware-scanner.sh

# Cron設定確認
crontab -l
```

#### **データベース接続**
```bash
# egao-salon.jp データベース
mysql -u xb592942_wp6 -p7q035hin7u -D xb592942_wp6

# 投稿数確認クエリ例
mysql -u xb592942_wp6 -p7q035hin7u -D xb592942_wp6 -e "SELECT post_type, COUNT(*) FROM wp_posts WHERE post_status='publish' GROUP BY post_type;"
```

---

## ~/.ssh/config 設定例

```bash
# egao-salon.jp 用設定
Host egao-salon
    HostName sv504.xbiz.ne.jp
    User xb592942
    Port 10022
    IdentityFile ~/.ssh/egao-salon_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

設定後は `ssh egao-salon` で接続可能

---

**最終更新**: 2025年9月14日