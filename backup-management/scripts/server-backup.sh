#!/bin/bash

# =============================================================================
# Server-side Backup Script for 678photo.com
# このスクリプトはサーバー上で実行され、バックアップを作成します
# =============================================================================

set -e

# --- Configuration ---
SITE_PATH="/home/xb592942/678photo.com/public_html"
BACKUP_BASE_DIR="/home/xb592942/678photo.com/public_html/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="$BACKUP_BASE_DIR/678photo_backup_$TIMESTAMP"
DB_BACKUP_DIR="$BACKUP_DIR/database"
FILES_BACKUP_DIR="$BACKUP_DIR/files"

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

print_success() {
    echo -e "${GREEN}[SUCCESS] $1${NC}"
}

print_error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

print_warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

# --- Check if running on correct server ---
if [[ ! -f "$SITE_PATH/wp-config.php" ]]; then
    print_error "wp-config.php not found at $SITE_PATH"
fi

# --- Create backup directories ---
print_info "Creating backup directories..."
mkdir -p "$DB_BACKUP_DIR"
mkdir -p "$FILES_BACKUP_DIR"

# --- Get database credentials from wp-config.php ---
print_info "Reading database credentials..."
DB_NAME=$(grep "DB_NAME" "$SITE_PATH/wp-config.php" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_USER=$(grep "DB_USER" "$SITE_PATH/wp-config.php" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_PASSWORD=$(grep "DB_PASSWORD" "$SITE_PATH/wp-config.php" | sed "s/.*, *'\(.*\)'.*/\1/")
DB_HOST=$(grep "DB_HOST" "$SITE_PATH/wp-config.php" | sed "s/.*, *'\(.*\)'.*/\1/")

if [[ -z "$DB_NAME" || -z "$DB_USER" || -z "$DB_PASSWORD" || -z "$DB_HOST" ]]; then
    print_error "Failed to parse database credentials from wp-config.php"
fi

print_info "Database: $DB_NAME"

# --- 1. Backup Database ---
print_info "Starting database backup..."
DB_BACKUP_FILE="$DB_BACKUP_DIR/${DB_NAME}_backup_$TIMESTAMP.sql"

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$DB_BACKUP_FILE"

if [[ -s "$DB_BACKUP_FILE" ]]; then
    DB_SIZE=$(du -h "$DB_BACKUP_FILE" | cut -f1)
    print_success "Database backup completed: $DB_SIZE"
    
    # Compress database backup
    print_info "Compressing database backup..."
    gzip "$DB_BACKUP_FILE"
    COMPRESSED_SIZE=$(du -h "${DB_BACKUP_FILE}.gz" | cut -f1)
    print_success "Database compressed: $COMPRESSED_SIZE"
else
    print_error "Database backup failed"
fi

# --- 2. Backup Files ---
print_info "Starting file backup..."
print_info "This may take several minutes depending on site size..."

# Create archive of WordPress files (excluding cache and temp files)
tar -czf "$FILES_BACKUP_DIR/wordpress_files_$TIMESTAMP.tar.gz" \
    -C "$SITE_PATH" \
    --exclude="wp-content/cache" \
    --exclude="wp-content/uploads/cache" \
    --exclude="*.log" \
    --exclude="wp-content/updraft" \
    --exclude="wp-content/backup*" \
    --exclude="backups" \
    .

if [[ -f "$FILES_BACKUP_DIR/wordpress_files_$TIMESTAMP.tar.gz" ]]; then
    FILES_SIZE=$(du -h "$FILES_BACKUP_DIR/wordpress_files_$TIMESTAMP.tar.gz" | cut -f1)
    print_success "Files backup completed: $FILES_SIZE"
else
    print_error "Files backup failed"
fi

# --- 3. Create backup info file ---
print_info "Creating backup info file..."
cat > "$BACKUP_DIR/backup_info.txt" << EOF
678photo.com Backup Information
================================
Backup Date: $(date)
Backup Directory: $BACKUP_DIR

Database Information:
- Database Name: $DB_NAME
- Backup File: ${DB_NAME}_backup_$TIMESTAMP.sql.gz

Files Information:
- WordPress Path: $SITE_PATH
- Archive File: wordpress_files_$TIMESTAMP.tar.gz

To restore database:
gunzip ${DB_NAME}_backup_$TIMESTAMP.sql.gz
mysql -h $DB_HOST -u $DB_USER -p $DB_NAME < ${DB_NAME}_backup_$TIMESTAMP.sql

To restore files:
tar -xzf wordpress_files_$TIMESTAMP.tar.gz -C /target/directory/
EOF

# --- 4. Set permissions ---
# Set permissions only on the files we can access
if [[ -d "$BACKUP_DIR" ]]; then
    # Set directory permission first
    chmod 700 "$BACKUP_DIR" 2>/dev/null || print_warning "Could not set permissions on backup directory"
    
    # Set permissions on subdirectories
    if [[ -d "$DB_BACKUP_DIR" ]]; then
        chmod 700 "$DB_BACKUP_DIR" 2>/dev/null || true
        chmod 600 "$DB_BACKUP_DIR"/*.gz 2>/dev/null || true
    fi
    
    if [[ -d "$FILES_BACKUP_DIR" ]]; then
        chmod 700 "$FILES_BACKUP_DIR" 2>/dev/null || true
        chmod 600 "$FILES_BACKUP_DIR"/*.tar.gz 2>/dev/null || true
    fi
    
    # Set permission on info file
    if [[ -f "$BACKUP_DIR/backup_info.txt" ]]; then
        chmod 600 "$BACKUP_DIR/backup_info.txt" 2>/dev/null || true
    fi
fi

# --- 5. Clean old backups (keep last 5) ---
print_info "Cleaning old backups..."
cd "$BACKUP_BASE_DIR"
ls -1d 678photo_backup_* 2>/dev/null | head -n -5 | xargs -r rm -rf

# --- Summary ---
echo ""
print_success "🎉 Backup completed successfully!"
echo ""
echo "Backup Location: $BACKUP_DIR"
echo "Database: $(du -h "$DB_BACKUP_DIR"/*.gz | cut -f1)"
echo "Files: $(du -h "$FILES_BACKUP_DIR"/*.tar.gz | cut -f1)"
echo "Total Size: $(du -sh "$BACKUP_DIR" | cut -f1)"
echo ""
print_info "Backup files are stored on the server for safety."
print_info "Download them using scp or rsync if needed."