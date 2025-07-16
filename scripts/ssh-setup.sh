#!/bin/bash
# SSH認証セットアップスクリプト

# 設定 - プロジェクト専用SSHキー（本番サーバー設定後に変更）
SSH_KEY_PATH="$HOME/.ssh/678studio_rsa"
SSH_PASSPHRASE="678Studio2024!"

# カラー設定
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔐 SSH認証をセットアップ中...${NC}"

# 本番サーバー未設定の警告
echo -e "${YELLOW}⚠️  本番サーバーがまだ設定されていません${NC}"
echo -e "${YELLOW}   エックスサーバーにドメインを登録後、以下の設定を更新してください:${NC}"
echo -e "${YELLOW}   - SSH_KEY_PATH${NC}"
echo -e "${YELLOW}   - SSH_PASSPHRASE${NC}"
echo -e "${YELLOW}   - ~/.ssh/config の Host設定${NC}"
echo ""

# SSHエージェントが既に起動しているかチェック
if [ -z "$SSH_AUTH_SOCK" ]; then
    echo "🚀 SSHエージェントを起動中..."
    eval "$(ssh-agent -s)"
else
    echo "✅ SSHエージェントは既に起動しています"
fi

# SSHキーが存在するかチェック
if [ ! -f "$SSH_KEY_PATH" ]; then
    echo -e "${YELLOW}⚠️  SSHキーが見つかりません: $SSH_KEY_PATH${NC}"
    echo -e "${YELLOW}   本番サーバー設定後に以下のコマンドでキーを生成してください:${NC}"
    echo -e "${YELLOW}   ssh-keygen -t rsa -b 4096 -f $SSH_KEY_PATH -C \"your-email@example.com\"${NC}"
    exit 0
fi

# SSHキーが既に追加されているかチェック
if ssh-add -l | grep -q "678studio_rsa"; then
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

echo -e "${GREEN}🎉 SSH認証セットアップ完了！${NC}"
echo -e "${YELLOW}📝 本番サーバー設定後の追加タスク:${NC}"
echo -e "${YELLOW}   1. ~/.ssh/config にHost設定を追加${NC}"
echo -e "${YELLOW}   2. 公開鍵をサーバーに登録${NC}"
echo -e "${YELLOW}   3. 接続テストの実行${NC}"