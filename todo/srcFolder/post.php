<?php

include_once('./config/DB.php');
include_once('./SQL.php');

//Ajaxで送られたtodoパラメータに応じた処理を行う
switch ($_POST['todo']) {
    case "insertProject":
        insertProject();
        break;
    case "deleteProject":
        deleteProject();
        break;
    case "updateProject":
        updateProject();
        break;
    case "insertTaskToProject":
        insertTaskToProject();
        break;
    case "selectProject":
        selectProject();
        break;
    case "selectTasks":
        selectTasks();
        break;
    case "deleteTask":
        deleteTask();
        break;
    case "updateTaskStatus":
        updateTaskStatus();
        break;
    case "selectIncompleteTasksCount":
        selectIncompleteTasksCount();
        break;
    case "selectSkin":
        selectSkin();
        break;
    case "updateSkin":
        updateSkin();
        break;
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
        $data = SQL::selectTasks($project_id, null, null, null);
    }

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスクの削除
function deleteTask()
{
    $task_id = $_POST['task_id'];
    //データの削除
    $data = SQL::deleteTask($task_id);
    //更新後のタスクの取得
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//プロジェクト一覧を返す
function selectProject()
{
    $data = SQL::selectProject();
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//タスク一覧を返す
function selectTasks()
{
    $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : null;
    $date_range_start = isset($_POST['date_range_start']) ? $_POST['date_range_start'] : null;
    $date_range_end = isset($_POST['date_range_end']) ? $_POST['date_range_end'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    //該当タスク取得
    $new_tasks_row = SQL::selectTasks($project_id, $date_range_start, $date_range_end, $status);
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
    $project_id = SQL::insertProject($data);
    //新しいプロジェクトのデータを返す
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($project_id);
    exit;
}

//プロジェクトの削除を行い、画面表示を行うためのプロジェクトの配列を返す

function deleteProject()
{
    $project_id = $_POST['project_id'];
    //データの削除
    SQL::deleteProject(DB::h($project_id));
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($_POST['project_id']);
    exit;
}

//プロジェクト内容更新
function updateProject()
{
    $project_id = $_POST['project_id'];
    $project_name = isset($_POST['project_name']) ? $_POST['project_name'] : null;
    $color = isset($_POST['color']) ? $_POST['color'] : null;
    //データの更新
    $count = SQL::updateProject($project_id, $project_name, $color);

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($count);
    exit;
}

//タスクの追加を行い、画面表示を行うためのタスクの配列を返す
function insertTaskToProject()
{
    //タスクの追加
    SQL::insertTaskToProject(DB::h($_POST['project_id']), DB::h($_POST['task_value']), DB::h($_POST['completetion_date']));
    //タスク追加後のプロジェクト内のタスク取得
    $data = SQL::selectTasks($_POST['project_id'], null, null, null);
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//今日、明日、それ以降、期限超過の未完了タスク数を取得して返す
function selectIncompleteTasksCount()
{
    $todayDate = $_POST["today"];
    $tomorrowDate = $_POST["tomorrow"];
    $laterDate = $_POST["later"];
    
    $todayCount = SQL::countIncompleteTasks($todayDate, $todayDate);
    $tomorrowCount = SQL::countIncompleteTasks($tomorrowDate, $tomorrowDate);
    $laterCount = SQL::countIncompleteTasks($laterDate, null);
    $overCount = SQL::countIncompleteTasks(null, $todayDate);
    
    $data = array($todayCount, $tomorrowCount, $laterCount, $overCount);
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

//最大画像ファイルサイズ取得(POST最大サイズより取得)
function getImageFileMaxSize()
{
    //POST最大サイズを取得
    $post_max_size = strtoupper(ini_get('post_max_size'));
    $unit = $post_max_size[strlen($post_max_size) - 1];

    //BASE64で画像ファイルデータを登録するため3/4にする
    return intval(intval($post_max_size) * 3 / 4) . $unit;
}

//スキン情報取得
function selectSkin()
{
    $rows = SQL::selectSkin();
    if(is_array($rows)){
        array_push($rows, array('key' => 'image-file-max-size', 'value' => getImageFileMaxSize()));
    }
    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($rows);
    exit;
}

//スキン情報更新
function updateSkin()
{
    if(!isset($_POST['key']) || !isset($_POST['value'])){
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode(false);
        exit;
    }

    //データの登録
    $result = SQL::updateSkin($_POST['key'], $_POST['value']);

    header("Content-type: application/json; charset=UTF-8");
    echo json_encode($result);
    exit;
}
