#!/bin/bash

# =============================================================================
# 678 Studio Database Management System
# Unified management script for database operations
# =============================================================================

set -e

# Script directory and configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$DB_DIR")"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Database configuration
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="wordpress_678"
DB_USER="wp_user"
DB_PASS="password"
DB_ROOT_PASS="rootpassword"

# Docker configuration
DOCKER_COMPOSE_FILE="$PROJECT_ROOT/docker-compose.yml"
DB_CONTAINER="678-db-1"

# Helper functions
print_header() {
    echo -e "${PURPLE}╔════════════════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${PURPLE}║${NC}  🗄️ 678 Studio Database Management System  ${PURPLE}║${NC}"
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
    echo -e "${CYAN}ℹ️  $1${NC}"
}

# Show usage
show_usage() {
    print_header
    echo -e "${BLUE}Usage:${NC} $0 <command> [options]"
    echo ""
    echo -e "${YELLOW}Available Commands:${NC}"
    echo ""
    echo -e "  ${GREEN}setup${NC}        Initial database setup and configuration"
    echo -e "  ${GREEN}backup${NC}       Create database backup"
    echo -e "  ${GREEN}restore${NC}      Restore database from backup"
    echo -e "  ${GREEN}status${NC}       Show database status and information"
    echo -e "  ${GREEN}optimize${NC}     Optimize database tables and indexes"
    echo -e "  ${GREEN}clean${NC}        Clean old backups and temporary files"
    echo -e "  ${GREEN}reset${NC}        Reset database to initial state"
    echo -e "  ${GREEN}logs${NC}         Show database logs"
    echo -e "  ${GREEN}shell${NC}        Open MySQL shell"
    echo ""
    echo -e "${YELLOW}Examples:${NC}"
    echo -e "  $0 setup              # Initial setup"
    echo -e "  $0 backup             # Create backup"
    echo -e "  $0 restore            # Restore from latest backup"
    echo -e "  $0 restore BACKUP=backup_20241220.sql    # Restore specific backup"
    echo -e "  $0 status             # Show status"
    echo -e "  $0 optimize           # Optimize database"
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo -e "  --force               Force operation without confirmation"
    echo -e "  --help                Show this help message"
    echo ""
}

# Check if Docker is running
check_docker() {
    if ! docker ps >/dev/null 2>&1; then
        print_error "Docker is not running"
        print_info "Please start Docker and try again"
        exit 1
    fi
}

# Check if database container is running
check_db_container() {
    if ! docker ps | grep -q "$DB_CONTAINER"; then
        print_error "Database container is not running"
        print_info "Run 'make up' to start the development environment"
        exit 1
    fi
}

# Database setup
setup_database() {
    print_step "Setting up database configuration..."

    check_docker
    check_db_container

    # Apply initial SQL configuration
    local init_sql="$DB_DIR/config/mysql-init.sql"
    if [[ -f "$init_sql" ]]; then
        print_info "Applying initial database configuration..."
        docker exec -i "$DB_CONTAINER" mysql -u root -p"$DB_ROOT_PASS" < "$init_sql" || {
            print_warning "Some SQL commands may have failed (this is normal for existing databases)"
        }
        print_success "Database configuration applied"
    else
        print_warning "Initial SQL file not found: $init_sql"
    fi

    print_success "Database setup completed"
}

# Create database backup
backup_database() {
    print_step "Creating database backup..."

    check_docker
    check_db_container

    local backup_dir="$DB_DIR/backups"
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_file="$backup_dir/wordpress_678_backup_$timestamp.sql"

    # Create backup directory
    mkdir -p "$backup_dir"

    # Create backup
    print_info "Creating backup: $backup_file"
    docker exec "$DB_CONTAINER" mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$backup_file" || {
        print_error "Backup creation failed"
        exit 1
    }

    # Compress backup
    print_info "Compressing backup..."
    gzip "$backup_file"
    local compressed_file="${backup_file}.gz"

    # Show backup info
    local size=$(du -h "$compressed_file" | cut -f1)
    print_success "Backup created: $(basename "$compressed_file") ($size)"

    echo ""
    print_info "Backup location: $compressed_file"
    print_info "Backup size: $size"
}

# Restore database from backup
restore_database() {
    print_step "Restoring database from backup..."

    check_docker
    check_db_container

    local backup_dir="$DB_DIR/backups"
    local backup_file=""

    # Determine backup file
    if [[ -n "$BACKUP" ]]; then
        backup_file="$backup_dir/$BACKUP"
        if [[ ! -f "$backup_file" ]]; then
            backup_file="$backup_dir/${BACKUP}.gz"
        fi
    else
        # Find latest backup
        backup_file=$(find "$backup_dir" -name "*.sql.gz" -type f | sort -r | head -1)
    fi

    if [[ -z "$backup_file" || ! -f "$backup_file" ]]; then
        print_error "No backup file found"
        print_info "Available backups:"
        ls -la "$backup_dir"/*.sql.gz 2>/dev/null || print_info "No backups available"
        exit 1
    fi

    print_info "Restoring from: $(basename "$backup_file")"

    # Confirmation prompt
    if [[ "$FORCE" != "true" ]]; then
        echo ""
        print_warning "This will overwrite the current database!"
        read -p "Are you sure you want to continue? (y/N): " -n 1 -r
        echo ""

        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Restore cancelled by user"
            exit 0
        fi
    fi

    # Restore database
    print_info "Restoring database..."
    if [[ "$backup_file" == *.gz ]]; then
        zcat "$backup_file" | docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
    else
        docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$backup_file"
    fi

    print_success "Database restored successfully"
}

# Show database status
show_status() {
    print_step "Database Status"

    check_docker
    check_db_container

    echo ""
    print_info "Container Status:"
    docker ps | grep "$DB_CONTAINER" | awk '{print "  Container: " $1 "  Status: " $7 "  Created: " $4 " " $5 " " $6}'

    echo ""
    print_info "Database Information:"

    # Database size
    local db_size=$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" -e "
        SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Database Size (MB)'
        FROM information_schema.tables
        WHERE table_schema = '$DB_NAME';" -s -N)

    echo "  Database: $DB_NAME"
    echo "  Size: ${db_size} MB"

    # Table count
    local table_count=$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" -e "
        SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME';" -s -N)

    echo "  Tables: $table_count"

    # Connection test
    echo ""
    print_info "Connection Test:"
    if docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
        print_success "Database connection successful"
    else
        print_error "Database connection failed"
    fi

    # Recent backups
    echo ""
    print_info "Recent Backups:"
    local backup_dir="$DB_DIR/backups"
    if [[ -d "$backup_dir" ]]; then
        ls -la "$backup_dir"/*.sql.gz 2>/dev/null | tail -5 | while read line; do
            echo "  $line"
        done
    else
        print_info "No backups found"
    fi
}

# Optimize database
optimize_database() {
    print_step "Optimizing database..."

    check_docker
    check_db_container

    print_info "Analyzing and optimizing tables..."

    # Get all tables
    local tables=$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" -e "
        SELECT table_name FROM information_schema.tables
        WHERE table_schema = '$DB_NAME';" -s -N)

    # Optimize each table
    for table in $tables; do
        print_info "Optimizing table: $table"
        docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" -e "OPTIMIZE TABLE $DB_NAME.$table;" >/dev/null
    done

    print_success "Database optimization completed"
}

# Clean old backups
clean_backups() {
    print_step "Cleaning old backups..."

    local backup_dir="$DB_DIR/backups"
    local retention_days=30

    if [[ -d "$backup_dir" ]]; then
        print_info "Removing backups older than $retention_days days..."
        local removed_count=$(find "$backup_dir" -name "*.sql.gz" -type f -mtime +$retention_days -delete -print | wc -l)
        print_success "Removed $removed_count old backup files"
    else
        print_info "No backup directory found"
    fi
}

# Reset database
reset_database() {
    print_step "Resetting database..."

    if [[ "$FORCE" != "true" ]]; then
        echo ""
        print_warning "This will completely reset the database to initial state!"
        print_warning "All data will be lost!"
        read -p "Are you sure you want to continue? (y/N): " -n 1 -r
        echo ""

        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Reset cancelled by user"
            exit 0
        fi
    fi

    check_docker

    print_info "Stopping and removing database container..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" stop db
    docker-compose -f "$DOCKER_COMPOSE_FILE" rm -f db

    print_info "Restarting database with fresh data..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" up -d db

    # Wait for database to be ready
    print_info "Waiting for database to be ready..."
    sleep 10

    setup_database

    print_success "Database reset completed"
}

# Show database logs
show_logs() {
    print_step "Database Logs"

    check_docker
    check_db_container

    if [[ "$1" == "--tail" ]]; then
        docker logs -f "$DB_CONTAINER"
    else
        docker logs --tail=50 "$DB_CONTAINER"
    fi
}

# Open MySQL shell
open_shell() {
    print_step "Opening MySQL shell..."

    check_docker
    check_db_container

    print_info "Connecting to MySQL as $DB_USER..."
    docker exec -it "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
}

# Main execution
main() {
    local command="$1"
    shift

    # Parse global options
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_usage
                exit 0
                ;;
            --force)
                export FORCE="true"
                shift
                ;;
            BACKUP=*)
                export BACKUP="${1#*=}"
                shift
                ;;
            *)
                break
                ;;
        esac
    done

    # Execute command
    case "$command" in
        setup)
            setup_database
            ;;
        backup)
            backup_database
            ;;
        restore)
            restore_database
            ;;
        status)
            show_status
            ;;
        optimize)
            optimize_database
            ;;
        clean)
            clean_backups
            ;;
        reset)
            reset_database
            ;;
        logs)
            show_logs "$@"
            ;;
        shell)
            open_shell
            ;;
        ""|--help)
            show_usage
            ;;
        *)
            print_error "Unknown command: $command"
            echo ""
            show_usage
            exit 1
            ;;
    esac
}

# Check if script is being sourced or executed
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi