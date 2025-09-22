-- MySQL init script to ensure proper UTF8MB4 character set
SET character_set_client = utf8mb4;
SET character_set_connection = utf8mb4;  
SET character_set_results = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- Set default character set for the database
ALTER DATABASE wordpress_678 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ensure studio_shops table uses UTF8MB4
ALTER TABLE studio_shops CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE studio_shop_images CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;