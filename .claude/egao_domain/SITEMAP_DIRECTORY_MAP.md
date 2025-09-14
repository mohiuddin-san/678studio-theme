# 📁 サイトマップ システム ディレクトリマップ

**作成日**: 2025年9月14日
**サーバー**: sv504.xbiz.ne.jp
**ドメイン**: egao-salon.jp

---

## 🎯 **超シンプル構造**

### **入力（1個 + プラグイン補助）**
```
📂 /home/xb592942/egao-salon.jp/public_html/wordpress/
├── 🔧 auto-sitemap-generator.php                    ← メイン処理（これが主役）
└── 📁 wp-content/plugins/                           ← プラグイン補助システム
    ├── 📁 dynamic-sitemap/                          ← 動的サイトマップ生成
    │   ├── dynamic-sitemap-generator.php           ← プラグイン版ジェネレーター
    │   └── dynamic-sitemap.php                     ← プラグインメインファイル
    └── 📁 auto-sitemap/                             ← 自動サイトマップ更新
        └── auto-sitemap.php                        ← WordPressフック対応
```

### **出力（10個）**
```
📂 /home/xb592942/egao-salon.jp/public_html/
├── 🌐 sitemap.xml                          ← メインのインデックス
└── 📁 sitemaps/                            ← 詳細サイトマップ格納庫
    ├── sitemap-pages.xml                   ← 固定ページ（8.4KB）
    ├── sitemap-posts.xml                   ← ブログ投稿（112KB）
    ├── sitemap-news.xml                    ← ニュース（14KB）
    ├── sitemap-achievement.xml             ← 実績（97KB）
    ├── sitemap-staffs.xml                  ← スタッフ（7KB）
    ├── sitemap-models.xml                  ← モデル（287B）
    ├── sitemap-archives.xml                ← アーカイブ（955B）
    ├── sitemap-reading_glasses_news.xml    ← メガネニュース（5.4KB）
    └── sitemap-images.xml                  ← 画像（954KB）
```

---

## 🔌 **プラグインシステム詳細**

### **プラグイン構成**
```
📁 wp-content/plugins/
├── 📁 dynamic-sitemap/                           ← 動的サイトマップ生成プラグイン
│   ├── dynamic-sitemap.php                      ← プラグインメインファイル（フック設定）
│   └── dynamic-sitemap-generator.php            ← 実際のサイトマップ生成処理
└── 📁 auto-sitemap/                              ← 自動サイトマップ更新プラグイン
    └── auto-sitemap.php                         ← WordPress投稿保存時の自動更新
```

### **各プラグインの役割**

#### **🔄 dynamic-sitemap プラグイン**
- **ファイル**: `dynamic-sitemap.php` + `dynamic-sitemap-generator.php`
- **機能**: 動的サイトマップ生成とリライトルール管理
- **処理**: プラグイン有効化時のセットアップ処理

#### **⚡ auto-sitemap プラグイン**
- **ファイル**: `auto-sitemap.php`
- **機能**: WordPress投稿の保存・削除・ステータス変更時の自動更新
- **フック対応**:
  - `save_post` → 投稿保存時
  - `before_delete_post` → 投稿削除時
  - `transition_post_status` → ステータス変更時
- **管理画面**: WordPress管理画面から手動再生成も可能

### **プラグインと本体ファイルの連携**
```
WordPress投稿操作 → auto-sitemap.php (フック検知)
                        ↓
            auto-sitemap-generator.php (実行)
                        ↓
              サイトマップファイル生成・更新
```

### **対応投稿タイプ**
プラグインが監視・処理する投稿タイプ：
- `page` → 固定ページ
- `post` → ブログ投稿
- `news` → ニュース
- `achievement` → 実績
- `staffs` → スタッフ
- `reading_glasses_news` → メガネニュース
- `models` → モデル

---

## 🔄 **処理の流れ**

### **ステップ1: データ取得**
```
📂 /wordpress/auto-sitemap-generator.php
        ↓ (データベース接続)
📊 WordPressデータベース (xb592942_wp6)
```

### **ステップ2: ファイル生成**
```
🔧 auto-sitemap-generator.php
        ↓ (XMLファイル生成)
📄 sitemap.xml (メインインデックス)
📄 sitemap-pages.xml (ページ一覧)
📄 sitemap-posts.xml (投稿一覧)
📄 ... (その他7ファイル)
```

### **ステップ0: プラグインによるリアルタイム更新**
```
WordPress投稿操作 (保存・削除・ステータス変更)
        ↓ (WordPressフック検知)
⚡ auto-sitemap.php (プラグイン)
        ↓ (バックグラウンド実行)
🔧 auto-sitemap-generator.php
        ↓ (即座にサイトマップ更新)
📂 /public_html/sitemap*.xml
```

### **ステップ3: ファイル配置**
```
Generated Files
        ↓ (ファイル移動)
📂 /public_html/sitemap.xml           ← Web公開用
📂 /public_html/sitemaps/*.xml        ← Web公開用詳細
```

---

## 🌐 **外部アクセスURL**

### **メインサイトマップ**
- **ファイル**: `/public_html/sitemap.xml`
- **URL**: https://egao-salon.jp/sitemap.xml

### **詳細サイトマップ**
- **ファイル**: `/public_html/sitemaps/sitemap-pages.xml`
- **URL**: https://egao-salon.jp/sitemaps/sitemap-pages.xml

---

## ⚙️ **自動実行設定**

### **二重の自動実行システム**

#### **📅 Cronジョブ（定期実行）**
```bash
# crontab設定
0 */6 * * * /usr/bin/php7.4 /home/xb592942/egao-salon.jp/public_html/wordpress/auto-sitemap-generator.php generate >/dev/null 2>&1
```

**実行タイミング**:
- **00:00** → 深夜バッチ
- **06:00** → 朝の更新
- **12:00** → 昼の更新
- **18:00** → 夕方の更新

#### **⚡ プラグイン（リアルタイム実行）**
```bash
# WordPress投稿操作時に即座に実行
/usr/bin/php7.4 /path/to/auto-sitemap-generator.php generate > /dev/null 2>&1 &
```

**実行タイミング**:
- 投稿保存時 → **即座に実行**
- 投稿削除時 → **即座に実行**
- ステータス変更時 → **即座に実行**

### **💪 ダブル保証システム**
1. **プラグイン** → リアルタイム更新（投稿操作時）
2. **Cronジョブ** → 定期バックアップ更新（6時間毎）

これにより、投稿を更新したらすぐにサイトマップが更新され、万が一プラグインが動作しなくても6時間以内には確実に更新されます。

---

## 📊 **ファイルサイズ一覧**

| 場所 | ファイル名 | サイズ | 内容 |
|------|------------|--------|------|
| `/public_html/` | `sitemap.xml` | 1.3KB | インデックス |
| `/sitemaps/` | `sitemap-pages.xml` | 8.4KB | 固定ページ |
| `/sitemaps/` | `sitemap-posts.xml` | 112KB | ブログ投稿 |
| `/sitemaps/` | `sitemap-images.xml` | 954KB | 画像ファイル |
| `/sitemaps/` | `sitemap-news.xml` | 14KB | ニュース |
| `/sitemaps/` | `sitemap-achievement.xml` | 97KB | 実績 |
| `/sitemaps/` | `sitemap-staffs.xml` | 7KB | スタッフ |
| `/sitemaps/` | `sitemap-models.xml` | 287B | モデル |
| `/sitemaps/` | `sitemap-archives.xml` | 955B | アーカイブ |
| `/sitemaps/` | `sitemap-reading_glasses_news.xml` | 5.4KB | メガネニュース |

**合計サイズ**: 約1.2MB

---

## 🛠️ **管理用コマンド**

### **手動実行**

#### **🖥️ SSH経由**
```bash
# SSHでログイン後
cd /home/xb592942/egao-salon.jp/public_html/wordpress/
php auto-sitemap-generator.php generate
```

#### **🎛️ WordPress管理画面**
```
WordPressダッシュボード
    ↓
設定 → Auto Sitemap
    ↓
「全サイトマップを手動再生成」ボタンをクリック
```

**管理画面では**:
- ✅ ワンクリックで再生成
- ✅ 実行ログの確認
- ✅ 実行状況の監視

### **ファイル確認**
```bash
# メインサイトマップ確認
ls -la /home/xb592942/egao-salon.jp/public_html/sitemap.xml

# 詳細サイトマップ確認
ls -la /home/xb592942/egao-salon.jp/public_html/sitemaps/
```

### **アクセス確認**
```bash
# 外部からのアクセステスト
curl https://egao-salon.jp/sitemap.xml
```

---

## 🔍 **トラブルシューティング**

### **問題1: サイトマップが古い**
```bash
# 手動実行
cd /wordpress/
php auto-sitemap-generator.php generate
```

### **問題2: ファイルが存在しない**
```bash
# ディレクトリ確認
ls -la /public_html/sitemap*
ls -la /public_html/sitemaps/
```

### **問題3: 権限エラー**
```bash
# 権限確認・修正
chmod 644 /public_html/sitemap.xml
chmod 644 /public_html/sitemaps/*.xml
```

---

## 📈 **SEO効果測定**

### **Google Search Console**
- サイトマップ送信先: https://egao-salon.jp/sitemap.xml
- インデックス状況監視

### **robots.txt設定**
```
Sitemap: https://egao-salon.jp/sitemap.xml
```

---

## 🎉 **まとめ**

### **📁 シンプルな全体構造**
- **メインファイル**: 1個のPHPファイル (`auto-sitemap-generator.php`)
- **プラグイン**: 2個のプラグイン (リアルタイム更新用)
- **出力**: 10個のXMLファイル
- **実行方法**: ダブル保証（リアルタイム + 6時間毎定期）

### **🏗️ ファイル配置**
- **処理用**: `/wordpress/` ディレクトリ (メインファイル + プラグイン)
- **公開用**: `/public_html/` ディレクトリ (XMLファイル)

### **🌐 アクセス方法**
- **メイン**: https://egao-salon.jp/sitemap.xml
- **詳細**: https://egao-salon.jp/sitemaps/*.xml

### **⚡ 自動化レベル**
1. **投稿操作時** → **即座に更新** (プラグイン)
2. **6時間毎** → **定期更新** (Cronジョブ)
3. **手動実行** → **管理画面 or SSH** (いつでも)

### **🎯 重要ポイント**
- **1個のメインファイル**が全システムを制御
- **2個のプラグイン**がリアルタイム更新を担当
- **完全自動**で人の手を必要としない
- **ダブル保証**で確実にサイトマップ更新

**この構造を理解すれば、サイトマップシステムは完璧です！** 🚀

---

**作成者**: Claude Code Development Team
**対象**: egao-salon.jp サイトマップシステム
**バージョン**: Auto-Sitemap Generator v2.1