# TODO アプリ「みんまるたすく」の構築について

本アプリは [グーグル拡張機能 Focus To-Do](https://www.focustodo.cn/) の機能縮小、アレンジ版として学習用途に開発したアプリになります。
元アプリには、ポモドーロ支援機能なども含まれておりますので、興味ある方は是非ご覧ください。

本アプリではタスク管理機能のみに焦点を当てて開発しております。
純粋な TODO リストが欲しい方に使っていただけると幸いです。

### 2022/11/14更新
ver.1.7.1 ファイル構成変更
htdocsフォルダにWebサーバーでの公開対象コンテンツを集約しました。

### 2021/09/25更新
ver1.5.0にて魔王魂様(https://maou.audio/) よりお借りした効果音を実装しました。
右上ベルアイコンを1回押すとシンプルな音に、もう一度押すと豪華な音に、もう一度押すと初期設定のミュートに戻ります。

## ファイル構成

	├── README.md      : 当ファイル
	├── documents
	│   ├── class.puml : クラス図 (PlantUML形式)
	│   └── erd.puml   : ER図 (PlantUML形式)
	├── htdocs         : みんまるたすく本体 (PHP, CSS, JavaScript, 他)
	│   ├── SQL.php    : 使用する SQL 文をまとめた PHP ファイル
	│   ├── config
	│   │   └── DB-example.php : DB接続設定のサンプルファイル
	│   ├── deploy_database.php : データベース内のテーブル作成・更新を行います
	│   ├── index.php : メインページになります
	│   ├── post.php : 非同期通信時に用いるコードになります
	│   └── static
	│       ├── css
	│       │   ├── base.css : ベースとなるスタイルシート定義ファイル
	│       │   └── main.css : 詳細なスタイルシート定義ファイル
	│       ├── js
	│       │   ├── jquery-3.5.1.min.js : jQuery の minify ファイル
	│       │   ├── knockout-3.5.1.js : MVVM 実装用 JavaScript ライブラリ
	│       │   └── main.js : Javascript 本体コード
	│       └── media
	│           ├── great.mp3 : タスク完了時効果音ファイル(豪華バージョン)
	│           └── simple.mp3 : タスク完了時効果音ファイル(シンプルバージョン)
	└── test
	    └── js
	        ├── example.html : JavaScript単体テストサンプル (テスト実行ページ)
	        ├── example.js : JavaScript単体テストサンプル (単体テスト実装)
	        ├── viewmodel.html : JavaScript単体テスト (テスト実行ページ)
	        └── viewmodel.js : JavaScript単体テスト (単体テスト実装)

htdocsディレクトリ以下の階層はそのままご利用ください。

#### htdocs/config フォルダの中身について

DB-example.php : PHP でデータベース接続設定サンプルファイル
各環境（MySQL など）の設定に合わせて変更をお願いします（XAMPP の初期値をベースにしてあります）  
   * dsn : データベース名指定、ホスト名、ポート名
   * username : データベースのユーザ名
   * password : ユーザに該当するパスワード

   **※必ずDB.phpへリネームしてご利用ください**
   
### 使用ライブラリ等
- [jQuery](https://jquery.com/)  
- [darkmode.js](https://darkmodejs.learn.uno/)
- [Knockout](https://knockoutjs.com/)  

### ローカルで XAMPP を使用する場合

[XAMPP](https://www.apachefriends.org/jp/index.html)をインストール。
htdocs フォルダ内をそのままもしくはApache設定ファイル変更によりご利用下さい。
http://localhost/index.php 等でにブラウザでアクセスすることにより利用できます。

※XAMPP を使用せず、LAMP 環境を用意する際の説明は省略させていただきます。
（私は[こちらのチュートリアル](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04-ja)を用いてサーバーを用意して動作させています）

### docker compose を使用する場合
最初に docker コンテナを作成していない場合は、以下のコマンドを実行して下さい。
````
~/workspace/todoApp $ docker compose build
~/workspace/todoApp $ docker compose up -d
````
これで、バックグランドで docker コンテナが起動します。
````
~/workspace/todoApp $ docker compose down
````
のようなコマンドを実行しなければ、起動し続けます。PCを再起動しても自動的に docker コンテナが起動します。

* http://localhost:8080/  # htdocs ディレクトリ内のPHP実行結果が表示されます
* http://localhost:8081/  # phpMyAdmin が表示されます。ユーザー名 root パスワード password でログインできます。
