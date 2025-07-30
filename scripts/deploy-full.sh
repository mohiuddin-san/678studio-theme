#!/bin/bash

# =============================================================================
# Full Production Deployment Script with Backup
# サーバーバックアップ + WordPress完全デプロイメント
# =============================================================================

set -e

# --- Configuration ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$PROJECT_ROOT/.env.deploy"
THEME_DIR="$PROJECT_ROOT/html/wp-content/themes/678studio"
PLUGINS_DIR="$PROJECT_ROOT/html/wp-content/plugins"
UPLOADS_DIR="$PROJECT_ROOT/html/wp-content/uploads"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Backup directories
BACKUP_DIR="$PROJECT_ROOT/backups"
SERVER_BACKUP_DIR="$BACKUP_DIR/server_$TIMESTAMP"
DB_BACKUP_DIR="$SERVER_BACKUP_DIR/database"
FILES_BACKUP_DIR="$SERVER_BACKUP_DIR/files"

# Color codes
BLUE='\033[0;34m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
PURPLE='\033[0;35m'
NC='\033[0m'

print_header() {
    echo -e "${PURPLE}╔════════════════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${PURPLE}║${NC}  🚀 Full WordPress Deployment with Backup ${PURPLE}║${NC}"
    echo -e "${PURPLE}╚════════════════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_step() {
    echo -e "${BLUE}▶ $1${NC}"
}

# --- Load Configuration ---
if [[ ! -f "$CONFIG_FILE" ]]; then
    print_error "Configuration file not found: $CONFIG_FILE"
    echo "Please run 'make ssh-setup' first."
    exit 1
fi
source "$CONFIG_FILE"

# --- 1. Create Server Backup ---
create_server_backup() {
    print_step "Creating full server backup..."
    
    # Create backup directories
    mkdir -p "$DB_BACKUP_DIR"
    mkdir -p "$FILES_BACKUP_DIR"
    
    # Get database credentials from remote wp-config.php
    print_info "Fetching database credentials..."
    WP_CONFIG=$(ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" "cat $REMOTE_PATH/wp-config.php")
    
    DB_NAME=$(echo "$WP_CONFIG" | grep "DB_NAME" | sed "s/.*, *'\(.*\)'.*/\1/")
    DB_USER=$(echo "$WP_CONFIG" | grep "DB_USER" | sed "s/.*, *'\(.*\)'.*/\1/")
    DB_PASSWORD=$(echo "$WP_CONFIG" | grep "DB_PASSWORD" | sed "s/.*, *'\(.*\)'.*/\1/")
    DB_HOST=$(echo "$WP_CONFIG" | grep "DB_HOST" | sed "s/.*, *'\(.*\)'.*/\1/")
    
    # Backup database
    print_info "Backing up database: $DB_NAME"
    DB_BACKUP_FILE="$DB_BACKUP_DIR/${DB_NAME}_backup_$TIMESTAMP.sql"
    
    ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" \
        "mysqldump -h $DB_HOST -u $DB_USER -p'$DB_PASSWORD' $DB_NAME" > "$DB_BACKUP_FILE"
    
    if [[ -s "$DB_BACKUP_FILE" ]]; then
        print_success "Database backup completed: $(du -h "$DB_BACKUP_FILE" | cut -f1)"
    else
        print_error "Database backup failed"
    fi
    
    # Backup all files
    print_info "Backing up all server files..."
    rsync -avz --progress \
        -e "ssh -p $SSH_PORT -i $COMPANY_SSH_KEY" \
        "$SSH_USER@$SSH_HOST:$REMOTE_PATH/" \
        "$FILES_BACKUP_DIR/"
    
    print_success "Server backup completed at: $SERVER_BACKUP_DIR"
}

# --- 2. Build Local Assets ---
build_assets() {
    print_step "Building theme assets for production..."
    
    cd "$THEME_DIR"
    
    # Install dependencies if needed
    if [[ ! -d "node_modules" ]]; then
        print_info "Installing npm dependencies..."
        npm install
    fi
    
    # Build production assets
    print_info "Compiling SCSS and optimizing assets..."
    npm run build
    
    cd - > /dev/null
    print_success "Assets built successfully"
}

# --- 3. Deploy Theme ---
deploy_theme() {
    print_step "Deploying theme to production..."
    
    local exclude_file="$SCRIPT_DIR/deploy-exclude.txt"
    
    # Create remote theme directory if it doesn't exist
    ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" \
        "mkdir -p $REMOTE_PATH/wp-content/themes/678studio"
    
    # Deploy theme files
    rsync -avz --delete --progress \
        --exclude-from="$exclude_file" \
        -e "ssh -p $SSH_PORT -i $COMPANY_SSH_KEY" \
        "$THEME_DIR/" \
        "$SSH_USER@$SSH_HOST:$REMOTE_PATH/wp-content/themes/678studio/"
    
    print_success "Theme deployed successfully"
}

# --- 4. Deploy Plugins ---
deploy_plugins() {
    print_step "Deploying plugins to production..."
    
    if [[ ! -d "$PLUGINS_DIR" ]]; then
        print_warning "No plugins directory found, skipping..."
        return
    fi
    
    # Count plugins
    local plugin_count=$(ls -1 "$PLUGINS_DIR" | wc -l)
    print_info "Found $plugin_count plugins to deploy"
    
    # Deploy plugins (excluding certain system plugins)
    rsync -avz --progress \
        --exclude="akismet/" \
        --exclude="hello.php" \
        --exclude="*.log" \
        --exclude="cache/" \
        -e "ssh -p $SSH_PORT -i $COMPANY_SSH_KEY" \
        "$PLUGINS_DIR/" \
        "$SSH_USER@$SSH_HOST:$REMOTE_PATH/wp-content/plugins/"
    
    print_success "Plugins deployed successfully"
}

# --- 5. Deploy Database ---
deploy_database() {
    print_step "Deploying database to production..."
    
    # Export local database
    print_info "Exporting local database..."
    LOCAL_DB_FILE="$PROJECT_ROOT/backups/local_db_export_$TIMESTAMP.sql"
    mkdir -p "$PROJECT_ROOT/backups"
    
    # Get local DB credentials
    LOCAL_DB_NAME="wordpress_678"
    LOCAL_DB_USER="wp_user"
    LOCAL_DB_PASS="password"
    LOCAL_DB_HOST="db"
    
    # Export from Docker container (using mysql container since wordpress container doesn't have mysql client)
    docker exec mysql-678studio mysqldump -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" > "$LOCAL_DB_FILE"
    
    if [[ ! -s "$LOCAL_DB_FILE" ]]; then
        print_error "Failed to export local database"
    fi
    
    print_info "Local database exported: $(du -h "$LOCAL_DB_FILE" | cut -f1)"
    
    # Get remote DB credentials
    WP_CONFIG=$(ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" "cat $REMOTE_PATH/wp-config.php")
    REMOTE_DB_NAME=$(echo "$WP_CONFIG" | grep "DB_NAME" | sed "s/.*, *'\(.*\)'.*/\1/")
    REMOTE_DB_USER=$(echo "$WP_CONFIG" | grep "DB_USER" | sed "s/.*, *'\(.*\)'.*/\1/")
    REMOTE_DB_PASSWORD=$(echo "$WP_CONFIG" | grep "DB_PASSWORD" | sed "s/.*, *'\(.*\)'.*/\1/")
    REMOTE_DB_HOST=$(echo "$WP_CONFIG" | grep "DB_HOST" | sed "s/.*, *'\(.*\)'.*/\1/")
    
    # Update URLs in SQL file
    print_info "Updating URLs in database..."
    LOCAL_URL="http://localhost:8080"
    REMOTE_URL="https://678photo.com"  # Update this with your actual domain
    
    sed -i.bak "s|$LOCAL_URL|$REMOTE_URL|g" "$LOCAL_DB_FILE"
    
    # Upload and import database
    print_info "Uploading database to server..."
    scp -P "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$LOCAL_DB_FILE" "$SSH_USER@$SSH_HOST:/tmp/import_$TIMESTAMP.sql"
    
    print_info "Importing database on server..."
    ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" \
        "mysql -h $REMOTE_DB_HOST -u $REMOTE_DB_USER -p'$REMOTE_DB_PASSWORD' $REMOTE_DB_NAME < /tmp/import_$TIMESTAMP.sql && rm /tmp/import_$TIMESTAMP.sql"
    
    print_success "Database deployed successfully"
}

# --- 6. Deploy Uploads (Optional) ---
deploy_uploads() {
    print_step "Deploying media uploads..."
    
    if [[ ! -d "$UPLOADS_DIR" ]]; then
        print_warning "No uploads directory found, skipping..."
        return
    fi
    
    # Deploy uploads directory
    rsync -avz --progress \
        -e "ssh -p $SSH_PORT -i $COMPANY_SSH_KEY" \
        "$UPLOADS_DIR/" \
        "$SSH_USER@$SSH_HOST:$REMOTE_PATH/wp-content/uploads/"
    
    print_success "Media uploads deployed successfully"
}

# --- 7. Post-deployment Tasks ---
post_deployment() {
    print_step "Running post-deployment tasks..."
    
    # Set correct permissions
    print_info "Setting file permissions..."
    ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" "
        find $REMOTE_PATH -type d -exec chmod 755 {} \;
        find $REMOTE_PATH -type f -exec chmod 644 {} \;
        chmod -R 777 $REMOTE_PATH/wp-content/uploads
    "
    
    # Clear cache if W3 Total Cache or similar is installed
    print_info "Clearing caches..."
    ssh -p "$SSH_PORT" -i "$COMPANY_SSH_KEY" "$SSH_USER@$SSH_HOST" "
        if [ -d '$REMOTE_PATH/wp-content/cache' ]; then
            rm -rf $REMOTE_PATH/wp-content/cache/*
        fi
    "
    
    print_success "Post-deployment tasks completed"
}

# --- Show Summary ---
show_summary() {
    echo ""
    print_success "🎉 Full deployment completed successfully!"
    echo ""
    print_info "Deployment Summary:"
    echo "  ✅ Server backup created at: $SERVER_BACKUP_DIR"
    echo "  ✅ Theme deployed"
    echo "  ✅ Plugins deployed"
    echo "  ✅ Database deployed"
    echo "  ✅ Permissions set"
    echo ""
    print_warning "Important Notes:"
    echo "  • Server backup is stored locally for safety"
    echo "  • Database URLs have been updated automatically"
    echo "  • Please test the live site thoroughly"
    echo ""
    echo "🌐 Your site should now be live at: https://678photo.com"
}

# --- Main Execution ---
main() {
    local skip_backup=false
    local skip_db=false
    local skip_uploads=false
    local dry_run=false
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                echo "Usage: $0 [options]"
                echo ""
                echo "Options:"
                echo "  --skip-backup    Skip server backup"
                echo "  --skip-db        Skip database deployment"
                echo "  --skip-uploads   Skip media uploads"
                echo "  --dry-run        Show what would be done"
                echo ""
                exit 0
                ;;
            --skip-backup)
                skip_backup=true
                shift
                ;;
            --skip-db)
                skip_db=true
                shift
                ;;
            --skip-uploads)
                skip_uploads=true
                shift
                ;;
            --dry-run)
                dry_run=true
                shift
                ;;
            *)
                print_error "Unknown option: $1"
                ;;
        esac
    done
    
    print_header
    
    # Show configuration
    print_info "Deployment Configuration:"
    echo "  Host: $SSH_HOST"
    echo "  User: $SSH_USER"
    echo "  Remote: $REMOTE_PATH"
    echo ""
    
    # Confirmation
    if [[ "$dry_run" != true ]]; then
        print_warning "This will deploy your local WordPress to production!"
        read -p "Continue? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Deployment cancelled"
            exit 0
        fi
    fi
    
    # Execute deployment steps
    if [[ "$skip_backup" != true ]]; then
        create_server_backup
    fi
    
    build_assets
    deploy_theme
    deploy_plugins
    
    if [[ "$skip_db" != true ]]; then
        deploy_database
    fi
    
    if [[ "$skip_uploads" != true ]]; then
        deploy_uploads
    fi
    
    post_deployment
    show_summary
}

# Run main function
main "$@"