# COACHTECH フリマアプリ

フリマ形式の商品出品・購入ができる Laravel アプリケーションです。ログイン/会員登録/メール認証、商品一覧・詳細、出品、購入（Stripe 決済）、コメント/お気に入り、マイページを提供します。

## 機能

- ユーザー登録/ログイン/メール認証
- 商品一覧・検索・詳細表示
- 出品（画像アップロード/カテゴリ/状態/価格）
- 購入フロー（Stripe Checkout）
- コメント/お気に入り
- マイページ（購入/出品の切替、プロフィール編集）

## 環境構築

**Dockerビルド**

1. `git clone git@github.com:nekomajin-1017/exam-frema.git`
2. `cd exam-frema`
3. DockerDesktopアプリを立ち上げる
4. `docker compose up -d --build`

**Laravel環境構築**
1. `docker compose exec php bash`
2. `composer install`
3. `cp .env.example .env`
4. `.env` に以下の環境変数を追加
```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
STRIPE_KEY=pk_test_(発行されたkeyを入力)
STRIPE_SECRET=sk_test_(発行されたkeyを入力)
```
5. メール認証確認用（Mailhog）
```text
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="app@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```
6. アプリケーションキーの作成
```bash
php artisan key:generate
```
7. マイグレーション・シーディングの実行
```bash
php artisan migrate --seed
```
8. 画像保存用のシンボリックリンク作成
```bash
php artisan storage:link
```

**使用技術(実行環境)**
- PHP: 8.1
- Laravel: 8.x
- MySQL: 8.0.26
- nginx: 1.21.1
- Stripe Checkout
- Laravel Fortify
- Mailhog

## ER図

![ER図](er.png)

## URL

- アプリ: `http://localhost/`
- ログイン: `http://localhost/login`
- 会員登録: `http://localhost/register`
- phpMyAdmin: `http://localhost:8080/`
- Mailhog（認証メール）: `http://localhost:8025`

## テストユーザー

- 出品者: `seller@example.com` / `password`
- 購入者: `buyer@example.com` / `password`
<br>※ 初回ログイン時、「認証メールを再送する」ボタンを押してください。

## テスト

```bash
docker compose exec -T php vendor/bin/phpunit
```
