プロジェクト名	attendance-app


# coachtech 勤怠管理アプリ【仮】

## 環境構築
（あとで書く：Dockerのビルドからマイグレーション、シーディングまで）

## 使用技術(実行環境)
- PHP 8.2
- Laravel 10.x
- MySQL 8.4

## ER図
（あとで書く：作成したER図の画像）

## URL
- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- Mailpit：http://localhost:8025/

## ログイン情報
- 一般ユーザー：user1@example.com / password
- 一般ユーザー：user2@example.com / password
- 管理者ユーザー：user3@example.com / password



【仮】
# お問い合わせフォーム

## 環境構築
Dockerビルド
1. git clone リンク
2. docker-compose up -d —build

＊ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせて docker-compose.yml ファイルを編集してください。

Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

## 使用技術
- PHP 8.0
- Laravel 10.0
- MySQL 8.0

## URL
- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/


リポジトリURL
　GitHub　https://github.com/yoko-77/更新予定


作成者
yoko-77
【仮】
