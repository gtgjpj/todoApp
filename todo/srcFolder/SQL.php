<?php

include_once("./config/DB.php");

define('BACKGROUND_IMAGE_HEADER_DATA', 'data:');

class SQL
{
    //プロジェクトのINSERT
    public static function insertProject($name)
    {
        $project_id = -1;
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
INSERT INTO
 `project`
(
 `owner_user_id`,
 `project_name`,
 `project_status`
)
SELECT
 MIN(`user_id`),
 :name,
 1
FROM
 `user`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':name' => $name));
            $project_id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            //MySQL/MariaDB 例外コード確認
            // https://dev.mysql.com/doc/refman/5.6/ja/error-messages-server.html
            // 42S02: Base table or view not found: 1146 Table 'xxxx.user' doesn't exist
            if ($pdo === null || $e->getCode() !== '42S02') {
                die();
            }

            try {
                $sql = <<< EOF
INSERT INTO
 `project`
(
 `project_name`,
 `project_status`
)
VALUES
(
 :name,
 1
)
EOF;
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':name' => $name));
                $project_id = $pdo->lastInsertId();
            }
            catch (PDOException $e) {
                die();
            }
        }

        $pdo = null;
        return $project_id;
    }

    //プロジェクトのSELECT
    public static function selectProject($project_id = -1)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql_project_id = "";
            if ($project_id >= 0) {
                $sql_project_id = "AND `project_id` = {$project_id}";
            }
            $sql = <<< EOF
SELECT
 `project_id`,
 `project_name`,
 `color`
FROM
 `project`
WHERE
 `project_status` = 1
{$sql_project_id}
ORDER BY
 `project_id`
EOF;
            $stmt = $pdo->query($sql);
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //プロジェクトのデリート
    public static function deleteProject($id)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
DELETE FROM
 `project`
WHERE
 `project_id` = :id
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':id' => $id));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }

    //プロジェクト内容更新
    public static function updateProject($project_id, $project_name, $color)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlSet = "";
            $array = array(':project_id' => $project_id);
            if($project_name != null && strlen($project_name) > 0){
                if(strlen($sqlSet) > 0) $sqlSet .= ",";
                $sqlSet .= " `project_name` = :project_name ";
                $array[":project_name"] = $project_name;
            }
            if($color != null && strlen($color) > 0){
                if(strlen($sqlSet) > 0) $sqlSet .= ",";
                $sqlSet .= " `color` = :color ";
                $array[":color"] = $color;
            }
            $sql = <<< EOF
UPDATE
 `project`
SET
{$sqlSet}
WHERE
 `project_id` = :project_id
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($array);
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->rowCount();
    }

    //プロジェクト選択時、タスクのINSERT
    public static function insertTaskToProject($project_id, $task_value, $completetion_date)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
INSERT INTO
 `task`
(
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
)
VALUES
(
 :project_id,
 :task_value,
 :completetion_date,
 1
)
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':project_id' => $project_id, ':task_value' => $task_value, ':completetion_date' => $completetion_date));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }

    //タスクのSELECT
    public static function selectTasks($project_id, $completetion_date_range_start, $completetion_date_range_end, $status)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlWhere = "";
            $array = array();
            if ($project_id != null && strlen($project_id) > 0) {
                if (strlen($sqlWhere) > 0) {
                    $sqlWhere .= "AND";
                }
                $sqlWhere .= " `project_id` = :project_id ";
                $array[":project_id"] = $project_id;
            }
            if ($completetion_date_range_start != null && strlen($completetion_date_range_start) > 0) {
                if (strlen($sqlWhere) > 0) {
                    $sqlWhere .= "AND";
                }
                $sqlWhere .= " `completetion_date` >= STR_TO_DATE(:completetion_date_range_start, '%Y-%m-%d') ";
                $array[":completetion_date_range_start"] = $completetion_date_range_start;
            }
            if ($completetion_date_range_end != null && strlen($completetion_date_range_end) > 0) {
                if (strlen($sqlWhere) > 0) {
                    $sqlWhere .= "AND";
                }
                $sqlWhere .= " `completetion_date` <= STR_TO_DATE(:completetion_date_range_end, '%Y-%m-%d') ";
                $array[":completetion_date_range_end"] = $completetion_date_range_end;
            }
            if ($status != null && strlen($status) > 0) {
                if (strlen($sqlWhere) > 0) {
                    $sqlWhere .= "AND";
                }
                $sqlWhere .= " `task_status` = :status ";
                $array[":status"] = $status;
            }
            if (strlen($sqlWhere) > 0) {
                $sqlWhere = "WHERE" . $sqlWhere;
            }
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
{$sqlWhere}
ORDER BY
 `completetion_date`,
 `task_id`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($array);
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //タスクを削除(単発)
    public static function deleteTask($task_id)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
DELETE FROM
 `task`
WHERE
 `task_id` = :id
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':id' => $task_id));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->rowCount();
    }

    //タスクの完了状態更新
    public static function updateTaskStatus($task_id, $task_status)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
UPDATE
 `task`
SET
 `task_status` = :task_status
WHERE
 `task_id` = :task_id
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':task_id' => $task_id, ':task_status' => $task_status));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }

    //未完了タスク数を取得
    public static function countIncompleteTasks($date_from, $date_to)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlWhere = "";
            $array = array();

            if ($date_from != null && $date_to != null){
                $sqlWhere = "`completetion_date` = STR_TO_DATE(:date_to, '%Y-%m-%d')";
                $array[":date_to"] = $date_to;
            }else if($date_from != null){
                $sqlWhere = "`completetion_date` >= STR_TO_DATE(:date_from, '%Y-%m-%d')";
                $array[":date_from"] = $date_from;
            }else{
                $sqlWhere = "`completetion_date` < STR_TO_DATE(:date_to, '%Y-%m-%d')";
                $array[":date_to"] = $date_to;
            }
            
            $sql = <<< EOF
SELECT COUNT(`task_id`) AS `count`
FROM
 `task`
WHERE
 `task_status` = 1
AND
 {$sqlWhere}
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($array);

        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //スキン情報取得
    public static function selectSkin()
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //MySQL/MariaDB GROUP_CONCAT()関数の許可する最大結果長を変更
            // https://dev.mysql.com/doc/refman/5.6/ja/server-system-variables.html#sysvar_group_concat_max_len
            if ($pdo->query("SET SESSION group_concat_max_len = 4294967295") === false) {
                die();
            }

            //スキン情報取得(背景画像データハッシュ以外)
            $sql = <<< EOF
SELECT
 `key`,
 `value`
FROM
 `skin`
WHERE
 `key` <> 'background-image'
ORDER BY
 `key`,
 `row_index`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //スキン情報取得(背景画像データハッシュのみ)
            //SHA256ハッシュ値をフロントエンドへ返すことにより、
            //背景画像が変更されたかどうか判定できるようにする
            $sql = <<< EOF
SELECT
 'background-image-hash' AS `key`,
 SHA2(GROUP_CONCAT(`value` ORDER BY `row_index` SEPARATOR ''), 256) AS `value`
FROM
 `skin`
WHERE
 `key` = 'background-image'
GROUP BY
 `key`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $array = array_merge($array, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $array;
    }

    //背景画像取得(スキン)
    public static function getBackgroundImage()
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //DBシステム変数設定(当セッションのみ)
            //MySQL/MariaDB GROUP_CONCAT()関数の許可する最大結果長を変更
            // https://dev.mysql.com/doc/refman/5.6/ja/server-system-variables.html#sysvar_group_concat_max_len
            if ($pdo->query("SET SESSION group_concat_max_len = 4294967295") === false) {
                //結果 (DBシステム変数設定失敗)
                return null;
            }
            $sql = <<< EOF
SELECT
 GROUP_CONCAT(`value` ORDER BY `row_index` SEPARATOR '') AS `value`
FROM
 `skin`
WHERE
 `key` = 'background-image'
GROUP BY
 `key`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        $array = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($array)) {
            //結果 (データなし)
            return null;
        }

        //65535文字毎に分割されているデータ文字列を連結する
        $data = implode("", $array);
        if (strlen($data) <= 0) {
            //結果 (データなし)
            return null;
        }

        //この行以降の処理は
        // https://developer.mozilla.org/ja/docs/Web/HTTP/Basics_of_HTTP/Data_URIs
        //に基づいて処理を行う
 
        //先頭が"data:"となっているか？
        //注:str_starts_with()関数(PHP8～)がPHP7ではサポートされていないためsubstrを用いる
        // if (!str_starts_with($data, BACKGROUND_IMAGE_HEADER_DATA)) {
        if (substr($data, 0, strlen(BACKGROUND_IMAGE_HEADER_DATA)) !== BACKGROUND_IMAGE_HEADER_DATA) {
            //結果 (無効データ)
            return null;
        }

        //","(カンマ)で文字列分割して配列要素が2になっているかどうか？
        $array = explode(",", $data);
        if (count($array) !== 2) {
            //結果 (無効データ)
            return null;
        }

        //ヘッダー文字列分割
        $header = explode(";", $array[0]);
        if (count($header) !== 1 && count($header) !== 2) {
            //結果 (ヘッダーデータ不良)
            return null;
        }

        //メディア(MIME)タイプ・画像データ取得 (BASE64文字列=>バイナリ変換)
        return array(
            'mediatype' => substr($header[0], strlen(BACKGROUND_IMAGE_HEADER_DATA)),
            'data' => base64_decode($array[1]));
    }

    //スキン情報更新
    public static function updateSkin($key, $value)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            //スキン情報削除
            $sql = <<< EOF
DELETE FROM
 `skin`
WHERE
 `key` = :key
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':key' => $key));

            //$value変数がブランクの場合はINSERT文を実行しない
            if ($value === null || strlen($value) <= 0) {
                $pdo->commit();
                return array('result' => true, 'hash' => null);
            }

            //スキン情報登録
            $sql = <<< EOF
INSERT INTO
 `skin`
(
 `user_id`,
 `key`,
 `row_index`,
 `value`
)
SELECT
 MIN(`user_id`),
 :key,
 :row_index,
 :value
FROM
 `user`
EOF;
            $stmt = $pdo->prepare($sql);
            $row_index = 0;
            for($offset = 0; $offset < strlen($value); $offset += 65535) {
                $stmt->execute(array(':key' => $key, ':row_index' => $row_index++, ':value' => substr($value, $offset, 65535)));
            }

            $pdo->commit();
        } catch (PDOException $e) {
            //MySQL/MariaDB 例外コード確認
            // https://dev.mysql.com/doc/refman/5.6/ja/error-messages-server.html
            // 42S02: Base table or view not found: 1146 Table 'xxxx.user' doesn't exist
            if ($pdo === null || $e->getCode() !== '42S02') {
                die();
            }

            try {
                if ($pdo->inTransaction()) {
                    // 一旦、ロールバックを行い別のSQLで再試行する
                    $pdo->rollBack();
                }
                $pdo->beginTransaction();

                //スキン情報削除
                $sql = <<< EOF
DELETE FROM
 `skin`
WHERE
 `key` = :key
EOF;
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':key' => $key));

                //$value変数がブランクの場合はINSERT文を実行しない
                if ($value === null || strlen($value) <= 0) {
                    $pdo->commit();
                    return array('result' => true, 'hash' => null);
                }

                //スキン情報登録
                $sql = <<< EOF
INSERT INTO
 `skin`
(
 `key`,
 `row_index`,
 `value`
)
VALUES
(
 :key,
 :row_index,
 :value
)
EOF;
                $stmt = $pdo->prepare($sql);
                $row_index = 0;
                for($offset = 0; $offset < strlen($value); $offset += 65535) {
                    $stmt->execute(array(':key' => $key, ':row_index' => $row_index++, ':value' => substr($value, $offset, 65535)));
                }

                $pdo->commit();
            } catch (PDOException $e) {
                die();
            }
        }

        $pdo = null;
        return array('result' => true, 'hash' => hash('sha256', $value));
    }
}

