#!/bin/bash

# =============================================================================
# Production Backup Script
# =============================================================================

set -e

# --- Configuration ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$PROJECT_ROOT/.env.deploy"
LOCAL_BACKUP_DIR="$PROJECT_ROOT/backups"
LOCAL_DB_BACKUP_DIR="$LOCAL_BACKUP_DIR/db"
LOCAL_FILES_BACKUP_DIR="$LOCAL_BACKUP_DIR/files"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Color codes
BLUE='\033[0;34m'
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

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

# --- Load Config ---
if [[ ! -f "$CONFIG_FILE" ]]; then
    print_error "Configuration file not found: $CONFIG_FILE"
    echo "Please run 'scripts/ssh-setup.sh --setup' first."
    exit 1
fi
source "$CONFIG_FILE"
print_info "Configuration loaded from $CONFIG_FILE"

# --- Read DB credentials from wp-config.php on the server ---
print_info "Fetching database credentials from remote wp-config.php..."
WP_CONFIG_CONTENT=$(ssh -p "$SSH_PORT" -i ~/.ssh/egao-salon_rsa "$SSH_USER@$SSH_HOST" "cat $REMOTE_PATH/wp-config.php")

# A bit dangerous to parse with grep/sed, but for this specific format it's okay.
DB_NAME=$(echo "$WP_CONFIG_CONTENT" | grep "DB_NAME" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_USER=$(echo "$WP_CONFIG_CONTENT" | grep "DB_USER" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_PASSWORD=$(echo "$WP_CONFIG_CONTENT" | grep "DB_PASSWORD" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_HOST=$(echo "$WP_CONFIG_CONTENT" | grep "DB_HOST" | sed "s/.*, *'\(.*\)'.*/\1/")

if [[ -z "$DB_NAME" || -z "$DB_USER" || -z "$DB_PASSWORD" || -z "$DB_HOST" ]]; then
    print_error "Failed to parse database credentials from wp-config.php."
fi
print_success "Database credentials fetched successfully."
echo "  DB Name: $DB_NAME"

# --- Create local directories ---
print_info "Creating local backup directories..."
mkdir -p "$LOCAL_DB_BACKUP_DIR"
mkdir -p "$LOCAL_FILES_BACKUP_DIR"
print_success "Directories created."

# --- 1. Backup Database ---
DB_BACKUP_FILE="db_backup_${DB_NAME}_${TIMESTAMP}.sql"
LOCAL_DB_BACKUP_PATH="$LOCAL_DB_BACKUP_DIR/$DB_BACKUP_FILE"

print_info "Starting database backup..."
echo "  - Source DB: $DB_NAME on $SSH_HOST"
echo "  - Target File: $LOCAL_DB_BACKUP_PATH"

ssh -p "$SSH_PORT" -i ~/.ssh/egao-salon_rsa "$SSH_USER@$SSH_HOST" "mysqldump -h $DB_HOST -u $DB_USER -p'$DB_PASSWORD' $DB_NAME" > "$LOCAL_DB_BACKUP_PATH"

if [[ $? -eq 0 && -s "$LOCAL_DB_BACKUP_PATH" ]]; then
    print_success "Database backup completed successfully."
else
    rm -f "$LOCAL_DB_BACKUP_PATH" # Clean up empty file on failure
    print_error "Database backup failed."
fi

# --- 2. Backup Files ---
print_info "Starting file backup..."
echo "  - Source Dir: $SSH_USER@$SSH_HOST:$REMOTE_PATH/"
echo "  - Target Dir: $LOCAL_FILES_BACKUP_DIR"

rsync -avz -e "ssh -p $SSH_PORT -i ~/.ssh/egao-salon_rsa" --delete --progress "$SSH_USER@$SSH_HOST:$REMOTE_PATH/" "$LOCAL_FILES_BACKUP_DIR"

if [ $? -eq 0 ]; then
    print_success "File backup completed successfully."
else
    print_error "File backup failed."
fi

echo ""
print_success "🎉 Full backup of 678photo.com completed!"
echo "Files are in: $LOCAL_FILES_BACKUP_DIR"
echo "Database dump is in: $LOCAL_DB_BACKUP_DIR"
