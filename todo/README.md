# TODO アプリ「みんまるたすく」の構築について

本アプリは [グーグル拡張機能 Focus To-Do](https://www.focustodo.cn/) の機能縮小、アレンジ版として学習用途に開発したアプリになります。  
元アプリには、ポモドーロ支援機能なども含まれておりますので、興味ある方は是非ご覧ください。

本アプリではタスク管理機能のみに焦点を当てて開発しております。
純粋な TODO リストが欲しい方に使っていただけると幸いです。

### 2021/09/25更新  
ver1.5.0にて魔王魂様(https://maou.audio/) よりお借りした効果音を実装しました。  
右上ベルアイコンを1回押すとシンプルな音に、もう一度押すと豪華な音に、もう一度押すと初期設定のミュートに戻ります。

## ファイル構成

階層はそのままにご利用ください。

1. base.css:スタイルシートの一つになります
2. class.puml:PlantUML で記述したクラス図になります
3. erd.puml:PlantUML で記述したER図になります
4. knockout-3.5.1.js:MVVM 実装に用いております JavaScript ライブラリとなります
5. jquery-3.5.1.min.js:jQuery の minify ファイルになります
6. srcFolder フォルダ:みんまるたすくの主要機能を実装したファイルが入っております

#### srcFolder フォルダの中身について

1. SQL.php:使用する SQL 文をまとめた PHP ファイルです
2. deploy_database.php:データベース内のテーブル作成・更新を行います。<br />当アプリを最新バージョンへ更新した際は、あらかじめ下記の config/DB.php を準備して頂いた上でWebブラウザで deploy_database.php へアクセスしてください。
3. index.php:メインページになります
4. main.css:スタイルシートの一つになります
5. main.js:Javascript コードです
6. post.php:非同期通信時に用いるコードになります
7. configフォルダ:DB接続設定が書かれたファイルが入っております

#### config フォルダの中身について

1.DB-example.php:PHP でデータベース接続時に用いるファイルの型。<br />各環境（MySQL など）の設定に合わせて変更をお願いします（XAMPP の初期値をベースにしてあります）  
   * dsn:データベース名指定、ホスト名、ポート名  
   * username:データベースのユーザ名  
   * password:ユーザに該当するパスワード

   **※必ずDB.phpへリネームしてご利用ください**
   
### 使用ライブラリ等  
- [jQuery](https://jquery.com/)  
- [darkmode.js](https://darkmodejs.learn.uno/)  
- [Knockout](https://knockoutjs.com/)  

### ローカルで XAMPP を使用する場合

[XAMPP](https://www.apachefriends.org/jp/index.html)をインストール。  
htdocs フォルダ内に todo フォルダをそのまま保存。  
localhost/todo/srcFolder/index.php  
にブラウザでアクセスすることにより起動できます。

※XAMPP を使用せず、LAMP 環境を用意する際の説明は省略させていただきます。  
（私は[こちらのチュートリアル](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04-ja)を用いてサーバーを用意して動作させています）
