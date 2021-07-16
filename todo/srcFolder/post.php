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
    case "insertTask":
        insertTask();
        break;
    case "selectProjectTask":
        selectProjectTask();
        break;
}

//プロジェクト選択時、該当プロジェクトのタスク一覧を返す
function selectProjectTask()
{
    $project_id = $_POST['value'];
    //該当タスク取得
    $new_tasks_row = SQL::selectTaskFromProject($project_id);
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
    $data = $_POST['project_id'];
    //データの削除
    SQL::deleteProject($data);
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスクの追加を行い、画面表示を行うためのタスクの配列を返す
function insertTask()
{
    $data[] = $_POST['project_id'];
    $data[] = $_POST['task_value'];
    $data[] = $_POST['completetion_date'];
    $data[] = $_POST['input_date'];
    //タスクの追加
    SQL::insertTaskToProject($data[0], $data[1], $data[2], $data[3]);
    //タスク追加後のプロジェクト内のタスク取得

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}
