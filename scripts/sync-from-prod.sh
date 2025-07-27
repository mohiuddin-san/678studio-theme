#!/bin/bash

# =============================================================================
# Production Sync Script for myproject
# エックスサーバーからローカルへの同期
# =============================================================================

set -e

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
THEME_DIR="$PROJECT_ROOT/html/wp-content/themes/myproject"

# Load configuration
CONFIG_FILE="$PROJECT_ROOT/.env.deploy"
if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "❌ Configuration file not found: $CONFIG_FILE"
    echo "Please run: make ssh-setup first"
    exit 1
fi

source "$CONFIG_FILE"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

print_header() {
    echo -e "${PURPLE}╔════════════════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${PURPLE}║${NC}  ⬇️  myproject Production Sync ${PURPLE}║${NC}"
    echo -e "${PURPLE}╚════════════════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step() {
    echo -e "${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Show sync configuration
show_config() {
    echo ""
    print_info "Sync Configuration:"
    echo "  Project: myproject"
    echo "  Host: $SSH_HOST"
    echo "  User: $SSH_USER"
    echo "  Port: $SSH_PORT"
    echo "  Remote Path: $REMOTE_WP_PATH"
    echo "  Local Theme: $THEME_DIR"
    echo ""
}

# Show usage
show_usage() {
    echo "Usage: $0 [options] [files...]"
    echo ""
    echo "Options:"
    echo "  -h, --help           Show this help message"
    echo "  --theme              Sync entire theme directory (default)"
    echo "  --uploads            Sync wp-content/uploads directory"
    echo "  --database           Download database backup"
    echo "  --config             Sync WordPress configuration files"
    echo "  --dry-run            Show what would be synced without actually syncing"
    echo "  --backup             Create backup before sync"
    echo "  --force              Force sync without confirmation"
    echo "  --exclude-dev        Exclude development files (node_modules, scss, etc.)"
    echo ""
    echo "File-specific sync:"
    echo "  $0 functions.php               # Sync single file"
    echo "  $0 style.css assets/js/        # Sync multiple files/directories"
    echo ""
    echo "Examples:"
    echo "  $0                             # Sync entire theme"
    echo "  $0 --uploads                   # Sync media uploads"
    echo "  $0 --database                  # Download database"
    echo "  $0 --dry-run                   # Preview sync"
    echo "  $0 functions.php style.css     # Sync specific files"
    echo ""
}

# Create backup of local files
create_local_backup() {
    print_step "Creating backup of local files..."
    
    local backup_dir="${BACKUP_DIR:-$PROJECT_ROOT/backups}/sync-backup"
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_name="local_myproject_$timestamp"
    
    # Create backup directory
    mkdir -p "$backup_dir"
    
    # Create backup
    print_info "Backing up local theme directory..."
    cp -r "$THEME_DIR" "$backup_dir/$backup_name" || {
        print_warning "Local backup failed, but continuing..."
        return 0
    }
    
    print_success "Local backup created: $backup_dir/$backup_name"
}

# Sync theme files from production
sync_theme() {
    local dry_run="$1"
    local exclude_dev="$2"
    
    print_step "Syncing theme files from production..."
    
    local rsync_options="-avz"
    
    # Add dry-run option
    if [[ "$dry_run" == true ]]; then
        rsync_options="$rsync_options --dry-run"
        print_info "DRY RUN MODE - No files will be modified"
    fi
    
    # Exclude development files if requested
    if [[ "$exclude_dev" == true ]]; then
        rsync_options="$rsync_options --exclude=node_modules/ --exclude=assets/scss/ --exclude=gulpfile.js --exclude=package*.json"
        print_info "Excluding development files"
    fi
    
    # Sync using rsync over SSH
    print_info "Downloading files from $SSH_HOST..."
    
    rsync $rsync_options \
        -e "ssh -p $SSH_PORT" \
        "$SSH_USER@$SSH_HOST:$REMOTE_WP_PATH/" \
        "$THEME_DIR/" || {
        print_error "Theme sync failed!"
        exit 1
    }
    
    if [[ "$dry_run" != true ]]; then
        print_success "Theme files synced successfully"
    else
        print_info "Dry run completed - use without --dry-run to perform actual sync"
    fi
}

# Sync specific files
sync_files() {
    local files=("$@")
    local dry_run="$1"
    
    # Remove dry_run flag from files array
    if [[ "$dry_run" == true ]]; then
        files=("${files[@]:1}")
    fi
    
    print_step "Syncing specific files from production..."
    
    for file in "${files[@]}"; do
        local remote_file="$REMOTE_WP_PATH/$file"
        local local_file="$THEME_DIR/$file"
        local local_dir=$(dirname "$local_file")
        
        print_info "Syncing: $file"
        
        # Create local directory if needed
        mkdir -p "$local_dir"
        
        # Check if remote file/directory exists
        if ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "test -e $remote_file"; then
            if [[ "$dry_run" == true ]]; then
                print_info "Would download: $remote_file -> $local_file"
            else
                if ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "test -d $remote_file"; then
                    # It's a directory
                    scp -r -P "$SSH_PORT" "$SSH_USER@$SSH_HOST:$remote_file" "$local_dir/"
                else
                    # It's a file
                    scp -P "$SSH_PORT" "$SSH_USER@$SSH_HOST:$remote_file" "$local_file"
                fi
                print_success "✓ $file synced"
            fi
        else
            print_warning "✗ $file not found on remote server"
        fi
    done
}

# Sync uploads directory
sync_uploads() {
    local dry_run="$1"
    local uploads_dir="$PROJECT_ROOT/html/wp-content/uploads"
    local remote_uploads="${REMOTE_PATH}/wp-content/uploads"
    
    print_step "Syncing uploads directory from production..."
    
    local rsync_options="-avz"
    if [[ "$dry_run" == true ]]; then
        rsync_options="$rsync_options --dry-run"
    fi
    
    # Create uploads directory if it doesn't exist
    mkdir -p "$uploads_dir"
    
    print_info "Downloading uploads from $SSH_HOST..."
    
    rsync $rsync_options \
        -e "ssh -p $SSH_PORT" \
        "$SSH_USER@$SSH_HOST:$remote_uploads/" \
        "$uploads_dir/" || {
        print_error "Uploads sync failed!"
        exit 1
    }
    
    if [[ "$dry_run" != true ]]; then
        print_success "Uploads synced successfully"
    fi
}

# Download database backup
download_database() {
    print_step "Downloading database backup..."
    
    local backup_dir="${BACKUP_DIR:-$PROJECT_ROOT/backups}/database"
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local db_backup_name="myproject_db_$timestamp.sql"
    
    # Create backup directory
    mkdir -p "$backup_dir"
    
    print_info "Creating database backup on remote server..."
    
    # Create database backup on remote server and download
    ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "
        cd $REMOTE_PATH
        if command -v wp &> /dev/null; then
            wp db export /tmp/$db_backup_name --path=$REMOTE_PATH
        else
            mysqldump -u\$DB_USER -p\$DB_PASSWORD \$DB_NAME > /tmp/$db_backup_name
        fi
    " || {
        print_error "Database backup creation failed!"
        exit 1
    }
    
    # Download the backup
    print_info "Downloading database backup..."
    scp -P "$SSH_PORT" "$SSH_USER@$SSH_HOST:/tmp/$db_backup_name" "$backup_dir/"
    
    # Clean up remote temporary file
    ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "rm -f /tmp/$db_backup_name"
    
    print_success "Database backup downloaded: $backup_dir/$db_backup_name"
    print_info "To import: mysql -u root -p myproject_dev < $backup_dir/$db_backup_name"
}

# Sync configuration files
sync_config() {
    local dry_run="$1"
    
    print_step "Syncing WordPress configuration files..."
    
    local config_files=("wp-config.php" ".htaccess")
    local config_dir="$PROJECT_ROOT/config-backup"
    
    mkdir -p "$config_dir"
    
    for config_file in "${config_files[@]}"; do
        local remote_file="$REMOTE_PATH/$config_file"
        local local_file="$config_dir/$config_file"
        
        if ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "test -f $remote_file"; then
            if [[ "$dry_run" == true ]]; then
                print_info "Would download: $config_file"
            else
                scp -P "$SSH_PORT" "$SSH_USER@$SSH_HOST:$remote_file" "$local_file"
                print_success "✓ $config_file synced"
            fi
        else
            print_warning "✗ $config_file not found"
        fi
    done
}

# Show sync summary
show_summary() {
    local sync_type="$1"
    
    echo ""
    print_success "🎉 Sync completed successfully!"
    echo ""
    print_info "Sync Summary:"
    echo "  ✅ Source: $SSH_HOST"
    echo "  ✅ Target: Local development environment"
    echo "  ✅ Type: $sync_type"
    echo ""
    print_info "Next steps:"
    echo "  1. Review synced files for any changes"
    echo "  2. Test local development environment"
    echo "  3. Update local database if needed"
    echo "  4. Run 'npm run dev' to start development"
    echo ""
}

# Main execution
main() {
    local sync_type="theme"
    local dry_run=false
    local create_backup=false
    local force=false
    local exclude_dev=false
    local specific_files=()
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_usage
                exit 0
                ;;
            --theme)
                sync_type="theme"
                shift
                ;;
            --uploads)
                sync_type="uploads"
                shift
                ;;
            --database)
                sync_type="database"
                shift
                ;;
            --config)
                sync_type="config"
                shift
                ;;
            --dry-run)
                dry_run=true
                shift
                ;;
            --backup)
                create_backup=true
                shift
                ;;
            --force)
                force=true
                shift
                ;;
            --exclude-dev)
                exclude_dev=true
                shift
                ;;
            -*)
                print_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
            *)
                specific_files+=("$1")
                sync_type="files"
                shift
                ;;
        esac
    done
    
    # Display header
    print_header
    
    # Show configuration
    show_config
    
    # Confirmation prompt unless forced or dry run
    if [[ "$force" != true && "$dry_run" != true ]]; then
        echo ""
        print_warning "This will sync files from production to local development environment!"
        print_warning "Local files may be overwritten!"
        read -p "Are you sure you want to continue? (y/N): " -n 1 -r
        echo ""
        
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Sync cancelled by user"
            exit 0
        fi
    fi
    
    echo ""
    
    # Create backup if requested
    if [[ "$create_backup" == true && "$dry_run" != true ]]; then
        create_local_backup
    fi
    
    # Perform sync based on type
    case "$sync_type" in
        "theme")
            sync_theme "$dry_run" "$exclude_dev"
            ;;
        "uploads")
            sync_uploads "$dry_run"
            ;;
        "database")
            if [[ "$dry_run" == true ]]; then
                print_info "DRY RUN: Would download database backup"
            else
                download_database
            fi
            ;;
        "config")
            sync_config "$dry_run"
            ;;
        "files")
            sync_files "$dry_run" "${specific_files[@]}"
            ;;
    esac
    
    # Show summary (unless dry run)
    if [[ "$dry_run" != true ]]; then
        show_summary "$sync_type"
    fi
}

# Check if script is being sourced or executed
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi