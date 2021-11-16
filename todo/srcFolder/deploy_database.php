<?php

include_once('./config/DB.php');

date_default_timezone_set('Asia/Tokyo');

$result_array = array();

function execute($pdo, $sql) {
    global $result_array;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        array_push($result_array, array(
            'sql' => $sql,
            'datetime' => date("Y-m-d H:i:s"),
            'result' => false,
            'message' => 'データベース未接続'));
        return;
    }

    $result = false;
    $message = null;
    try {
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
    } catch (PDOException $e) {
        $message = $e->getMessage();
    }

    array_push($result_array, array(
        'sql' => $sql,
        'datetime' => date("Y-m-d H:i:s"),
        'result' => $result,
        'message' => $message));
}

//DB接続
$pdo = null;
$sql = '-- CONNECT';
$result = false;
$message = null;
try {
    $pdo = new PDO(DB::dsn, DB::username, DB::password);
    $result = true;
} catch (PDOException $e) {
    $message = $e->getMessage();
}
array_push($result_array, array(
    'sql' => '-- CONNECT',
    'datetime' => date("Y-m-d H:i:s"),
    'result' => $result,
    'message' => $message));

try {
    if ($pdo !== null) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
}


//**** ver1.3.1 (2021/08/30 18:17 JST) ****
//初期テーブル作成

//プロジェクトテーブル作成
$sql = <<< EOF
-- projectテーブル作成
CREATE TABLE
  `project`
(
  `project_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_name` varchar(30),
  `project_status` int(1)
);
EOF;
execute($pdo, $sql);

//タスクテーブル作成
$sql = <<< EOF
-- taskテーブル作成
CREATE TABLE
  `task`
(
  `task_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_id` int(11),
  `task_value` varchar(30),
  `completetion_date` date,
  `task_status` int(1)
);
EOF;
execute($pdo, $sql);


//**** ver1.3.2 (2021/09/01 18:55 JST) ****
//タスクテーブル project_id列 に外部キー制約を追加

//タスクテーブル project_id列 NOT NULL 制約追加
$sql = <<< EOF
-- taskテーブル project_id列 NOT NULL 制約追加
ALTER TABLE `task` MODIFY `project_id` int(11) NOT NULL;
EOF;
execute($pdo, $sql);

//タスクテーブルproject_id列 外部キー制約追加
$sql = <<< EOF
-- taskテーブル project_id列 外部キー制約追加
ALTER TABLE `task` ADD FOREIGN KEY `fk_project_id`(`project_id`)
  REFERENCES `project`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOF;
execute($pdo, $sql);


//**** ver1.6.0 (2021/09/29 18:21 JST) ****
//プロジェクトの色分けを実装

//プロジェクトテーブル color列 追加
$sql = <<< EOF
-- projectテーブル color列 追加
ALTER TABLE `project` ADD `color` varchar(30);
EOF;
execute($pdo, $sql);


//**** ver1.7.0 (2021/10/29 18:21 JST) ****
//スキン設定

//スキンテーブル作成
$sql = <<< EOF
-- skinテーブル作成
CREATE TABLE
  `skin`
(
  `key` varchar(30) NOT NULL,
  `row_index` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY(`key`, `row_index`)
);
EOF;
execute($pdo, $sql);

$pdo = null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>To-Do [Deploy Database]</title>
    <style>
        .sql {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <pre class="sql">
<?php foreach ($result_array as $result) {
    echo htmlspecialchars($result['sql']) . PHP_EOL;
    echo '-- =&gt; ' . $result['datetime']
        . ' ** ' . ($result['result'] ? '成功' : '失敗') . ' **' . PHP_EOL;
    if ($result['message'] !== null) {
        echo '-- ' . htmlspecialchars($result['message']) . PHP_EOL;
    }
    echo PHP_EOL;
} ?>
    </pre>
</body>

</html>
