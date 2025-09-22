# 🗺️ 678photo.com サイトマップ・robots.txt 自動生成システム

**最終更新日**: 2025年9月22日
**システムバージョン**: Auto-Sitemap Generator v1.0 for 678photo.com
**稼働状況**: 🟢 正常稼働中（6時間毎実行）

---

## 🎯 **システム概要**

678photo.com専用のサイトマップ自動生成システム。WordPressのカスタム投稿タイプに対応し、SEO最適化されたサイトマップを6時間毎に自動更新します。

### **主要機能**
- ✅ 7種類のサイトマップ自動生成
- ✅ 678photo.com特化のカスタム投稿タイプ対応
- ✅ 6時間毎の完全自動更新
- ✅ robots.txt連携によるSEO最適化
- ✅ PHP7.4対応の高速生成

---

## 🚀 **システム構成**

### **メインシステム**
```
/home/xb592942/678photo.com/public_html/
├── auto-sitemap-generator-678photo.php  (メインジェネレーター)
├── sitemap.xml                          (メインサイトマップインデックス)
├── sitemap-pages.xml                    (固定ページ)
├── sitemap-posts.xml                    (投稿)
├── sitemap-stores.xml                   (店舗情報)
├── sitemap-studio-shops.xml             (スタジオショップ)
├── sitemap-seo-articles.xml             (SEO記事)
├── sitemap-images.xml                   (画像サイトマップ)
├── robots.txt                           (検索エンジン制御)
└── sitemap-generation.log               (生成ログ)
```

### **WordPressプラグイン（補助システム）**
```
/wp-content/plugins/dynamic-sitemap/
├── dynamic-sitemap.php                  (メインプラグイン)
├── dynamic-sitemap-generator.php        (動的生成エンジン)
└── sitemap-scheduler.php                (スケジューラー)
```

---

## ⚙️ **技術仕様**

### **対応カスタム投稿タイプ**
| 投稿タイプ | ファイル名 | 優先度 | 更新頻度 | 説明 |
|------------|------------|--------|----------|------|
| `page` | sitemap-pages.xml | 0.8 | monthly | 固定ページ |
| `post` | sitemap-posts.xml | 0.6 | weekly | ブログ投稿 |
| `stores` | sitemap-stores.xml | 0.9 | weekly | 店舗情報 |
| `studio_shops` | sitemap-studio-shops.xml | 0.7 | monthly | スタジオショップ |
| `seo_articles` | sitemap-seo-articles.xml | 0.5 | monthly | SEO記事 |
| `attachment` | sitemap-images.xml | - | - | 画像ファイル |

### **実行環境**
- **PHP**: 7.4
- **データベース**: MySQL 8.0
- **実行方式**: Cronジョブ + スタンドアロンスクリプト
- **文字エンコーディング**: UTF-8

---

## 🕐 **自動実行スケジュール**

### **Cronジョブ設定**
```bash
# 678photo.com Sitemap Auto Generation - Every 6 hours
0 */6 * * * /usr/bin/php7.4 /home/xb592942/678photo.com/public_html/auto-sitemap-generator-678photo.php generate >/dev/null 2>&1
```

### **実行タイムテーブル**
- **00:00** - 深夜自動更新
- **06:00** - 朝の定期更新
- **12:00** - 昼の定期更新
- **18:00** - 夕方の定期更新

---

## 🔧 **手動操作方法**

### **手動サイトマップ生成**
```bash
# SSH接続後
cd /home/xb592942/678photo.com/public_html
/usr/bin/php7.4 auto-sitemap-generator-678photo.php generate
```

### **WordPress管理画面からの操作**
1. WordPress管理画面にログイン
2. 設定 → 678photo Sitemaps
3. 「今すぐ生成」ボタンをクリック

### **ログ確認**
```bash
tail -f /home/xb592942/678photo.com/public_html/sitemap-generation.log
```

---

## 🌐 **robots.txt 設定**

### **現在の設定**
```
User-agent: *
Allow: /

# Priority pages access
Allow: /about
Allow: /gallery
Allow: /stores
Allow: /studio-reservation
Allow: /studio-inquiry

# Admin pages exclusion
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Disallow: /wp-content/themes/
Allow: /wp-content/themes/678studio/assets/

# Sitemap locations
Sitemap: https://678photo.com/sitemap.xml
Sitemap: https://678photo.com/sitemap-pages.xml
Sitemap: https://678photo.com/sitemap-posts.xml
Sitemap: https://678photo.com/sitemap-stores.xml
Sitemap: https://678photo.com/sitemap-studio-shops.xml
Sitemap: https://678photo.com/sitemap-seo-articles.xml
Sitemap: https://678photo.com/sitemap-images.xml
```

---

## 📊 **システムパフォーマンス**

### **生成統計**
- **処理時間**: 5秒以内
- **ファイルサイズ**: 合計約15KB
- **対象ページ数**: 約50ページ
- **画像数**: 約100枚

### **SEO効果**
- ✅ Google検索エンジンによる自動発見
- ✅ 新規ページの迅速なインデックス化
- ✅ 更新ページの適切な再評価
- ✅ サイト全体のクロール効率向上

---

## 🔍 **トラブルシューティング**

### **よくある問題と解決方法**

**1. サイトマップが生成されない**
```bash
# PHP実行権限確認
ls -la auto-sitemap-generator-678photo.php

# 手動実行でエラー確認
/usr/bin/php7.4 auto-sitemap-generator-678photo.php generate
```

**2. 文字化けが発生**
```bash
# ファイルエンコーディング確認
file robots.txt

# 再作成
cat > robots.txt << 'EOF'
[正しい内容をASCII文字で記述]
EOF
```

**3. Cronが実行されない**
```bash
# Cron設定確認
crontab -l | grep 678photo

# ログ確認
grep sitemap /var/log/cron
```

### **緊急時の復旧手順**

**1. バックアップからの復元**
```bash
# バックアップディレクトリ確認
ls -la /home/xb592942/678photo.com/backup_cleanup_*

# ファイル復元（必要に応じて）
cp backup_cleanup_*/auto-sitemap-generator-*.php ./
```

**2. システム再起動**
```bash
# Cron再起動
service cron restart

# 手動生成実行
/usr/bin/php7.4 auto-sitemap-generator-678photo.php generate
```

---

## 📈 **メンテナンス・改善計画**

### **定期メンテナンス（月1回）**
- [ ] ログファイルのローテーション
- [ ] 生成統計の確認
- [ ] パフォーマンスモニタリング
- [ ] バックアップファイルの整理

### **今後の改善案**
- 画像サイトマップの詳細情報追加
- カテゴリー別サイトマップの分離
- 生成統計のダッシュボード化
- エラー通知機能の追加

---

## 🛡️ **セキュリティ**

### **実施済み対策**
- ✅ ルートディレクトリからの不要ファイル除去
- ✅ デバッグファイルの適切なバックアップ
- ✅ 実行権限の最小化
- ✅ ログファイルの定期的なクリーンアップ

### **アクセス制御**
- wp-config.php: 600 (所有者のみ読み書き)
- サイトマップファイル: 644 (一般読み取り可能)
- 生成スクリプト: 644 (実行可能)

---

## 📞 **サポート情報**

### **関連ファイル**
- 設定ファイル: `/home/xb592942/678photo.com/public_html/wp-config.php`
- ログファイル: `/home/xb592942/678photo.com/public_html/sitemap-generation.log`
- バックアップ: `/home/xb592942/678photo.com/backup_cleanup_20250922/`

### **参考URL**
- メインサイトマップ: https://678photo.com/sitemap.xml
- robots.txt: https://678photo.com/robots.txt
- WordPress管理画面: https://678photo.com/wp-admin/

---

**システム管理者**: Claude Code Development Team
**ドメイン**: 678photo.com
**最終検証**: 2025年9月22日 17:40

---

_🗺️ このサイトマップシステムは、678photo.comのSEO最適化とユーザビリティ向上のため24時間稼働しています_