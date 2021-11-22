<?php

include_once('./config/DB.php');

date_default_timezone_set('Asia/Tokyo');

$pdo = null;
$result_array = array();

//SQL実行結果
abstract class Result
{
    public const COMMENT = -2;
    public const SKIPPED = -1;
    public const FAILURE = 0;
    public const SUCCESS = 1;
}

//SQL実行
function execute($sql) {
    global $pdo;
    global $result_array;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        array_push($result_array, array(
            'sql' => $sql,
            'datetime' => date("Y-m-d H:i:s"),
            'result' => Result::SKIPPED,
            'message' => 'データベース未接続のためスキップしました'));
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
        'result' => $result ? Result::SUCCESS : Result::FAILURE,
        'message' => $message));
}

//SQL実行スキップ
function skipExecute($sql, $message = null) {
    global $result_array;

    array_push($result_array, array(
        'sql' => $sql,
        'datetime' => date("Y-m-d H:i:s"),
        'result' => Result::SKIPPED,
        'message' => $message));
}

//テーブル存在確認
function existsTable($table_name) {
    global $pdo;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        return false;
    }

    //テーブル存在確認
    $sql = <<< EOF
-- テーブル存在確認
SELECT
  1
FROM
  `${table_name}`;
EOF;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // nothing to do
    }

    return false;
}

//テーブル列存在確認
function existsTableColumn($table_name, $column_name) {
    global $pdo;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        return false;
    }

    //テーブル列存在確認
    $sql = <<< EOF
-- テーブル列存在確認
SHOW COLUMNS FROM
  `${table_name}`
LIKE
  :column_name;
EOF;
    try {
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute(array(':column_name' => $column_name))) {
            return false;
        }
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($array) === 1) {
            return true;
        }
    } catch (PDOException $e) {
        // nothing to do
    }

    return false;
}

//テーブルインデックス存在確認
function existsTableIndex($table_name, $key_name) {
    global $pdo;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        return false;
    }

    //以下の実装ではユニーク制約と外部キー制約の対象列が重なった場合に正常動作しなかったためコメントアウト
/*
    //テーブルインデックス存在確認
    $sql = <<< EOF
-- テーブルインデックス存在確認
SHOW INDEX FROM
  `${table_name}`
WHERE
  key_name = :key_name;
EOF;
    try {
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute(array(':key_name' => $key_name))) {
            return false;
        }
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($array) >= 1) {
            return true;
        }
    } catch (PDOException $e) {
        // nothing to do
    }
 */
    //CREATE TABLE 文取得
    $sql = <<< EOF
-- CREATE TABLE 文取得
SHOW CREATE TABLE
  `${table_name}`
EOF;
    try {
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute()) {
            return false;
        }
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($array) !== 1) {
            return false;
        }

        $key_name = strtolower($key_name);
        foreach ($array[0] as $key => $value) {
            if (strtolower($key) !== 'create table') {
                continue;
            }

            //CREATE TABLE 文を行単位に分割する
            $lines = preg_split("/[\r\n]+/", $value);
            foreach ($lines as $line) {

                //主キー存在確認
                if ($key_name === 'primary'
                    && strpos($line, 'PRIMARY KEY') !== false) {
                    return true;
                }

                //ユニーク制約存在確認
                if (strpos($line, 'UNIQUE KEY') !== false
                    && strpos($line, "`${key_name}`") !== false) {
                    return true;
                }

                //外部キー制約存在確認
                if (strpos($line, "`${key_name}`") !== false
                    && strpos($line, 'FOREIGN KEY') !== false) {
                    return true;
                }
            }
            break;
        }
    } catch (PDOException $e) {
        // nothing to do
    }

    return false;
}

//テーブル行数取得
function getTableRowsCount($table_name) {
    global $pdo;

    //PDOオブジェクトがnullの場合はデータベース未接続とみなす
    if ($pdo === null) {
        return null;
    }

    //テーブル行数取得
    $sql = <<< EOF
-- テーブル行数取得
SELECT
  COUNT(*) AS `count`
FROM
  `${table_name}`;
EOF;
    try {
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute()) {
            return null;
        }
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        // nothing to do
    }

    return null;
}

//**** ver1.3.1 (2021/08/30 18:17 JST) ****
//初期テーブル作成
function deploy_ver1_3_1(){
    global $pdo;
    global $result_array;

    array_push($result_array, array(
        'sql' => '-- **** ver1.3.1 ****',
        'result' => Result::COMMENT,
        'message' => '初期テーブル作成'));

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
    if (!existsTable('project')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル がすでに存在するためスキップしました');
    }

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
    if (!existsTable('task')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル がすでに存在するためスキップしました');
    }
}

//**** ver1.3.2 (2021/09/01 18:55 JST) ****
//タスクテーブル project_id列 に外部キー制約を追加
function deploy_ver1_3_2(){
    global $pdo;
    global $result_array;

    array_push($result_array, array(
        'sql' => '-- **** ver1.3.2 ****',
        'result' => Result::COMMENT,
        'message' => 'タスクテーブル project_id列 に外部キー制約を追加'));

    //タスクテーブル project_id列 NOT NULL 制約追加
    $sql = <<< EOF
-- taskテーブル project_id列 NOT NULL 制約追加
ALTER TABLE `task` MODIFY `project_id` int(11) NOT NULL;
EOF;
    if (existsTableColumn('task', 'project_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル `project_id`列 が存在しないためスキップしました');
    }

    //タスクテーブル project_id列 外部キー制約追加
    $sql = <<< EOF
-- taskテーブル project_id列 外部キー制約追加
ALTER TABLE `task` ADD FOREIGN KEY `fk_project_id`(`project_id`)
  REFERENCES `project`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOF;
    if (!existsTableIndex('task', 'fk_project_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル `fk_project_id`外部キー制約 がすでに存在するためスキップしました');
    }
}

//**** ver1.6.0 (2021/09/29 18:21 JST) ****
//プロジェクトの色分けを実装
function deploy_ver1_6_0(){
    global $pdo;
    global $result_array;

    array_push($result_array, array(
        'sql' => '-- **** ver1.6.0 ****',
        'result' => Result::COMMENT,
        'message' => 'プロジェクトの色分けを実装'));

    //プロジェクトテーブル color列 追加
    $sql = <<< EOF
-- projectテーブル color列 追加
ALTER TABLE `project` ADD `color` varchar(30);
EOF;
    if (!existsTableColumn('project', 'color')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `color`列 がすでに存在するためスキップしました');
    }
}

//**** ver1.7.0 (2021/10/29 18:21 JST) ****
//スキン設定
function deploy_ver1_7_0(){
    global $pdo;
    global $result_array;

    array_push($result_array, array(
        'sql' => '-- **** ver1.7.0 ****',
        'result' => Result::COMMENT,
        'message' => 'スキン設定'));

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
    if (!existsTable('skin')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル がすでに存在するためスキップしました');
    }
}

//**** ver1.x.x (2021/xx/xx xx:xx JST) ****
//マルチユーザー対応
function deploy_ver1_X_X(){
    global $pdo;
    global $result_array;

    array_push($result_array, array(
        'sql' => '-- **** ver1.x.x ****',
        'result' => Result::COMMENT,
        'message' => 'マルチユーザー対応'));

    //ユーザーテーブル作成
    $sql = <<< EOF
-- userテーブル作成
CREATE TABLE
  `user`
(
  `user_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `user_name` varchar(30) NOT NULL,
  `password_hash` char(64),
  `display_name` varchar(30) NOT NULL,
  `is_available` boolean NOT NULL DEFAULT true,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password_updated_datetime` datetime,
  UNIQUE KEY `uk_user_name`(`user_name`)
);
EOF;
    if (!existsTable('user')) {
        execute($sql);
    } else {
        skipExecute($sql, '`user`テーブル がすでに存在するためスキップしました');
    }

    //adminユーザー追加
    $sql = <<< EOF
-- adminユーザー追加
INSERT INTO
  `user`
(
  `user_name`,
  `password_hash`,
  `display_name`
)
VALUES
(
  'admin',
  SHA2('admin', 256),
  'admin'
);
EOF;
    $result = getTableRowsCount('user');
    if ($result === null || $result <= 0) {
        //ユーザー数を取得できなかった場合や0件だった場合はadminユーザー追加を行う
        execute($sql);
    } else {
        skipExecute($sql, 'すでに何らかのユーザーが登録されているためスキップしました');
    }

    //プロジェクトテーブル owner_user_id列 追加
    $sql = <<< EOF
-- projectテーブル owner_user_id列 追加
ALTER TABLE `project` ADD `owner_user_id` int(11) AFTER `project_id`;
EOF;
    if (!existsTableColumn('project', 'owner_user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `owner_user_id`列 がすでに存在するためスキップしました');
    }

    //プロジェクトテーブル owner_user_id列 NULL行更新(最小ユーザーID設定)
    $sql = <<< EOF
-- projectテーブル owner_user_id列 NULL行更新(最小ユーザーID設定)
UPDATE
  `project`
SET
  `owner_user_id` = (SELECT MIN(`user_id`) FROM `user`)
WHERE
  `owner_user_id` IS NULL;
EOF;
    execute($sql);

    //プロジェクトテーブル owner_user_id列 NOT NULL 制約追加
    $sql = <<< EOF
-- projectテーブル owner_user_id列 NOT NULL 制約追加
ALTER TABLE `project` MODIFY `owner_user_id` int(11) NOT NULL;
EOF;
    if (existsTableColumn('project', 'owner_user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `owner_user_id`列 が存在しないためスキップしました');
    }

    //プロジェクトテーブル owner_user_id列 外部キー制約追加
    $sql = <<< EOF
-- projectテーブル owner_user_id列 外部キー制約追加
ALTER TABLE `project` ADD FOREIGN KEY `fk_owner_user_id`(`owner_user_id`)
  REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOF;
    if (!existsTableIndex('project', 'fk_owner_user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `fk_owner_user_id`外部キー制約 がすでに存在するためスキップしました');
    }

    //プロジェクトテーブル is_private列 追加
    $sql = <<< EOF
-- projectテーブル is_private列 追加
ALTER TABLE `project` ADD `is_private` boolean NOT NULL DEFAULT true AFTER `owner_user_id`;
EOF;
    if (!existsTableColumn('project', 'is_private')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `is_private`列 がすでに存在するためスキップしました');
    }

    //プロジェクトテーブル created_datetime列 追加
    $sql = <<< EOF
-- projectテーブル created_datetime列 追加
ALTER TABLE `project` ADD `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `color`;
EOF;
    if (!existsTableColumn('project', 'created_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `created_datetime`列 がすでに存在するためスキップしました');
    }

    //プロジェクトテーブル name_updated_datetime列 追加
    $sql = <<< EOF
-- projectテーブル name_updated_datetime列 追加
ALTER TABLE `project` ADD `name_updated_datetime` datetime AFTER `created_datetime`;
EOF;
    if (!existsTableColumn('project', 'name_updated_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `name_updated_datetime`列 がすでに存在するためスキップしました');
    }

    //プロジェクトテーブル status_updated_datetime列 追加
    $sql = <<< EOF
-- projectテーブル status_updated_datetime列 追加
ALTER TABLE `project` ADD `status_updated_datetime` datetime AFTER `name_updated_datetime`;
EOF;
    if (!existsTableColumn('project', 'status_updated_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`project`テーブル `status_updated_datetime`列 がすでに存在するためスキップしました');
    }

    //タスクテーブル created_datetime列 追加
    $sql = <<< EOF
-- taskテーブル created_datetime列 追加
ALTER TABLE `task` ADD `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `task_status`;
EOF;
    if (!existsTableColumn('task', 'created_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル `created_datetime`列 がすでに存在するためスキップしました');
    }

    //タスクテーブル value_updated_datetime列 追加
    $sql = <<< EOF
-- taskテーブル value_updated_datetime列 追加
ALTER TABLE `task` ADD `value_updated_datetime` datetime AFTER `created_datetime`;
EOF;
    if (!existsTableColumn('task', 'value_updated_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル `value_updated_datetime`列 がすでに存在するためスキップしました');
    }

    //タスクテーブル status_updated_datetime列 追加
    $sql = <<< EOF
-- taskテーブル status_updated_datetime列 追加
ALTER TABLE `task` ADD `status_updated_datetime` datetime AFTER `value_updated_datetime`;
EOF;
    if (!existsTableColumn('task', 'status_updated_datetime')) {
        execute($sql);
    } else {
        skipExecute($sql, '`task`テーブル `status_updated_datetime`列 がすでに存在するためスキップしました');
    }

    //スキンテーブル 主キー削除
    $sql = <<< EOF
-- skinテーブル 主キー削除
ALTER TABLE `skin` DROP PRIMARY KEY;
EOF;
    if (!existsTableColumn('skin', 'skin_id') && existsTableIndex('skin', 'primary')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `skin_id`列 がすでに存在するか 主キー が設定されていないためスキップしました');
    }

    //スキンテーブル skin_id列 追加
    $sql = <<< EOF
-- skinテーブル skin_id列 追加
ALTER TABLE `skin` ADD `skin_id` bigint AUTO_INCREMENT NOT NULL PRIMARY KEY FIRST;
EOF;
    if (!existsTableColumn('skin', 'skin_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `skin_id`列 がすでに存在するためスキップしました');
    }

    //スキンテーブル user_id列 追加
    $sql = <<< EOF
-- skinテーブル user_id列 追加
ALTER TABLE `skin` ADD `user_id` int(11) AFTER `skin_id`;
EOF;
    if (!existsTableColumn('skin', 'user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `user_id`列 がすでに存在するためスキップしました');
    }

    //スキンテーブル user_id列 NULL行更新(最小ユーザーID設定)
    $sql = <<< EOF
-- skinテーブル user_id列 NULL行更新(最小ユーザーID設定)
UPDATE
  `skin`
SET
  `user_id` = (SELECT MIN(`user_id`) FROM `user`)
WHERE
  `user_id` IS NULL;
EOF;
    execute($sql);

    //スキンテーブル user_id列 NOT NULL 制約追加
    $sql = <<< EOF
-- skinテーブル user_id列 NOT NULL 制約追加
ALTER TABLE `skin` MODIFY `user_id` int(11) NOT NULL;
EOF;
    if (existsTableColumn('skin', 'user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `user_id`列 が存在しないためスキップしました');
    }

    //スキンテーブル user_id, key, row_index列 ユニーク制約追加
    $sql = <<< EOF
-- skinテーブル user_id, key, row_index列 ユニーク制約追加
ALTER TABLE `skin` ADD UNIQUE KEY `uk_skin`(`user_id`, `key`, `row_index`);
EOF;
    if (!existsTableIndex('skin', 'uk_skin')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `uk_skin`ユニーク制約 がすでに存在するためスキップしました');
    }

    //スキンテーブル user_id列 外部キー制約追加
    $sql = <<< EOF
-- skinテーブル user_id列 外部キー制約追加
ALTER TABLE `skin` ADD FOREIGN KEY `fk_user_id`(`user_id`)
  REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOF;
    if (!existsTableIndex('skin', 'fk_user_id')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `fk_user_id`外部キー制約 がすでに存在するためスキップしました');
    }

    //スキンテーブル value列 NULL行削除
    $sql = <<< EOF
-- skinテーブル value列 NULL行削除
DELETE FROM
  `skin`
WHERE
  `value` IS NULL;
EOF;
    execute($sql);

    //スキンテーブル value列 NOT NULL 制約追加
    $sql = <<< EOF
-- skinテーブル value列 NOT NULL 制約追加
ALTER TABLE `skin` MODIFY `value` text NOT NULL;
EOF;
    if (existsTableColumn('skin', 'value')) {
        execute($sql);
    } else {
        skipExecute($sql, '`skin`テーブル `value`列 が存在しないためスキップしました');
    }

    //セッションテーブル作成
    $sql = <<< EOF
-- sessionテーブル作成
CREATE TABLE
  `session`
(
  `session_id` bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY `fk_session_user_id`(`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
);
EOF;
    if (!existsTable('session')) {
        execute($sql);
    } else {
        skipExecute($sql, '`session`テーブル がすでに存在するためスキップしました');
    }

    //トップページお知らせテーブル作成
    $sql = <<< EOF
-- notificationテーブル作成
CREATE TABLE
  `notification`
(
  `notification_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `notification_text` text NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text_updated_datetime` datetime
);
EOF;
    if (!existsTable('notification')) {
        execute($sql);
    } else {
        skipExecute($sql, '`notification`テーブル がすでに存在するためスキップしました');
    }

    //ユーザー通知テーブル作成
    $sql = <<< EOF
-- user_notificationテーブル作成
CREATE TABLE
  `user_notification`
(
  `user_notification_id` bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `notification_text` text NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text_updated_datetime` datetime,
  `is_read` boolean NOT NULL DEFAULT false,
  FOREIGN KEY `fk_user_notification_user_id`(`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
);
EOF;
    if (!existsTable('user_notification')) {
        execute($sql);
    } else {
        skipExecute($sql, '`user_notification`テーブル がすでに存在するためスキップしました');
    }

    //タスクに対するリアクションテーブル作成
    $sql = <<< EOF
-- user_reactionテーブル作成
CREATE TABLE
  `user_reaction`
(
  `user_reaction_id` bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_icon` varchar(64) NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_reaction`(`task_id`, `user_id`, `material_icon`),
  FOREIGN KEY `fk_user_reaction_task_id`(`task_id`)
    REFERENCES `task`(`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY `fk_user_reaction_user_id`(`user_id`)
    REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
);
EOF;
    if (!existsTable('user_reaction')) {
        execute($sql);
    } else {
        skipExecute($sql, '`user_reaction`テーブル がすでに存在するためスキップしました');
    }
}

//DB接続
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
    'result' => $result ? Result::SUCCESS : Result::FAILURE,
    'message' => $message));

try {
    if ($pdo !== null) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    // nothing to do
}

//デプロイ
deploy_ver1_3_1();
deploy_ver1_3_2();
deploy_ver1_6_0();
deploy_ver1_7_0();
deploy_ver1_X_X();

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
<?php
foreach ($result_array as $result) {
    echo htmlspecialchars($result['sql']) . PHP_EOL;
    if ($result['result'] !== Result::COMMENT) {
        echo '-- =&gt; ' . $result['datetime'] . ' ** ';
        switch ($result['result']) {
            case Result::SKIPPED:
                echo 'スキップ';
                break;
            case Result::FAILURE:
                echo '失敗';
                break;
            case Result::SUCCESS:
                echo '成功';
                break;
            default:
                echo '不明';
        }
        echo ' **' . PHP_EOL;
    }
    if ($result['message'] !== null) {
        echo '-- =&gt; ' . htmlspecialchars($result['message']) . PHP_EOL;
    }
    echo PHP_EOL;
} ?>
    </pre>
</body>

</html>
