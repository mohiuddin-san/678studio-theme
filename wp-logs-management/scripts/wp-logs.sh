#!/bin/bash

# WordPress Logs Management Script
# 678 Studio Theme ログ管理システム

set -e

# 設定
PROJECT_ROOT="/Users/yoshiharajunichi/Desktop/works/inside/678"
THEME_PATH="$PROJECT_ROOT/html/wp-content/themes/678studio"
LOGS_PATH="$PROJECT_ROOT/html/wp-content/debug-logs"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 色付きメッセージ
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

print_header() {
    echo -e "${MAGENTA}"
    echo "=== WordPress Logs Management System ==="
    echo -e "${NC}"
}

print_usage() {
    echo "使用方法: $0 [command] [options]"
    echo ""
    echo "コマンド:"
    echo "  analyze     全ログを解析表示"
    echo "  errors      エラーログのみ表示"
    echo "  summary     直近1時間のサマリー表示"
    echo "  component   コンポーネント別表示"
    echo "  cleanup     古いログファイルを削除"
    echo "  status      ログシステムの状態確認"
    echo "  help        このヘルプを表示"
    echo ""
    echo "オプション:"
    echo "  --hours N   直近N時間のログを表示"
    echo "  --last N    最新N件のログを表示"
    echo "  --level L   特定レベル(ERROR/WARNING/INFO/DEBUG)のログを表示"
    echo ""
    echo "例:"
    echo "  $0 analyze"
    echo "  $0 errors --hours 2"
    echo "  $0 summary"
    echo "  $0 cleanup"
}

check_dependencies() {
    if ! command -v node &> /dev/null; then
        echo -e "${RED}Error: Node.js が見つかりません${NC}"
        exit 1
    fi

    if [ ! -d "$THEME_PATH" ]; then
        echo -e "${RED}Error: テーマディレクトリが見つかりません: $THEME_PATH${NC}"
        exit 1
    fi

    if [ ! -f "$THEME_PATH/package.json" ]; then
        echo -e "${RED}Error: package.json が見つかりません${NC}"
        exit 1
    fi
}

show_status() {
    echo -e "${CYAN}=== システム状態 ===${NC}"
    echo -e "プロジェクトルート: ${GREEN}$PROJECT_ROOT${NC}"
    echo -e "テーマパス: ${GREEN}$THEME_PATH${NC}"
    echo -e "ログディレクトリ: ${GREEN}$LOGS_PATH${NC}"

    if [ -d "$LOGS_PATH" ]; then
        LOG_COUNT=$(find "$LOGS_PATH" -name "*.log" | wc -l)
        LOG_SIZE=$(du -sh "$LOGS_PATH" 2>/dev/null | cut -f1)
        echo -e "ログファイル数: ${GREEN}$LOG_COUNT 個${NC}"
        echo -e "ログディスク使用量: ${GREEN}$LOG_SIZE${NC}"
    else
        echo -e "ログディレクトリ: ${RED}存在しません${NC}"
    fi

    if command -v node &> /dev/null; then
        NODE_VERSION=$(node --version)
        echo -e "Node.js: ${GREEN}$NODE_VERSION${NC}"
    else
        echo -e "Node.js: ${RED}インストールされていません${NC}"
    fi

    echo ""
}

run_analysis() {
    local command="$1"
    shift
    local args="$@"

    echo -e "${BLUE}ログ解析を実行中...${NC}"
    cd "$THEME_PATH"

    case "$command" in
        "analyze")
            npm run wp-logs:analyze $args
            ;;
        "errors")
            npm run wp-logs:errors $args
            ;;
        "summary")
            npm run wp-logs:summary $args
            ;;
        "component")
            npm run wp-logs:component $args
            ;;
        "cleanup")
            echo -e "${YELLOW}ログクリーンアップを実行します...${NC}"
            npm run wp-logs:cleanup
            ;;
        *)
            echo -e "${RED}Error: 不明なコマンド: $command${NC}"
            print_usage
            exit 1
            ;;
    esac
}

# メイン処理
main() {
    print_header

    if [ $# -eq 0 ]; then
        print_usage
        exit 0
    fi

    case "$1" in
        "help"|"-h"|"--help")
            print_usage
            ;;
        "status")
            check_dependencies
            show_status
            ;;
        "analyze"|"errors"|"summary"|"component"|"cleanup")
            check_dependencies
            run_analysis "$@"
            ;;
        *)
            echo -e "${RED}Error: 不明なコマンド: $1${NC}"
            print_usage
            exit 1
            ;;
    esac
}

# スクリプト実行
main "$@"