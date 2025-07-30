# 678 Studio デプロイメントガイド

## 🚀 フルデプロイメント機能

Xserverへの完全なデプロイメントシステムを実装しました。サーバーのバックアップを取りながら、ローカルのWordPress環境を本番環境へ安全にデプロイできます。

## 🆕 推奨ワークフロー

```bash
# 1. デプロイ前にサーバー側でバックアップ作成
make server-backup

# 2. フルデプロイメント実行
make deploy-full
```

## 📋 主な機能

### 1. サーバーバックアップ
- **`make server-backup`**: サーバー上の`/backups/`ディレクトリに保存（推奨）
- **`make deploy-full`**: ローカルの`backups/`ディレクトリに保存
- **`make backup-from-prod`**: ローカルの`backups/db/`と`backups/files/`に保存

### 2. デプロイ内容
- **テーマ**: SCSSを自動ビルドして最新版をアップロード
- **プラグイン**: 全プラグインを同期（システムプラグインを除く）
- **データベース**: URLを自動変換してインポート
- **メディア**: アップロードファイル（オプション）

### 3. 安全機能
- デプロイ前の確認プロンプト
- 自動バックアップ作成
- エラー時の自動停止
- ファイルパーミッション自動設定

## 🎯 使い方

### 基本コマンド

```bash
# サーバー側でバックアップ作成（デプロイ前推奨）
make server-backup

# フルデプロイメント（推奨）
make deploy-full

# テーマのみデプロイ
make deploy

# 単一ファイルのデプロイ
make deploy-file FILE=wp-content/themes/678studio/style.css

# サーバーからローカルにバックアップ取得
make backup-from-prod
```

### オプション

```bash
# バックアップをスキップ
./scripts/deploy-full.sh --skip-backup

# データベースデプロイをスキップ
./scripts/deploy-full.sh --skip-db

# メディアアップロードをスキップ
./scripts/deploy-full.sh --skip-uploads

# ドライラン（実行内容の確認のみ）
./scripts/deploy-full.sh --dry-run
```

## ⚙️ 初期設定

### 1. SSH設定（必須）

```bash
make ssh-setup
```

以下から選択：
- `678photo` - 678photo.com用
- `egao` - egao-salon.jp用
- `kokensha` - kokensha.com用
- `xserver` - inside.xsrv.jp用

### 2. 設定ファイル

`.env.deploy` が自動生成されます：

```bash
SSH_HOST="sv504.xbiz.ne.jp"
SSH_USER="xb592942"
SSH_PORT="10022"
REMOTE_PATH="/home/xb592942/678photo.com/public_html"
COMPANY_SSH_KEY="/Users/username/.ssh/egao-salon_rsa"
```

## 📁 ディレクトリ構成

```
678/
├── scripts/
│   ├── deploy-full.sh      # フルデプロイメントスクリプト
│   ├── deploy-to-prod.sh   # テーマのみデプロイ
│   ├── backup-from-prod.sh # バックアップのみ実行（ローカル保存）
│   ├── server-backup.sh    # サーバー側バックアップスクリプト
│   └── ssh-setup.sh        # SSH設定
├── backups/                # ローカルバックアップ保存先
│   ├── server_YYYYMMDD_HHMMSS/     # deploy-fullのバックアップ
│   ├── db/                         # backup-from-prodのDB
│   └── files/                      # backup-from-prodのファイル
└── .env.deploy            # デプロイ設定
```

### サーバー側バックアップ

`make server-backup`実行時：
```
/home/xb592942/678photo.com/public_html/backups/
└── 678photo_backup_YYYYMMDD_HHMMSS/
    ├── database/
    │   └── xb592942_1qqor_backup_YYYYMMDD_HHMMSS.sql.gz
    ├── files/
    │   └── wordpress_files_YYYYMMDD_HHMMSS.tar.gz
    └── backup_info.txt
```

## ⚠️ 注意事項

### URL変換
データベースデプロイ時、URLが自動変換されます：
- `http://localhost:8080` → `https://678photo.com`

別のドメインの場合は、`scripts/deploy-full.sh` の `REMOTE_URL` を編集してください。

### 除外ファイル
以下は自動的に除外されます：
- `node_modules/`
- `.git/`
- `*.log`
- キャッシュファイル
- 開発用ファイル

詳細は `scripts/deploy-exclude.txt` で設定可能です。

### パーミッション
デプロイ後、自動的に適切なパーミッションが設定されます：
- ディレクトリ: 755
- ファイル: 644
- uploads: 777

## 🔧 トラブルシューティング

### SSH接続エラー
```bash
# SSH鍵の権限を確認
chmod 600 ~/.ssh/egao-salon_rsa

# 接続テスト
make ssh-setup --test
```

### データベースエラー
```bash
# ローカルDBの確認
docker exec 678-wordpress-1 mysql -u wordpress -pwordpress -e "SHOW DATABASES;"

# リモートDBの確認（バックアップスクリプト使用）
./scripts/backup-from-prod.sh
```

### ビルドエラー
```bash
# 手動でビルド
cd html/wp-content/themes/678studio
npm install
npm run build
```

## 📞 サポート

問題が発生した場合は、以下を確認してください：

1. `.env.deploy` の設定が正しいか
2. SSH鍵が適切に設定されているか
3. ローカル環境が起動しているか（`make status`）
4. エラーログを確認（`make logs`）