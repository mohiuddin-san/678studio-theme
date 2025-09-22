#!/bin/bash

# =============================================================================
# 678 Studio Deploy Management System
# Unified management script for all deployment operations
# =============================================================================

set -e

# Script directory and configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$DEPLOY_DIR")"
CONFIG_FILE="$DEPLOY_DIR/config/deploy-config.json"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Helper functions
print_header() {
    echo -e "${PURPLE}╔════════════════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${PURPLE}║${NC}  🚀 678 Studio Deploy Management System  ${PURPLE}║${NC}"
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

# Load configuration
load_config() {
    if [[ ! -f "$CONFIG_FILE" ]]; then
        print_error "Configuration file not found: $CONFIG_FILE"
        exit 1
    fi
}

# Show usage
show_usage() {
    print_header
    echo -e "${BLUE}Usage:${NC} $0 <command> [options]"
    echo ""
    echo -e "${YELLOW}Available Commands:${NC}"
    echo ""
    echo -e "  ${GREEN}setup${NC}        Initial deployment setup and SSH configuration"
    echo -e "  ${GREEN}test${NC}         Test SSH connection and server accessibility"
    echo -e "  ${GREEN}deploy${NC}       Standard theme deployment to production"
    echo -e "  ${GREEN}deploy-full${NC}  Complete deployment (files + database)"
    echo -e "  ${GREEN}deploy-file${NC}  Deploy single file (requires FILE parameter)"
    echo -e "  ${GREEN}backup${NC}       Create server-side backup"
    echo -e "  ${GREEN}backup-local${NC} Download backup from server to local"
    echo -e "  ${GREEN}rollback${NC}     Rollback to previous deployment"
    echo -e "  ${GREEN}status${NC}       Show deployment status and configuration"
    echo -e "  ${GREEN}logs${NC}        Show deployment logs"
    echo -e "  ${GREEN}clean${NC}       Clean old backups and temporary files"
    echo ""
    echo -e "${YELLOW}Examples:${NC}"
    echo -e "  $0 setup              # Initial setup"
    echo -e "  $0 test               # Test connection"
    echo -e "  $0 deploy             # Deploy theme"
    echo -e "  $0 deploy-full        # Full deployment"
    echo -e "  $0 deploy-file FILE=style.css    # Deploy single file"
    echo -e "  $0 backup             # Create backup"
    echo -e "  $0 status             # Show status"
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo -e "  --dry-run             Preview deployment without executing"
    echo -e "  --skip-backup         Skip backup creation"
    echo -e "  --skip-build          Skip build process"
    echo -e "  --force               Force deployment without confirmation"
    echo -e "  --help                Show this help message"
    echo ""
}

# Setup deployment environment
setup_deployment() {
    print_step "Setting up deployment environment..."

    # Run SSH setup
    print_info "Configuring SSH settings..."
    if [[ -f "$SCRIPT_DIR/ssh-setup.sh" ]]; then
        bash "$SCRIPT_DIR/ssh-setup.sh"
    else
        print_error "SSH setup script not found"
        exit 1
    fi

    print_success "Deployment setup completed"
}

# Test connection
test_connection() {
    print_step "Testing deployment connection..."

    # Load environment
    local env_file="$PROJECT_ROOT/.env.deploy"
    if [[ ! -f "$env_file" ]]; then
        print_error "Environment file not found: $env_file"
        print_info "Run 'deploy-manager.sh setup' first"
        exit 1
    fi

    source "$env_file"

    # Test SSH connection
    print_info "Testing SSH connection to $SSH_HOST..."
    if ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_USER@$SSH_HOST" "echo 'Connection successful'"; then
        print_success "SSH connection test passed"
    else
        print_error "SSH connection test failed"
        exit 1
    fi

    # Test remote path access
    print_info "Testing remote path access..."
    if ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "test -d $REMOTE_PATH"; then
        print_success "Remote path accessible: $REMOTE_PATH"
    else
        print_error "Remote path not accessible: $REMOTE_PATH"
        exit 1
    fi

    print_success "All connection tests passed"
}

# Standard theme deployment
deploy_theme() {
    print_step "Starting theme deployment..."

    if [[ -f "$SCRIPT_DIR/deploy-to-prod.sh" ]]; then
        bash "$SCRIPT_DIR/deploy-to-prod.sh" "$@"
    else
        print_error "Theme deployment script not found"
        exit 1
    fi

    print_success "Theme deployment completed"
}

# Full deployment
deploy_full() {
    print_step "Starting full deployment..."

    if [[ -f "$SCRIPT_DIR/deploy-full.sh" ]]; then
        bash "$SCRIPT_DIR/deploy-full.sh" "$@"
    else
        print_error "Full deployment script not found"
        exit 1
    fi

    print_success "Full deployment completed"
}

# Single file deployment
deploy_file() {
    print_step "Starting single file deployment..."

    if [[ -z "$FILE" ]]; then
        print_error "FILE parameter is required"
        print_info "Usage: deploy-manager.sh deploy-file FILE=path/to/file"
        exit 1
    fi

    if [[ -f "$SCRIPT_DIR/deploy-single-file.sh" ]]; then
        bash "$SCRIPT_DIR/deploy-single-file.sh" "$FILE" "$@"
    else
        print_error "Single file deployment script not found"
        exit 1
    fi

    print_success "Single file deployment completed"
}

# Create backup
create_backup() {
    print_step "Creating server backup..."

    if [[ -f "$SCRIPT_DIR/server-backup.sh" ]]; then
        bash "$SCRIPT_DIR/server-backup.sh" "$@"
    else
        print_error "Server backup script not found"
        exit 1
    fi

    print_success "Server backup completed"
}

# Download backup to local
backup_local() {
    print_step "Downloading backup to local..."

    if [[ -f "$SCRIPT_DIR/backup-from-prod.sh" ]]; then
        bash "$SCRIPT_DIR/backup-from-prod.sh" "$@"
    else
        print_error "Local backup script not found"
        exit 1
    fi

    print_success "Local backup completed"
}

# Show deployment status
show_status() {
    print_step "Deployment Status"

    local env_file="$PROJECT_ROOT/.env.deploy"
    if [[ -f "$env_file" ]]; then
        source "$env_file"
        echo ""
        print_info "Configuration:"
        echo "  Host: $SSH_HOST"
        echo "  User: $SSH_USER"
        echo "  Port: $SSH_PORT"
        echo "  Remote Path: $REMOTE_PATH"
        echo ""
    else
        print_warning "No deployment configuration found"
        print_info "Run 'deploy-manager.sh setup' to configure"
        return
    fi

    # Show recent deployments
    local log_file="$DEPLOY_DIR/logs/deploy-operations.log"
    if [[ -f "$log_file" ]]; then
        print_info "Recent deployments:"
        tail -5 "$log_file" | while read line; do
            echo "  $line"
        done
    else
        print_info "No deployment logs found"
    fi

    echo ""
}

# Show deployment logs
show_logs() {
    print_step "Deployment Logs"

    local log_file="$DEPLOY_DIR/logs/deploy-operations.log"
    if [[ -f "$log_file" ]]; then
        if [[ "$1" == "--tail" ]]; then
            tail -f "$log_file"
        else
            tail -20 "$log_file"
        fi
    else
        print_info "No deployment logs found"
    fi
}

# Clean old files
clean_files() {
    print_step "Cleaning old deployment files..."

    # Clean old backups
    local backup_dir="$PROJECT_ROOT/backups"
    if [[ -d "$backup_dir" ]]; then
        print_info "Cleaning backups older than 30 days..."
        find "$backup_dir" -type f -mtime +30 -delete 2>/dev/null || true
        print_success "Old backups cleaned"
    fi

    # Clean deployment logs
    local log_file="$DEPLOY_DIR/logs/deploy-operations.log"
    if [[ -f "$log_file" ]]; then
        print_info "Rotating deployment logs..."
        if [[ $(wc -l < "$log_file") -gt 1000 ]]; then
            tail -500 "$log_file" > "${log_file}.tmp"
            mv "${log_file}.tmp" "$log_file"
            print_success "Deployment logs rotated"
        fi
    fi

    print_success "Cleanup completed"
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
            FILE=*)
                export FILE="${1#*=}"
                shift
                ;;
            *)
                break
                ;;
        esac
    done

    # Load configuration for most commands
    if [[ "$command" != "setup" && "$command" != "--help" && "$command" != "" ]]; then
        load_config
    fi

    # Execute command
    case "$command" in
        setup)
            setup_deployment
            ;;
        test)
            test_connection
            ;;
        deploy)
            deploy_theme "$@"
            ;;
        deploy-full)
            deploy_full "$@"
            ;;
        deploy-file)
            deploy_file "$@"
            ;;
        backup)
            create_backup "$@"
            ;;
        backup-local)
            backup_local "$@"
            ;;
        rollback)
            print_warning "Rollback functionality coming soon"
            ;;
        status)
            show_status
            ;;
        logs)
            show_logs "$@"
            ;;
        clean)
            clean_files
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