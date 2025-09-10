#!/usr/bin/env python3
import re

# Read the backup file
with open('backups/server_20250730_133159/database/xb592942_1qqor_backup_20250730_133159.sql', 'r', encoding='utf-8') as f:
    content = f.read()

# Find the INSERT INTO wp_posts line
insert_pattern = r"INSERT INTO `wp_posts` VALUES.*?;"
match = re.search(insert_pattern, content, re.DOTALL)

if match:
    insert_statement = match.group(0)
    
    # Split by individual record patterns
    records = re.findall(r'\([^)]*(?:\([^)]*\)[^)]*)*media_achievements[^)]*(?:\([^)]*\)[^)]*)*\)', insert_statement)
    
    print(f"Found {len(records)} media_achievements records")
    
    # Write the SQL for import
    with open('backups/media_achievements_import.sql', 'w', encoding='utf-8') as out:
        out.write("SET NAMES utf8mb4;\n")
        out.write("SET CHARACTER SET utf8mb4;\n\n")
        out.write("-- Media achievements from July 30 backup\n")
        if records:
            # Update URLs to localhost
            for i, record in enumerate(records):
                record = record.replace('https://678photo.com', 'http://localhost:8080')
                record = record.replace('http://localhost:8080/wp-content/uploads/2025/07/', 'http://localhost:8080/wp-content/uploads/2025/07/')
                if i == 0:
                    out.write("INSERT INTO wp_posts VALUES\n")
                out.write(record)
                if i < len(records) - 1:
                    out.write(",\n")
                else:
                    out.write(";\n")
            print("Media achievements SQL written to backups/media_achievements_import.sql")
        else:
            print("No records to write")
else:
    print("Could not find INSERT statement")