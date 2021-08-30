# TODOアプリ「みんまるたすく」の構築について
本アプリは [グーグル拡張機能 Focus To-Do](https://www.focustodo.cn/) の機能縮小、アレンジ版として学習用途に開発したアプリになります。  
元アプリには、ポモドーロ支援機能なども含まれておりますので、興味ある方は是非ご覧ください。

本アプリではタスク管理機能のみに焦点を当てて開発しております。
純粋なTODOリストが欲しい方に使っていただけると幸いです。

## ファイル構成
階層はそのままにご利用ください。
1. srcFolder:ソースコードが複数入ったフォルダ
2. DB.php:PHPでデータベース接続時に用いるファイル。  
各環境（MySQLなど）の設定に合わせて変更をお願いします（XAMPPの初期値をベースにしてあります)  
      dsn:データベース名指定、ホスト名、ポート名  
username:データベースのユーザ名  
password:ユーザに該当するパスワード
3. DB設計.txt:必要なテーブルと、その設定を記載してあります。
　命令文が記載してある為、該当データベースにて使用してください
4. base.css:スタイルシートの一つになります
5. jquery-3.5.1.min.js:jQueryのminifyファイルになります
6. create.sql:データベース内でインポートすることで、必要なテーブルが作成されます

#### srcFolderフォルダの中身について
1. SQL.php:使用するSQL文をまとめたPHPファイルです
2. index.php:メインページになります
3. main.css:スタイルシートの一つになります
4. main.js:Javascriptコードです
5. post.php:非同期通信時に用いるコードになります

### ローカルでXAMPPを使用する場合
[XAMPP](https://www.apachefriends.org/jp/index.html)をインストール。  
htdocsフォルダ内にtodoフォルダをそのまま保存。  
localhost/todo/srcFolder/index.php  
にブラウザでアクセスすることにより起動できます。

※XAMPPを使用せず、LAMP環境を用意する際の説明は省略させていただきます。  
（私は[こちらのチュートリアル](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04-ja)を用いてサーバーを用意して動作させています）
