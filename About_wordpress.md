# 新egaoWordPressプロジェクト セットアップ手順書

## 🎯 概要

このプロジェクトと同様のSSH接続、Makefile、WP-CLIを使った新しいWordPressプロジェクトを立ち上げるための詳細手順書です。

## 📋 前提条件

- macOS（Apple Silicon対応）
- Docker Desktop for Mac インストール済み
- Git と Make コマンド利用可能
- SSH 接続用ターミナル

## 🚀 新プロジェクトセットアップ手順

### ステップ1: プロジェクトディレクトリの作成

```bash
# 新プロジェクト用ディレクトリを作成
mkdir your-new-project
cd your-new-project

# 必要なディレクトリ構造を作成
mkdir -p {scripts,backup/local,db-backup,html}
```

### ステップ2: Docker設定ファイルの作成

`docker-compose.yml`を作成:

```yaml
version: '3.9'

services:
  db:
    image: mysql:8.0
    platform: linux/arm64/v8
    container_name: mysql-your-project  # プロジェクト名に変更
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: db_local
      MYSQL_USER: wp_user
      MYSQL_PASSWORD: password
    volumes:
      - db_data:/var/lib/mysql:delegated
      - ./db-backup:/backup
    ports:
      - '3306:3306'  # ポート競合時は変更（例：3307:3306）
    healthcheck:
      test: ['CMD', 'mysqladmin', 'ping', '-h', '127.0.0.1']
      interval: 30s
      timeout: 5s
      retries: 5
    command: --default-authentication-plugin=mysql_native_password
    dns:
      - 8.8.8.8

  wordpress:
    image: wordpress:6.4-apache
    platform: linux/arm64/v8
    container_name: wordpress-your-project  # プロジェクト名に変更
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    ports:
      - '8080:80'  # ポート競合時は変更（例：8081:80）
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wp_user
      WORDPRESS_DB_PASSWORD: password
      WORDPRESS_DB_NAME: db_local
      WORDPRESS_DEBUG: 1
      WORDPRESS_CONFIG_EXTRA: |
        define('WP_DEBUG_LOG', true);
        define('WP_DEBUG_DISPLAY', false);
    volumes:
      - ./html:/var/www/html:delegated
    healthcheck:
      test: ['CMD', 'curl', '-f', 'http://localhost']
      interval: 1m
      timeout: 10s
      retries: 3
    dns:
      - 8.8.8.8
      - 8.8.4.4

  wpcli:
    image: wordpress:cli-php8.2
    platform: linux/arm64/v8
    container_name: wpcli-your-project  # プロジェクト名に変更
    depends_on:
      db:
        condition: service_healthy
    user: '33:33'
    command: tail -f /dev/null
    volumes:
      - ./html:/var/www/html:delegated
      - ./db-backup:/backup
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: db_local
      WORDPRESS_DB_USER: wp_user
      WORDPRESS_DB_PASSWORD: password
    working_dir: /var/www/html
    dns:
      - 8.8.8.8

  phpmyadmin:
    image: arm64v8/phpmyadmin:latest
    container_name: phpmyadmin_your_project  # プロジェクト名に変更
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    ports:
      - '8081:80'  # ポート競合時は変更（例：8082:80）
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: password
      PMA_USER: root
      PMA_PASSWORD: password
    dns:
      - 8.8.8.8

volumes:
  db_data:

networks:
  default:
    driver: bridge
```

### ステップ3: Makefileの作成

```makefile
.PHONY: help up down sync deploy reset logs status restart shell wp db-backup db-restore clean ssh-setup

# デフォルトターゲット
help:
	@echo "🌸 your-project.jp WordPress開発環境 🌸"  # プロジェクト名に変更
	@echo ""
	@echo "利用可能なコマンド:"
	@echo "  make up          - 環境を起動"
	@echo "  make down        - 環境を停止"
	@echo "  make ssh-setup   - SSH認証を自動設定"
	@echo "  make sync        - 本番→ローカル同期"
	@echo "  make deploy      - ローカル→本番デプロイ（選択的）"
	@echo "  make deploy-file - 単一ファイルをデプロイ"
	@echo "  make restart     - 環境を再起動"
	@echo "  make shell       - WordPressコンテナにアクセス"
	@echo "  make wp          - WP-CLIコマンドを実行"
	@echo "  make db-backup   - データベースをバックアップ"
	@echo "  make db-restore  - データベースをリストア"
	@echo "  make reset       - 環境をリセット"
	@echo "  make logs        - ログを表示"
	@echo "  make status      - 環境状態を確認"
	@echo "  make clean       - 全データをクリーン（注意！）"
	@echo ""
	@echo "開発フロー:"
	@echo "  1. make ssh-setup - SSH認証設定（初回のみ）"
	@echo "  2. make up       - 環境起動"
	@echo "  3. make sync     - 本番データ取得"
	@echo "  4. 開発作業..."
	@echo "  5. make deploy   - 本番反映"

# ローカル環境起動
up:
	@echo "🚀 WordPress環境を起動中..."
	docker-compose up -d
	@echo "✅ 起動完了!"
	@echo "🌐 WordPress: http://localhost:8080"  # ポート番号確認
	@echo "🗃️ phpMyAdmin: http://localhost:8081"  # ポート番号確認
	@echo ""
	@echo "初回起動の場合は 'make sync' でデータを同期してください"

# SSH認証自動設定
ssh-setup:
	@echo "🔐 SSH認証を自動設定します"
	@./scripts/ssh-setup.sh

# ローカル環境停止
down:
	@echo "⏹️ WordPress環境を停止中..."
	docker-compose down
	@echo "✅ 停止完了"

# 本番→ローカル同期
sync:
	@echo "🔄 本番→ローカル同期を実行します"
	@echo "⚠️  ローカルのデータは上書きされます。続行しますか？ [y/N]"
	@read ans; if [ "$$ans" = "y" ] || [ "$$ans" = "Y" ]; then \
		./scripts/sync-from-prod.sh; \
	else \
		echo "❌ 同期を中止しました"; \
	fi

# ローカル→本番デプロイ
deploy:
	@echo "🚀 ローカル→本番デプロイを実行します"
	@./scripts/deploy-to-prod.sh

# 単一ファイルデプロイ
deploy-file:
	@if [ -z "$(FILE)" ]; then \
		echo "使用方法: make deploy-file FILE=wp-content/themes/your-theme/style.css"; \
		exit 1; \
	fi
	@./scripts/deploy-single-file.sh $(FILE)

# 環境リセット
reset:
	@echo "♻️ 環境をリセットします（全データ削除）"
	@echo "⚠️  すべてのローカルデータが削除されます。続行しますか？ [y/N]"
	@read ans; if [ "$$ans" = "y" ] || [ "$$ans" = "Y" ]; then \
		docker-compose down -v; \
		docker-compose up -d; \
		echo "✅ リセット完了。'make sync' でデータを取得してください"; \
	else \
		echo "❌ リセットを中止しました"; \
	fi

# ログ表示
logs:
	@echo "📋 ログを表示中... (Ctrl+Cで終了)"
	docker-compose logs -f --tail=50

# 環境状態確認
status:
	@echo "📊 環境状態:"
	@echo ""
	@docker-compose ps
	@echo ""
	@echo "🌐 アクセスURL:"
	@echo "  WordPress:  http://localhost:8080"  # ポート番号確認
	@echo "  phpMyAdmin: http://localhost:8081"  # ポート番号確認
	@echo ""
	@echo "💾 ディスク使用量:"
	@docker system df

# Docker環境の再起動
restart:
	@echo "🔄 WordPress環境を再起動中..."
	@docker-compose restart
	@echo "✅ 再起動完了"

# WordPressコンテナにアクセス
shell:
	@echo "🐚 WordPressコンテナにアクセス中..."
	@docker-compose exec wordpress bash

# WP-CLIコマンドの実行
wp:
	@docker-compose exec wpcli wp $(cmd)

# データベースのバックアップ
db-backup:
	@echo "💾 データベースをバックアップ中..."
	@mkdir -p db-backup
	@docker-compose exec db mysqldump -u wp_user -ppassword db_local > db-backup/backup-$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ バックアップ完了: db-backup/"

# データベースのリストア
db-restore:
	@echo "📥 最新のバックアップからリストア中..."
	@latest=$$(ls -t db-backup/*.sql | head -1); \
	if [ -n "$$latest" ]; then \
		docker-compose exec -T db mysql -u wp_user -ppassword db_local < $$latest; \
		echo "✅ リストア完了: $$latest"; \
	else \
		echo "❌ バックアップファイルが見つかりません"; \
	fi

# 全データをクリーン
clean:
	@echo "🧹 全データをクリーンアップします"
	@echo "⚠️  すべてのデータが削除されます！続行しますか？ [y/N]"
	@read ans; if [ "$$ans" = "y" ] || [ "$$ans" = "Y" ]; then \
		docker-compose down -v; \
		rm -rf html/wp-content backup/ db-backup/*.sql; \
		echo "✅ クリーンアップ完了"; \
	else \
		echo "❌ クリーンアップを中止しました"; \
	fi
```

### ステップ4: SSH設定と認証情報の準備

#### 4.1 新プロジェクト用SSH設定

まず、新プロジェクト用の専用SSH設定を行います：

1. **SSH設定ファイルの編集**:
```bash
# SSH設定ファイルを編集
nano ~/.ssh/config

# 以下を追加（ホスト名は適宜変更）
Host your-project
    HostName your-server.com  # 本番サーバーのホスト名
    Port 22                   # SSHポート（サーバーに応じて変更）
    User your-username        # SSHユーザー名
    IdentityFile ~/.ssh/your-project_rsa  # 専用のSSHキー
```

2. **専用SSHキーの生成**（必要に応じて）:
```bash
# 新しいSSHキーペアを生成
ssh-keygen -t rsa -b 4096 -f ~/.ssh/your-project_rsa -C "your-email@example.com"

# パスフレーズを設定（推奨）
# 例: YourSecurePass2024!
```

3. **公開鍵をサーバーに登録**:
```bash
# 公開鍵をサーバーにコピー
ssh-copy-id -i ~/.ssh/your-project_rsa.pub your-username@your-server.com
```

#### 4.2 SSH設定スクリプトの作成

`scripts/ssh-setup.sh`を作成:

```bash
#!/bin/bash
# SSH認証セットアップスクリプト

# 設定 - プロジェクト専用SSHキー
SSH_KEY_PATH="$HOME/.ssh/your-project_rsa"  # キーパスを変更
SSH_PASSPHRASE="YourSecurePass2024!"         # パスフレーズを変更

# カラー設定
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔐 SSH認証をセットアップ中...${NC}"

# SSHエージェントが既に起動しているかチェック
if [ -z "$SSH_AUTH_SOCK" ]; then
    echo "🚀 SSHエージェントを起動中..."
    eval "$(ssh-agent -s)"
else
    echo "✅ SSHエージェントは既に起動しています"
fi

# SSHキーが既に追加されているかチェック
if ssh-add -l | grep -q "your-project_rsa"; then  # キー名を変更
    echo -e "${GREEN}✅ SSHキーは既に追加されています${NC}"
else
    echo "🔑 SSHキーを追加中..."
    
    # expectコマンドが利用可能かチェック
    if command -v expect &> /dev/null; then
        # パスフレーズを自動入力（macOSキーチェーン使用）
        expect -c "
            spawn ssh-add --apple-use-keychain $SSH_KEY_PATH
            expect \"Enter passphrase for\"
            send \"$SSH_PASSPHRASE\\r\"
            expect eof
        "
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ SSHキーの追加が完了しました${NC}"
        else
            echo "⚠️  自動入力に失敗しました。手動でパスフレーズを入力してください"
            echo "パスフレーズ: $SSH_PASSPHRASE"
            ssh-add --apple-use-keychain "$SSH_KEY_PATH"
        fi
    else
        echo "⚠️  expectコマンドが見つかりません。手動でパスフレーズを入力してください"
        echo "パスフレーズ: $SSH_PASSPHRASE"
        ssh-add "$SSH_KEY_PATH"
    fi
fi

# 接続テスト
echo "🔍 SSH接続をテスト中..."
if ssh -o ConnectTimeout=5 -o BatchMode=yes your-project "echo 'SSH接続成功'" 2>/dev/null; then  # ホスト名を変更
    echo -e "${GREEN}✅ SSH接続テスト成功${NC}"
else
    echo "❌ SSH接続テストに失敗しました"
    echo "手動で接続を確認してください: ssh your-project"  # ホスト名を変更
fi

echo -e "${GREEN}🎉 SSH認証セットアップ完了！${NC}"
```

実行権限を付与:
```bash
chmod +x scripts/ssh-setup.sh
```

### ステップ5: 同期スクリプトの作成

`scripts/sync-from-prod.sh`を作成（設定部分のみ変更）:

```bash
#!/bin/bash
# 本番→ローカル同期スクリプト

set -e  # エラー時に停止

# 設定（新プロジェクト環境用に変更）
REMOTE_HOST="your-project"                           # SSH設定のHost名
REMOTE_PATH="/path/to/your/wordpress"                # 本番WordPressパス
LOCAL_URL="http://localhost:8080"                    # ローカルURL（ポート確認）
PROD_URL="https://your-domain.com"                   # 本番URL
SSH_PORT="22"                                        # SSHポート
SSH_KEY="$HOME/.ssh/your-project_rsa"                # SSHキーパス

# 以下のコードは egao-salon プロジェクトと同じものを使用
# （既存のsync-from-prod.shの内容をコピー）
```

実行権限を付与:
```bash
chmod +x scripts/sync-from-prod.sh
```

### ステップ6: デプロイスクリプトの作成

`scripts/deploy-to-prod.sh`を作成（設定部分のみ変更）:

```bash
#!/bin/bash
# ローカル→本番デプロイスクリプト（選択的デプロイ）

set -e  # エラー時に停止

# 設定（新プロジェクト環境用に変更）
REMOTE_HOST="your-username@your-server.com"         # SSH接続情報
REMOTE_PATH="/path/to/your/wordpress"               # 本番WordPressパス
LOCAL_URL="http://localhost:8080"                   # ローカルURL
PROD_URL="https://your-domain.com"                  # 本番URL
SSH_PORT="22"                                       # SSHポート
SSH_KEY="$HOME/.ssh/your-project_rsa"               # SSHキーパス

# 以下のコードは egao-salon プロジェクトと同じものを使用
# （既存のdeploy-to-prod.shの内容をコピー）
```

実行権限を付与:
```bash
chmod +x scripts/deploy-to-prod.sh
```

### ステップ7: 単一ファイルデプロイスクリプトの作成

`scripts/deploy-single-file.sh`を作成:

```bash
#!/bin/bash
# 単一ファイルデプロイスクリプト

set -e

# 設定（新プロジェクト環境用に変更）
REMOTE_HOST="your-username@your-server.com"
REMOTE_PATH="/path/to/your/wordpress"
SSH_PORT="22"
SSH_KEY="$HOME/.ssh/your-project_rsa"

# 引数チェック
if [ $# -eq 0 ]; then
    echo "使用方法: $0 <ファイルパス>"
    echo "例: $0 wp-content/themes/your-theme/style.css"
    exit 1
fi

FILE_PATH="$1"
LOCAL_FILE="./html/$FILE_PATH"
REMOTE_FILE="$REMOTE_PATH/$FILE_PATH"

# ファイル存在チェック
if [ ! -f "$LOCAL_FILE" ]; then
    echo "❌ エラー: ローカルファイルが見つかりません: $LOCAL_FILE"
    exit 1
fi

echo "🚀 単一ファイルデプロイ"
echo "ファイル: $FILE_PATH"
echo "宛先: $REMOTE_HOST:$REMOTE_FILE"
echo ""

# 確認
read -p "このファイルをデプロイしますか？ [y/N] " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ デプロイをキャンセルしました"
    exit 0
fi

# バックアップ作成
BACKUP_DIR="single-file-backup-$(date +%Y%m%d_%H%M%S)"
ssh -i "$SSH_KEY" -p $SSH_PORT $REMOTE_HOST "
    mkdir -p ~/backups/$BACKUP_DIR
    if [ -f '$REMOTE_FILE' ]; then
        cp '$REMOTE_FILE' ~/backups/$BACKUP_DIR/
        echo '📁 バックアップ作成: ~/backups/$BACKUP_DIR/'
    fi
"

# ファイルアップロード
echo "📤 アップロード中..."
scp -i "$SSH_KEY" -P $SSH_PORT "$LOCAL_FILE" "$REMOTE_HOST:$REMOTE_FILE"

echo "✅ デプロイ完了！"
echo "📁 バックアップ: ~/backups/$BACKUP_DIR/"
```

実行権限を付与:
```bash
chmod +x scripts/deploy-single-file.sh
```

### ステップ8: 初回セットアップの実行

1. **Docker環境の起動**:
```bash
make up
```

2. **SSH認証の設定**:
```bash
make ssh-setup
```

3. **本番データの同期**（本番環境が既にある場合）:
```bash
make sync
```

4. **または、新規WordPressインストール**（新規の場合）:
```bash
# ブラウザで http://localhost:8080 にアクセス
# WordPressインストールウィザードを実行
```

### ステップ9: README.mdの作成

プロジェクト用のREADME.mdを作成し、設定値やアクセス情報を記載します。

## 🔧 設定のカスタマイズポイント

### 必ず変更が必要な項目

1. **プロジェクト固有の情報**:
   - コンテナ名（`mysql-your-project`、`wordpress-your-project`など）
   - プロジェクト名（Makefile内の表示文字列）
   - ホスト設定（SSH config）

2. **サーバー接続情報**:
   - SSH接続先（ホスト名、ユーザー名、ポート）
   - 本番WordPressのパス
   - 本番ドメイン

3. **SSH認証**:
   - SSHキーのパス
   - パスフレーズ

4. **ポート設定**（競合回避）:
   - WordPressポート（デフォルト8080）
   - phpMyAdminポート（デフォルト8081）
   - MySQLポート（デフォルト3306）

### オプションの変更項目

1. **WordPressバージョン**:
   - docker-compose.ymlの`image: wordpress:6.4-apache`

2. **PHPバージョン**:
   - `image: wordpress:cli-php8.2`

3. **データベース設定**:
   - MySQL 8.0以外のバージョン
   - 認証プラグインの設定

## 🛠️ トラブルシューティング

### ポート競合の解決

他のプロジェクトとポートが競合する場合：

```yaml
# docker-compose.yml で変更
ports:
  - '8082:80'  # WordPressポート
  - '8083:80'  # phpMyAdminポート
  - '3307:3306'  # MySQLポート
```

Makefileも対応して変更：
```makefile
@echo "🌐 WordPress: http://localhost:8082"
@echo "🗃️ phpMyAdmin: http://localhost:8083"
```

### SSH接続エラーの対処

1. **SSH設定の確認**:
```bash
ssh -T your-project  # 接続テスト
ssh-add -l           # 登録済みキーの確認
```

2. **権限の確認**:
```bash
chmod 600 ~/.ssh/your-project_rsa      # 秘密鍵
chmod 644 ~/.ssh/your-project_rsa.pub  # 公開鍵
```

## 📝 チェックリスト

新プロジェクトセットアップ時のチェックリスト：

- [ ] プロジェクトディレクトリ作成
- [ ] docker-compose.yml 作成・カスタマイズ
- [ ] Makefile 作成・カスタマイズ
- [ ] SSH設定追加（~/.ssh/config）
- [ ] 専用SSHキー生成・登録
- [ ] scripts/ssh-setup.sh 作成・設定
- [ ] scripts/sync-from-prod.sh 作成・設定
- [ ] scripts/deploy-to-prod.sh 作成・設定
- [ ] scripts/deploy-single-file.sh 作成・設定
- [ ] 実行権限付与（chmod +x scripts/*）
- [ ] 初回起動テスト（make up）
- [ ] SSH接続テスト（make ssh-setup）
- [ ] README.md 作成

これで egao-salon プロジェクトと同様の機能を持つ新しいWordPressプロジェクトが作成できます。