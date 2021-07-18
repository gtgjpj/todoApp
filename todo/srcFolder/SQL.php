<?php

include_once("../DB.php");

class SQL
{
    //プロジェクトのINSERT
    public static function insertProject($name)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO `project`(`project_name`, `project_status`) VALUES(:name, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':name' => $name));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }

    //プロジェクトのSELECT
    public static function selectProject()
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT `project_id`, `project_name` FROM `project` WHERE `project_status` = 1  ORDER BY `project_id` DESC Limit 1";
            $stmt = $pdo->query($sql);
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
        return $stmt;
    }

    //プロジェクトのデリート
    public static function deleteProject($id)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = 'DELETE FROM `project` WHERE `project_id`= :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':id' => $id));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }

    //プロジェクト削除時、該当するタスクの全削除
    public static function deleteProjectTasks($id)
    {
        try {
            $pdo = new PDO(DB::dsn, DB::username, DB::password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = 'DELETE FROM `task` WHERE `project_id`= :id';
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
            $sql = "INSERT INTO `task`(`project_id`,`task_value`, `completetion_date`, `task_status`) VALUES(:project_id, :task_value, :completetion_date, 1)";
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
            $sql = "SELECT `task_id`, `project_id`, `task_value`, `completetion_date`, `task_status` FROM `task` WHERE `project_id` = :project_id  ORDER BY `completetion_date`";
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
            $sql = 'DELETE FROM `task` WHERE `task_id`= :id LIMIT 1';
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
            $sql = 'UPDATE `task` SET `task_status`=:task_status WHERE `task_id`= :task_id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':task_id' => $task_id, ':task_status' => $task_status));
        } catch (PDOException $e) {
            die();
        }

        $pdo = null;
    }
}
