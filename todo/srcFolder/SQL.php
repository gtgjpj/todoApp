<?php

include_once("./config/DB.php");

class SQL
{
    public static function selectTaskUntilYesterday($date){
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
WHERE
 `completetion_date` <= STR_TO_DATE(:comp_date, '%Y-%m-%d')
ORDER BY
 `completetion_date`,
 `task_id`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':comp_date' => $date));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 完了済みのタスクを取得
     *
     * @return void
     */
    public static function selectCompletedTasks(){
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
WHERE
 `task_status` = 0
ORDER BY
 `completetion_date`,
 `task_id`
EOF;
            $stmt = $pdo->query($sql);
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 指定した日付以降のタスクを取得
     *
     * @param [type] $date
     * @return void
     */
    public static function selectTaskFromThatDay($date){
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
WHERE
 `completetion_date` >= STR_TO_DATE(:comp_date, '%Y-%m-%d')
ORDER BY
 `completetion_date`,
 `task_id`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':comp_date' => $date));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 日付を指定してタスクを取得
     *
     * @return void
     */
    public static function selectTaskFromOneDay($date){
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
WHERE
 `completetion_date` = STR_TO_DATE(:comp_date, '%Y-%m-%d')
ORDER BY
 `task_id`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':comp_date' => $date));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
        } catch (PDOException $e) {
            die();
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
			if ($project_id >= 0)
			{
				$sql_project_id = "AND `project_id` = {$project_id}";
			}
            $sql = <<< EOF
SELECT
 `project_id`,
 `project_name`
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

    //プロジェクト選択時、タスクのSELECT
    public static function selectTaskFromProject($project_id)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = <<< EOF
SELECT
 `task_id`,
 `project_id`,
 `task_value`,
 `completetion_date`,
 `task_status`
FROM
 `task`
WHERE
 `project_id` = :project_id
ORDER BY
 `completetion_date`
EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':project_id' => $project_id));
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
}
