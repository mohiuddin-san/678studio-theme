#!/bin/bash

# 678 Studio Backup Management Script
# 統合バックアップ・復旧管理システム

set -e

# 設定
PROJECT_ROOT="/Users/yoshiharajunichi/Desktop/works/inside/678"
BACKUP_ROOT="/Users/yoshiharajunichi/Desktop"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_DIR="$(dirname "$SCRIPT_DIR")/config"

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
    echo "🛡️  678 Studio Backup Management System"
    echo "========================================"
    echo -e "${NC}"
}

print_usage() {
    echo "使用方法: $0 [command] [options]"
    echo ""
    echo "コマンド:"
    echo "  backup [type]   バックアップ実行"
    echo "    - full        完全バックアップ（7.6GB）"
    echo "    - optimized   最適化バックアップ（3-4GB）"
    echo "    - production  本番環境からバックアップ"
    echo "  restore [path]  指定パスから復旧"
    echo "  list           バックアップ一覧表示"
    echo "  status         システム状態確認"
    echo "  cleanup        古いバックアップ削除"
    echo "  help           このヘルプを表示"
    echo ""
    echo "例:"
    echo "  $0 backup full"
    echo "  $0 backup optimized"
    echo "  $0 list"
    echo "  $0 status"
    echo "  $0 cleanup --days 14"
}

check_dependencies() {
    if [ ! -d "$PROJECT_ROOT" ]; then
        echo -e "${RED}Error: プロジェクトディレクトリが見つかりません: $PROJECT_ROOT${NC}"
        exit 1
    fi

    if ! command -v docker &> /dev/null; then
        echo -e "${RED}Error: Docker が見つかりません${NC}"
        exit 1
    fi
}

show_status() {
    echo -e "${CYAN}=== システム状態 ===${NC}"
    echo -e "プロジェクトルート: ${GREEN}$PROJECT_ROOT${NC}"
    echo -e "バックアップ先: ${GREEN}$BACKUP_ROOT${NC}"

    # Docker状態確認
    if docker ps &>/dev/null; then
        DOCKER_STATUS=$(docker ps --format "table {{.Names}}\t{{.Status}}" | grep -E "(wordpress|mysql)" | wc -l)
        echo -e "Docker コンテナ: ${GREEN}$DOCKER_STATUS 個実行中${NC}"
    else
        echo -e "Docker: ${RED}停止中または権限なし${NC}"
    fi

    # ディスク容量確認
    DISK_AVAILABLE=$(df -h "$BACKUP_ROOT" | awk 'NR==2 {print $4}')
    echo -e "利用可能容量: ${GREEN}$DISK_AVAILABLE${NC}"

    # バックアップ一覧
    echo -e "\n${CYAN}=== 既存バックアップ ===${NC}"
    list_backups | head -5
    echo ""
}

list_backups() {
    find "$BACKUP_ROOT" -maxdepth 1 -type d -name "manual_backup_*" 2>/dev/null | sort -r | while read backup_dir; do
        if [ -d "$backup_dir" ]; then
            SIZE=$(du -sh "$backup_dir" 2>/dev/null | cut -f1)
            BASENAME=$(basename "$backup_dir")
            DATE_PART=$(echo "$BASENAME" | sed 's/manual_backup_//' | sed 's/_/ /g')
            echo -e "${GREEN}$DATE_PART${NC} - ${YELLOW}$SIZE${NC} - $backup_dir"
        fi
    done
}

run_backup() {
    local backup_type="$1"

    case "$backup_type" in
        "full"|"")
            echo -e "${BLUE}完全バックアップを実行します...${NC}"
            echo -e "${YELLOW}推定サイズ: 7.6GB, 推定時間: 5-10分${NC}"
            cd "$PROJECT_ROOT"
            ./backup-management/scripts/manual_backup.sh
            ;;
        "optimized")
            echo -e "${BLUE}最適化バックアップを実行します...${NC}"
            echo -e "${YELLOW}推定サイズ: 3-4GB, 推定時間: 3-5分${NC}"
            cd "$PROJECT_ROOT"
            ./backup-management/scripts/manual_backup_optimized.sh
            ;;
        "production")
            echo -e "${BLUE}本番環境からバックアップを実行します...${NC}"
            cd "$PROJECT_ROOT"
            ./backup-management/scripts/backup-from-prod.sh
            ;;
        *)
            echo -e "${RED}Error: 不明なバックアップタイプ: $backup_type${NC}"
            echo "利用可能: full, optimized, production"
            exit 1
            ;;
    esac
}

run_restore() {
    local restore_path="$1"

    if [ -z "$restore_path" ]; then
        echo -e "${RED}Error: 復旧元パスを指定してください${NC}"
        echo "例: $0 restore /path/to/backup/manual_backup_20250920_120000"
        exit 1
    fi

    if [ ! -d "$restore_path" ]; then
        echo -e "${RED}Error: 指定されたパスが存在しません: $restore_path${NC}"
        exit 1
    fi

    echo -e "${YELLOW}⚠️  復旧操作を実行します${NC}"
    echo -e "復旧元: $restore_path"
    echo -e "復旧先: $PROJECT_ROOT"
    echo ""
    read -p "続行しますか？ (y/N): " -n 1 -r
    echo

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}復旧を実行中...${NC}"

        # Docker停止
        echo "Docker コンテナを停止中..."
        cd "$PROJECT_ROOT"
        docker compose down 2>/dev/null || true

        # バックアップ実行
        echo "現在の状態をバックアップ中..."
        CURRENT_BACKUP="$BACKUP_ROOT/pre_restore_backup_$(date +%Y%m%d_%H%M%S)"
        cp -r "$PROJECT_ROOT" "$CURRENT_BACKUP"
        echo "現在の状態を保存しました: $CURRENT_BACKUP"

        # 復旧実行
        echo "ファイルを復旧中..."
        rsync -av --delete "$restore_path/" "$PROJECT_ROOT/"

        # Docker再起動
        echo "Docker コンテナを再起動中..."
        cd "$PROJECT_ROOT"
        docker compose up -d

        echo -e "${GREEN}✅ 復旧が完了しました${NC}"
        echo -e "サイト確認: ${CYAN}http://localhost:8080${NC}"
    else
        echo "復旧をキャンセルしました"
    fi
}

cleanup_backups() {
    local days="${1:-14}"

    echo -e "${YELLOW}$days 日以上古いバックアップを削除します...${NC}"

    find "$BACKUP_ROOT" -maxdepth 1 -type d -name "manual_backup_*" -mtime +$days 2>/dev/null | while read old_backup; do
        SIZE=$(du -sh "$old_backup" 2>/dev/null | cut -f1)
        echo -e "削除中: ${RED}$(basename "$old_backup")${NC} ($SIZE)"
        rm -rf "$old_backup"
    done

    echo -e "${GREEN}✅ クリーンアップ完了${NC}"
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
        "list")
            echo -e "${CYAN}=== バックアップ一覧 ===${NC}"
            list_backups
            ;;
        "backup")
            check_dependencies
            run_backup "$2"
            ;;
        "restore")
            check_dependencies
            run_restore "$2"
            ;;
        "cleanup")
            if [ "$2" = "--days" ] && [ -n "$3" ]; then
                cleanup_backups "$3"
            else
                cleanup_backups
            fi
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