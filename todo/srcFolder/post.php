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
