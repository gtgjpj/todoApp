<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../base.css">
    <link rel="stylesheet" href="./main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>To-Do</title>
</head>

<body>


    <?php

    include("../DB.php");
    include("./SQL.php");

    $pdo = new PDO(DB::dsn, DB::username, DB::password);
    //$pdo = new PDO(dsn, username, password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT `project_id`, `project_name` FROM `project` WHERE `project_status` = 1";
    $stmt = $pdo->query($sql);

    ?>

    <div>
        <div class="header">
            <i class="icon-person material-icons">person</i>
            <p class="user">みんまるたすく ver1.2.3</p>
            <i class="icon-settings material-icons">settings</i>
        </div>
        <div class="main">
            <div class="main-column">
                <div id="today_task" class="main-column-today column_box selected_column">
                    <i class="material-icons icon-column">wb_sunny</i>
                    <p>今日</p>
                    <p class="counts"></p>
                </div>
                <div id="tomorrow_task" class="main-column-tomorrow column_box">
                    <i class="material-icons icon-column">wb_twilight</i>
                    <p>明日</p>
                    <p class="counts"></p>
                </div>
                <div id="later_task" class="main-column-later column_box">
                    <i class="material-icons icon-column">date_range</i>
                    <p>それ以降</p>
                    <p class="counts"></p>
                </div>
                <div id=completed_task class="main-column-completed column_box">
                    <i class="material-icons icon-column">check_circle_outline</i>
                    <p>完了済み</p>
                    <p class="counts"></p>
                </div>
                <hr class="hr_gray">
                <div id="projects" class="main-column-projects">

                    <?php
                    foreach ($stmt as $row) {
                    ?><div class="main-column-projects-project" data-project_id="<?php echo $row[0]; ?>">
                            <i class="material-icons">label</i>
                            <p><?php echo DB::h($row[1]); ?></p>
                            <i class="material-icons delete_project_button">delete_forever</i>
                        </div><?php
                            }
                                ?>



                </div>
                <hr class="hr_gray">
                <div id="add_project" class="main-column-add_project">
                    <i class="material-icons">add</i>
                    <input id="input_project" type="text" maxlength="30" placeholder="プロジェクトを入力してEnter">
                </div>
            </div>
            <div class="main-todo">
                <h2 id="todo_title">今日</h2>
                <div class="main-todo-result">
                    <div class="main-todo-result-incomplete">
                        <h3 id="incomplete_task_count">0</h3>
                        <p>未完了</p>
                    </div>
                    <div class="main-todo-result-complete">
                        <h3 id="complete_task_count">0</h3>
                        <p>完了済み</p>
                    </div>
                </div>
                <div class="main-todo-body">
                    <div class="main-todo-body-tasks">
                        <div class="main-todo-body-tasks-task hidden">
                            <i class="material-icons">add</i>
                            <input id="input_task" class="main-todo-body-tasks-task-text" type="text" maxlength="30" placeholder="ここにタスクを入力してEnter">
                            <input id="input_date" class="main-todo-body-tasks-task-date" type="date">
                        </div>
                    </div>
                    <div id="incomplete_tasks">
                    </div>
                    <div id="complete_task_button" class="main-todo-body-complete_task_button">
                        <p>完了済みのタスクを表示</p>
                    </div>
                    <div id="complete_tasks">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../jquery-3.5.1.min.js"></script>
    <script src="./main.js"></script>
</body>

</html>