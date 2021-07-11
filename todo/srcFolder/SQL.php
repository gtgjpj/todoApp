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
}
