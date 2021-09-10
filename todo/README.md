# TODO アプリ「みんまるたすく」の構築について

本アプリは [グーグル拡張機能 Focus To-Do](https://www.focustodo.cn/) の機能縮小、アレンジ版として学習用途に開発したアプリになります。  
元アプリには、ポモドーロ支援機能なども含まれておりますので、興味ある方は是非ご覧ください。

本アプリではタスク管理機能のみに焦点を当てて開発しております。
純粋な TODO リストが欲しい方に使っていただけると幸いです。

## ファイル構成

階層はそのままにご利用ください。

1. srcFolder:ソースコードが複数入ったフォルダ
2. DB.php:
3. DB 設計.txt:必要なテーブルと、その設定を記載してあります。
   　命令文が記載してある為、該当データベースにて使用してください
4. base.css:スタイルシートの一つになります
5. jquery-3.5.1.min.js:jQuery の minify ファイルになります
6. create.sql:データベース内でインポートすることで、必要なテーブルが作成されます
7. alter.sql:2021/09/01 の更新で、テーブル構造が変わりました。  
   そのため以前にテーブルを作成していた場合、変更する際に用いる alter table 文になります。

#### srcFolder フォルダの中身について

1. SQL.php:使用する SQL 文をまとめた PHP ファイルです
2. index.php:メインページになります
3. main.css:スタイルシートの一つになります
4. main.js:Javascript コードです
5. post.php:非同期通信時に用いるコードになります
6. configフォルダ:DB接続設定が書かれたファイルが入っております

#### config フォルダの中身について

1.DB-example.php:PHP でデータベース接続時に用いるファイルの型。  
   各環境（MySQL など）の設定に合わせて変更をお願いします（XAMPP の初期値をベースにしてあります）  
   dsn:データベース名指定、ホスト名、ポート名  
   username:データベースのユーザ名  
   password:ユーザに該当するパスワード
   **※必ずDB.phpへリネームしてご利用ください**

### ローカルで XAMPP を使用する場合

[XAMPP](https://www.apachefriends.org/jp/index.html)をインストール。  
htdocs フォルダ内に todo フォルダをそのまま保存。  
localhost/todo/srcFolder/index.php  
にブラウザでアクセスすることにより起動できます。

※XAMPP を使用せず、LAMP 環境を用意する際の説明は省略させていただきます。  
（私は[こちらのチュートリアル](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04-ja)を用いてサーバーを用意して動作させています）
