# メール送信問題のトラブルシューティングガイド

## 問題の概要

678 Studio のお問合せフォームとご予約フォームで以下の問題が発生している：

1. **企業側・お客様側の両方でメール本文が空になる**
2. **お客様側でメールが迷惑メール（スパム）フィルターに分類される**

## 問題の原因分析

### 1. メール本文が空になる原因

#### A. プラグイン設定の不備

- **AWS Email Plugin** の対象ページ設定 (`siaes_pages`) が不完全
- `studio-inquiry` と `studio-reservation` が設定されていない可能性
- プラグインの JavaScript (`form-handler.js`) が読み込まれていない

#### B. メールテンプレートの問題

- ショートコード置換が正常に動作していない
- テンプレート内の `[name]`, `[email]`, `[notes]` などが空文字に置換される
- フォームデータの収集・送信に失敗

#### C. フォーム送信ロジックの問題

- AJAX リクエストが正しく動作していない
- フォームデータが正しくシリアライズされていない
- ナンス（セキュリティトークン）の検証に失敗

### 2. 迷惑メール分類される原因

#### A. 送信者認証の未設定

- **SPF レコード**: DNS に適切な SPF レコードが設定されていない
- **DKIM 署名**: AWS SES で DKIM 認証が有効化されていない
- **DMARC ポリシー**: DMARC レコードが未設定

#### B. AWS SES 設定の問題

- 送信者メールアドレスが AWS SES で認証されていない
- サンドボックス モードのままで制限がかかっている
- レピュテーション（送信者評価）が低下している

## 解決手順

### Phase 1: 緊急対応（即座に実行可能）

#### 1.1 プラグイン JavaScript の強制読み込み

**実行済み**: `functions.php` に以下を追加済み：

```php
// AWS Email Plugin form handler (temporary fix)
wp_enqueue_script(
    'siaes-form-handler-fix',
    plugins_url('inquiry-to-aws-email/assets/js/form-handler.js'),
    array('jquery'),
    time(),
    true
);

// AJAX settings for form handler
wp_localize_script('siaes-form-handler-fix', 'siaes_ajax', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'page_id' => get_the_ID(),
    'nonce' => wp_create_nonce('siaes_form_nonce_' . get_the_ID())
));
```

#### 1.2 データベース接続問題の修正

**実行済み**:

- `docker-compose.yml` で MySQL SSL を無効化
- `wp-config.php` で SSL 設定を追加

### Phase 2: WordPress 管理画面での設定確認・修正

#### 2.1 AWS Email Plugin 設定の確認

WordPress 管理画面 → プラグイン → SIAES Email Plugin で確認：

1. **対象ページ設定**

   ```
   siaes_pages: studio-inquiry,studio-reservation
   ```

2. **各ページのメール設定**

   - **studio-inquiry** ページ:

     - 企業宛メール件名: 「お問合せフォームからの送信」
     - 企業宛メールテンプレート:

       ```
       以下の内容でお問合せがありました。

       ■お名前: [name]
       ■フリガナ: [kana]
       ■お電話番号: [contact]
       ■メールアドレス: [email]
       ■選択店舗: [shop-name]
       ■ご相談内容: [notes]

       以上
       ```

     - 顧客宛メール件名: 「お問合せを承りました」
     - 顧客宛メールテンプレート:

       ```
       [name] 様

       この度はお問合せをいただき、ありがとうございます。
       以下の内容で承りました。

       ■お名前: [name]
       ■メールアドレス: [email]
       ■選択店舗: [shop-name]
       ■ご相談内容: [notes]

       担当者より改めてご連絡差し上げます。

       ロクナナハチ(678)
       ```

   - **studio-reservation** ページ:
     - 企業宛・顧客宛の同様なテンプレート設定

#### 2.2 AWS 認証情報の設定確認

- AWS Access Key ID
- AWS Secret Access Key
- AWS リージョン (例: ap-northeast-1)
- 送信者メールアドレス

### Phase 3: AWS SES 設定とスパム対策

#### 3.1 AWS SES での送信者認証

1. **ドメイン認証** または **メールアドレス認証** を実行
2. **DKIM 署名** を有効化
3. **サンドボックス解除** の申請（必要に応じて）

#### 3.2 DNS レコードの設定

送信ドメインの DNS に以下を追加：

1. **SPF レコード**:

   ```
   v=spf1 include:amazonses.com ~all
   ```

2. **DKIM レコード**: AWS SES で提供される CNAME レコードを設定

3. **DMARC レコード**:
   ```
   v=DMARC1; p=quarantine; rua=mailto:admin@yourdomain.com
   ```

#### 3.3 メール内容の改善

- **From** フィールドに信頼できる送信者名を設定
- **Reply-To** を適切に設定
- HTML メール vs テキストメールの選択
- スパムフィルターに引っかかりやすいキーワードを避ける

### Phase 4: テスト・検証

#### 4.1 機能テスト

1. お問合せフォームからのテスト送信
2. ご予約フォームからのテスト送信
3. 企業側・顧客側両方でのメール受信確認
4. メール内容（件名・本文）の確認

#### 4.2 スパムフィルター テスト

1. Gmail, Outlook, Yahoo 等での受信テスト
2. スパムフォルダーの確認
3. メール認証状況の確認（SPF, DKIM, DMARC）

## 緊急連絡先・参考資料

### AWS SES 関連

- [AWS SES 設定ガイド](https://docs.aws.amazon.com/ses/)
- [DKIM 署名の設定](https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-email-authentication-dkim.html)

### DNS 設定関連

- SPF レコード生成ツール
- DMARC レコード生成ツール

### WordPress プラグイン関連

- プラグインのデバッグログ確認
- `wp-content/debug.log` の監視

## ログ・デバッグ情報

### 確認すべきログファイル

1. `wp-content/debug.log` - WordPress エラーログ
2. AWS SES の送信ログ - AWS コンソール
3. ブラウザの Developer Tools - JavaScript エラー
4. サーバーのメールログ（該当する場合）

### デバッグコマンド

```bash
# WordPress ログ確認
npm run wp-logs:errors

# AWS SES 送信テスト（AWS CLI）
aws ses send-email --source from@example.com --destination ToAddresses=to@example.com --message Subject={Data="Test"},Body={Text={Data="Test message"}}
```

## 更新履歴

- 2025-01-09: 初版作成 - メール送信問題の包括的な分析と解決手順
- 2025-01-09: functions.php への緊急修正を実施
- 2025-01-09: データベース接続問題を修正（MySQL 8.0 SSL 対応）

---

**注意**: このドキュメントは問題解決の進行と共に更新される予定です。最新情報については担当者に確認してください。
