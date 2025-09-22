# 678 Studio データベースガイド

## 🗄️ データベース構成

### 基本情報
- **データベース名**: wordpress_678
- **文字セット**: UTF8MB4
- **照合順序**: utf8mb4_unicode_ci
- **エンジン**: InnoDB

### 接続情報（ローカル開発）
```
Host: localhost
Port: 3306
Database: wordpress_678
User: wp_user
Password: password
Root Password: rootpassword
```

## 📋 テーブル構成

### WordPress標準テーブル
- `wp_posts` - 投稿データ
- `wp_postmeta` - 投稿メタデータ
- `wp_users` - ユーザー情報
- `wp_usermeta` - ユーザーメタデータ
- `wp_options` - サイト設定
- `wp_terms` - タクソノミー用語
- `wp_term_taxonomy` - タクソノミー分類
- `wp_term_relationships` - 用語関係
- `wp_comments` - コメント
- `wp_commentmeta` - コメントメタデータ

### カスタムテーブル
- `studio_shops` - スタジオ店舗情報
- `studio_shop_images` - 店舗画像情報

## 🔧 データベース管理コマンド

### 基本操作
```bash
# データベース管理システムの使用
make database-manager COMMAND=<command>

# 初期セットアップ
make database-manager COMMAND=setup

# ステータス確認
make database-manager COMMAND=status

# MySQLシェルを開く
make database-manager COMMAND=shell
```

### バックアップ・復旧
```bash
# バックアップ作成
make database-manager COMMAND=backup

# 最新バックアップから復旧
make database-manager COMMAND=restore

# 特定のバックアップから復旧
make database-manager COMMAND=restore BACKUP=wordpress_678_backup_20241220_143000.sql.gz
```

### メンテナンス
```bash
# データベース最適化
make database-manager COMMAND=optimize

# 古いバックアップの削除
make database-manager COMMAND=clean

# データベースリセット（注意！）
make database-manager COMMAND=reset --force
```

## 📊 パフォーマンス監視

### 重要なメトリクス

#### データベースサイズ
```sql
SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'wordpress_678'
GROUP BY table_schema;
```

#### テーブルサイズ
```sql
SELECT
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'wordpress_678'
ORDER BY (data_length + index_length) DESC;
```

#### インデックス効率
```sql
SELECT
    table_name,
    index_name,
    column_name,
    cardinality
FROM information_schema.statistics
WHERE table_schema = 'wordpress_678'
ORDER BY cardinality DESC;
```

## 🛠️ 最適化手法

### 定期的な最適化
1. **週次**: テーブル最適化
2. **月次**: インデックス再構築
3. **四半期**: 統計情報更新

### パフォーマンスチューニング

#### 1. クエリ最適化
```sql
-- スロークエリログの確認
SHOW VARIABLES LIKE 'slow_query_log';
SHOW VARIABLES LIKE 'long_query_time';

-- プロセスリストの確認
SHOW PROCESSLIST;
```

#### 2. インデックス最適化
```sql
-- 未使用インデックスの確認
SELECT
    s.table_schema,
    s.table_name,
    s.index_name
FROM information_schema.statistics s
LEFT JOIN information_schema.index_statistics i
    ON s.table_schema = i.table_schema
    AND s.table_name = i.table_name
    AND s.index_name = i.index_name
WHERE s.table_schema = 'wordpress_678'
    AND i.index_name IS NULL;
```

#### 3. テーブル最適化
```sql
-- 自動最適化
OPTIMIZE TABLE wp_posts;
OPTIMIZE TABLE wp_postmeta;
OPTIMIZE TABLE wp_options;

-- 全テーブル最適化（管理スクリプト経由推奨）
-- make database-manager COMMAND=optimize
```

## 🔒 セキュリティ設定

### ユーザー権限管理

#### 開発環境用ユーザー
```sql
-- wp_user権限の確認
SHOW GRANTS FOR 'wp_user'@'localhost';

-- 必要最小限の権限設定
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER
ON wordpress_678.* TO 'wp_user'@'localhost';
```

#### 本番環境での注意点
1. **root権限の制限**
2. **不要ユーザーの削除**
3. **パスワードポリシーの強化**
4. **接続元IP制限**

### バックアップセキュリティ
```bash
# バックアップファイルの暗号化
gpg --symmetric --cipher-algo AES256 backup.sql

# 復号化
gpg --decrypt backup.sql.gpg > backup.sql
```

## 📈 容量管理

### ディスク使用量監視
```sql
-- データベース全体の使用量
SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Size (MB)'
FROM information_schema.tables
GROUP BY table_schema;

-- 大きなテーブルの特定
SELECT
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND((data_length / 1024 / 1024), 2) AS 'Data (MB)',
    ROUND((index_length / 1024 / 1024), 2) AS 'Index (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'wordpress_678'
ORDER BY (data_length + index_length) DESC;
```

### 容量削除方法
```sql
-- 不要なリビジョンの削除
DELETE FROM wp_posts WHERE post_type = 'revision';

-- スパムコメントの削除
DELETE FROM wp_comments WHERE comment_approved = 'spam';

-- 孤立したメタデータの削除
DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT ID FROM wp_posts);
DELETE FROM wp_commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM wp_comments);
```

## 🚨 トラブルシューティング

### よくある問題

#### 1. 文字化け問題
**症状**: 日本語が正しく表示されない
**原因**: 文字セットの不整合
**解決方法**:
```sql
-- データベース文字セット確認
SHOW VARIABLES LIKE 'character_set%';

-- テーブル文字セット確認
SHOW CREATE TABLE wp_posts;

-- 文字セット修正
ALTER TABLE wp_posts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 2. 接続エラー
**症状**: "Too many connections"エラー
**原因**: 接続数の上限超過
**解決方法**:
```sql
-- 現在の接続数確認
SHOW STATUS LIKE 'Threads_connected';
SHOW VARIABLES LIKE 'max_connections';

-- 接続プロセス確認
SHOW PROCESSLIST;

-- 不要な接続の終了
KILL [connection_id];
```

#### 3. パフォーマンス低下
**症状**: クエリ実行が遅い
**原因**: インデックス不足、統計情報の古さ
**解決方法**:
```sql
-- クエリ実行計画の確認
EXPLAIN SELECT * FROM wp_posts WHERE post_status = 'publish';

-- 統計情報の更新
ANALYZE TABLE wp_posts;

-- インデックスの追加
CREATE INDEX idx_post_status ON wp_posts(post_status);
```

### 緊急時対応

#### データ復旧手順
1. **即座の対応**: サービス停止
2. **状況確認**: エラーログの確認
3. **バックアップ復旧**: 最新の正常なバックアップから復旧
4. **データ検証**: 復旧後のデータ整合性確認
5. **サービス再開**: 動作確認後のサービス復旧

#### 連絡体制
- **システム管理者**: [連絡先]
- **開発チーム**: [連絡先]
- **ホスティング会社**: [連絡先]

## 📚 参考資料

### MySQL/MariaDB ドキュメント
- [MySQL 8.0 Reference Manual](https://dev.mysql.com/doc/refman/8.0/en/)
- [MariaDB Knowledge Base](https://mariadb.com/kb/en/)

### WordPress データベース
- [WordPress Database Description](https://wordpress.org/support/article/database-description/)
- [WordPress Performance Optimization](https://wordpress.org/support/article/optimization/)

### 最適化ツール
- [MySQL Tuner](https://github.com/major/MySQLTuner-perl)
- [Percona Toolkit](https://www.percona.com/software/database-tools/percona-toolkit)

---

**重要**: 本番環境での作業前には必ずバックアップを作成し、ステージング環境でテストを行ってください。