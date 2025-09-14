# SSH設定例

## ~/.ssh/configファイルでの設定

SSH設定ファイルを使用することで、接続コマンドを簡潔にできます。

### 設定ファイルの場所
```bash
~/.ssh/config
```

### Egao X-Server用の設定例

```bash
# Egao X-Server - 678photo.com
Host egao-678photo
    HostName sv504.xbiz.ne.jp
    User xb592942
    Port 10022
    IdentityFile ~/.ssh/egao-salon_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3

# Egao X-Server - egao-salon.jp
Host egao-salon
    HostName sv504.xbiz.ne.jp
    User xb592942
    Port 10022
    IdentityFile ~/.ssh/egao-salon_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3

# Egao X-Server - 汎用設定
Host egao
    HostName sv504.xbiz.ne.jp
    User xb592942
    Port 10022
    IdentityFile ~/.ssh/egao-salon_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

### 設定後の接続方法

設定ファイルを作成した後は、以下のように簡潔に接続できます：

```bash
# 678photo.com用
ssh egao-678photo

# egao-salon.jp用
ssh egao-salon

# 汎用設定
ssh egao
```

### 設定ファイルのセキュリティ
```bash
# 設定ファイルの権限を適切に設定
chmod 600 ~/.ssh/config
```

## rsync設定例

### 基本的なrsyncコマンド
```bash
# ローカル → リモート
rsync -avz --delete \
    -e "ssh -i ~/.ssh/egao-salon_rsa -p 10022" \
    ./local-path/ \
    xb592942@sv504.xbiz.ne.jp:/home/xb592942/678photo.com/public_html/

# リモート → ローカル
rsync -avz \
    -e "ssh -i ~/.ssh/egao-salon_rsa -p 10022" \
    xb592942@sv504.xbiz.ne.jp:/home/xb592942/678photo.com/public_html/ \
    ./local-backup/
```

### SSH configを使用したrsync
```bash
# 設定ファイル使用時（より簡潔）
rsync -avz --delete ./local-path/ egao-678photo:/home/xb592942/678photo.com/public_html/
rsync -avz egao-678photo:/home/xb592942/678photo.com/public_html/ ./local-backup/
```

## scpコマンド例

### 基本的なscpコマンド
```bash
# ファイルをリモートにコピー
scp -i ~/.ssh/egao-salon_rsa -P 10022 \
    local-file.txt \
    xb592942@sv504.xbiz.ne.jp:/home/xb592942/678photo.com/public_html/

# ディレクトリを再帰的にコピー
scp -i ~/.ssh/egao-salon_rsa -P 10022 -r \
    ./local-directory/ \
    xb592942@sv504.xbiz.ne.jp:/home/xb592942/678photo.com/public_html/

# リモートからローカルにコピー
scp -i ~/.ssh/egao-salon_rsa -P 10022 \
    xb592942@sv504.xbiz.ne.jp:/home/xb592942/678photo.com/public_html/file.txt \
    ./local-file.txt
```

### SSH configを使用したscp
```bash
# 設定ファイル使用時
scp local-file.txt egao-678photo:/home/xb592942/678photo.com/public_html/
scp -r ./local-directory/ egao-678photo:/home/xb592942/678photo.com/public_html/
scp egao-678photo:/home/xb592942/678photo.com/public_html/file.txt ./local-file.txt
```

## 環境変数での設定

### bashrc/zshrcでのエイリアス設定
```bash
# ~/.bashrc または ~/.zshrcに追加
alias ssh-egao="ssh -i ~/.ssh/egao-salon_rsa -p 10022 xb592942@sv504.xbiz.ne.jp"
alias ssh-egao-678photo="ssh -i ~/.ssh/egao-salon_rsa -p 10022 xb592942@sv504.xbiz.ne.jp"

# WordPressディレクトリへの直接アクセス
alias ssh-egao-wp="ssh -i ~/.ssh/egao-salon_rsa -p 10022 xb592942@sv504.xbiz.ne.jp 'cd /home/xb592942/678photo.com/public_html && bash'"
```

### 環境変数の設定
```bash
# SSH接続情報
export EGAO_SSH_HOST="sv504.xbiz.ne.jp"
export EGAO_SSH_USER="xb592942"
export EGAO_SSH_PORT="10022"
export EGAO_SSH_KEY="~/.ssh/egao-salon_rsa"
export EGAO_WP_PATH="/home/xb592942/678photo.com/public_html"

# 接続コマンド
ssh -i $EGAO_SSH_KEY -p $EGAO_SSH_PORT $EGAO_SSH_USER@$EGAO_SSH_HOST
```