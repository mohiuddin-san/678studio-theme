.PHONY: help up down sync deploy reset logs status restart shell wp db-backup db-restore clean ssh-setup server-backup backup-from-prod

# デフォルトターゲット
help:
	@echo "🎨 678studio WordPress開発環境 🎨"
	@echo ""
	@echo "利用可能なコマンド:"
	@echo "  make up          - 環境を起動"
	@echo "  make down        - 環境を停止"
	@echo "  make ssh-setup   - SSH認証を自動設定"
	@echo "  make sync        - 本番→ローカル同期"
	@echo "  make deploy      - ローカル→本番デプロイ（テーマのみ）"
	@echo "  make deploy-full - フルデプロイ（バックアップ＋全データ）"
	@echo "  make deploy-file - 単一ファイルをデプロイ"
	@echo "  make server-backup - サーバー側でバックアップを作成"
	@echo "  make backup-from-prod - サーバーからローカルにバックアップ"
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
	@echo "🌐 WordPress: http://localhost:8080"
	@echo "🗃️ phpMyAdmin: http://localhost:8081"
	@echo ""
	@echo "初回起動の場合は新規WordPressインストールを実行してください"

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

# ローカル→本番デプロイ（テーマのみ）
deploy:
	@echo "🚀 ローカル→本番デプロイを実行します"
	@./scripts/deploy-to-prod.sh

# フルデプロイ（バックアップ＋テーマ＋DB＋プラグイン）
deploy-full:
	@echo "🚀 フルデプロイメント（バックアップ付き）を実行します"
	@echo "⚠️  本番環境のバックアップを取得後、ローカル環境で上書きします"
	@./scripts/deploy-full.sh

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
		echo "✅ リセット完了。新規WordPressインストールを実行してください"; \
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
	@echo "  WordPress:  http://localhost:8080"
	@echo "  phpMyAdmin: http://localhost:8081"
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
	@docker-compose exec db mysqldump -u wp_user -ppassword wordpress_678 > db-backup/backup-$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ バックアップ完了: db-backup/"

# データベースのリストア
db-restore:
	@echo "📥 最新のバックアップからリストア中..."
	@latest=$$(ls -t db-backup/*.sql | head -1); \
	if [ -n "$$latest" ]; then \
		docker-compose exec -T db mysql -u wp_user -ppassword wordpress_678 < $$latest; \
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

# サーバー側でバックアップを作成
server-backup:
	@echo "💾 サーバー側でバックアップを作成します..."
	@echo "📍 保存先: 678photo.com/public_html/backups/"
	@if [ ! -f ".env.deploy" ]; then \
		echo "❌ .env.deployが見つかりません。make ssh-setup を実行してください"; \
		exit 1; \
	fi
	@source .env.deploy && \
	ssh -p $$SSH_PORT -i $$COMPANY_SSH_KEY $$SSH_USER@$$SSH_HOST \
		"if [ -f /home/$$SSH_USER/server-backup.sh ]; then \
			bash /home/$$SSH_USER/server-backup.sh; \
		else \
			echo '❌ server-backup.shが見つかりません'; \
			echo 'スクリプトをアップロードします...'; \
			exit 1; \
		fi" || \
	(echo "📤 バックアップスクリプトをアップロード中..." && \
		source .env.deploy && \
		scp -P $$SSH_PORT -i $$COMPANY_SSH_KEY scripts/server-backup.sh $$SSH_USER@$$SSH_HOST:/home/$$SSH_USER/ && \
		ssh -p $$SSH_PORT -i $$COMPANY_SSH_KEY $$SSH_USER@$$SSH_HOST "bash /home/$$SSH_USER/server-backup.sh")

# サーバーからローカルにバックアップ
backup-from-prod:
	@echo "📥 サーバーからローカルにバックアップを取得します..."
	@./scripts/backup-from-prod.sh