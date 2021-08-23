<?php

include_once('../DB.php');
include_once('./SQL.php');

//Ajaxで送られたtodoパラメータに応じた処理を行う
switch ($_POST['todo']) {
    case "insertProject":
        insertProject();
        break;
    case "deleteProject":
        deleteProject();
        break;
    case "insertTaskToProject":
        insertTaskToProject();
        break;
    case "selectProjectTask":
        selectProjectTask();
        break;
    case "deleteTask":
        deleteTask();
        break;
    case "updateTaskStatus":
        updateTaskStatus();
        break;
    case "selectTaskFromOneDay":
        selectTaskFromOneDay();
        break;
    case "selectTaskFromThatDay":
        selectTaskFromThatDay();
        break;
    case "selectCompletedTasks":
        selectCompletedTasks();
        break;
    case "selectTaskUntilYesterday":
        selectTaskUntilYesterday();
        break;
    case "deleteTaskByTaskId":
        deleteTaskByTaskId();
        break;
}

//期限超過したタスクを表示
function selectTaskUntilYesterday(){
    $date = $_POST['finish_date'];
    //データの取得
    $data = SQL::selectTaskUntilYesterday($date);
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスクIDを用いてタスクを削除
function deleteTaskByTaskId(){
    $task_id = $_POST['task_id'];
    //データの削除
    SQL::deleteTask(DB::h($task_id));
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($task_id);
    exit;
}

//完了済みのタスクを取得
function selectCompletedTasks(){
    $data = SQL::selectCompletedTasks();
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//明後日以降のタスクを取得
function selectTaskFromThatDay(){
    $date = $_POST['finish_date'];
    //データの取得
    $data = SQL::selectTaskFromThatDay($date);

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//日付を指定して、タスクを取得
function selectTaskFromOneDay(){
    $date = $_POST['finish_date'];
    //データの取得
    $data = SQL::selectTaskFromOneDay($date);

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスクの完了状態変更
function updateTaskStatus()
{
    $project_id = $_POST['project_id'];
    $task_id = $_POST['task_id'];
    $task_status = $_POST['task_status'];
    //データの更新
    SQL::updateTaskStatus($task_id, $task_status);
    //更新後のタスクの取得
    $data = null;
    if($project_id === null){
        $data = "notProject";
    }else{
        $data = SQL::selectTaskFromProject(DB::h($project_id));
    }

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスクの削除
function deleteTask()
{
    $project_id = $_POST['project_id'];
    $task_id = $_POST['task_id'];
    //データの削除
    SQL::deleteTask(DB::h($task_id));
    //更新後のタスクの取得
    if($project_id === null){
        $data = "notProject";
    }else{
        $data = SQL::selectTaskFromProject(DB::h($project_id));
    }
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//プロジェクト選択時、該当プロジェクトのタスク一覧を返す
function selectProjectTask()
{
    $project_id = $_POST['value'];
    //該当タスク取得
    $new_tasks_row = SQL::selectTaskFromProject(DB::h($project_id));
    //新しいタスクのデータを返す
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($new_tasks_row);
    exit;
}

//プロジェクトの追加を行い、追加したプロジェクトを返す
function insertProject()
{
    $data = $_POST['value'];
    //プロジェクトの追加SQL送信
    SQL::insertProject(DB::h($data));
    //追加後のプロジェクトを取得
    $new_project_stmt = SQL::selectProject();

    $new_project_row =  array();
    foreach ($new_project_stmt as $row) {
        $new_project_row[] = $row[0];
        $new_project_row[] = $row[1];
    }

    //新しいプロジェクトのデータを返す
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($new_project_row);
    exit;
}

//プロジェクトの削除を行い、画面表示を行うためのプロジェクトの配列を返す

function deleteProject()
{
    $project_id = $_POST['project_id'];
    //データの削除
    SQL::deleteProject(DB::h($project_id));
    //該当タスクの全削除
    SQL::deleteProjectTasks(DB::h($project_id));
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($_POST['project_id']);
    exit;
}

//タスクの追加を行い、画面表示を行うためのタスクの配列を返す
function insertTaskToProject()
{
    //タスクの追加
    SQL::insertTaskToProject(DB::h($_POST['project_id']), DB::h($_POST['task_value']), DB::h($_POST['completetion_date']));
    //タスク追加後のプロジェクト内のタスク取得
    $data = SQL::selectTaskFromProject($_POST['project_id']);
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}
