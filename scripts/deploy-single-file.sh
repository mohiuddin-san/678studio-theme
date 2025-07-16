#!/bin/bash
# 単一ファイルデプロイスクリプト

set -e

# 設定（本番サーバー設定後に変更）
REMOTE_HOST="egao-photo-app"                         # SSH設定のHost名
REMOTE_PATH="/path/to/egao-photo-app/wordpress"      # 本番WordPressパス
SSH_PORT="22"                                        # SSHポート
SSH_KEY="$HOME/.ssh/egao-photo-app_rsa"              # SSHキーパス

# カラー設定
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# 引数チェック
if [ $# -eq 0 ]; then
    echo "使用方法: $0 <ファイルパス>"
    echo "例: $0 wp-content/themes/your-theme/style.css"
    exit 1
fi

echo -e "${YELLOW}⚠️  本番サーバーがまだ設定されていません${NC}"
echo -e "${YELLOW}   このスクリプトは本番サーバー設定後に使用してください${NC}"
echo ""
echo -e "${YELLOW}📝 設定が必要な項目:${NC}"
echo -e "${YELLOW}   - REMOTE_HOST: SSH Host設定名${NC}"
echo -e "${YELLOW}   - REMOTE_PATH: 本番WordPressディレクトリパス${NC}"
echo -e "${YELLOW}   - SSH_KEY: SSHキーパス${NC}"
echo ""
echo -e "${RED}❌ デプロイを中止しました（本番サーバー未設定）${NC}"
exit 1

# 以下は本番サーバー設定後に有効化される単一ファイルデプロイ処理のテンプレート