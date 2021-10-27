<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="../base.css">
    <link rel="stylesheet" href="./main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>To-Do</title>
</head>

<body data-bind="style: { width: isMobile() ? '100%' : '800px', margin: isMobile() ? '0' : '8px' }">
    <div class="bodyDiv">
        <div class="header">
            <i class="icon-person material-icons">person</i>
            <p class="user">みんまるたすく ver1.6.1</p>
            <i id="sound" class="icon-settings material-icons">notifications_off</i>
        </div>
        <div class="main" data-bind="style: { height: isMobile() ? 'auto' : '450px'}">
            <i class="material-icons hamburger" data-bind="visible: isMobile, click: displayColumn(!displayColumn())">menu</i>
            <div class="main-column" data-bind="visible: displayColumn">
                <div data-bind="foreach: columns">
                    <div class="column_box" data-bind="click: displayTasks, css: { selected_column: $root.selectedColumn() === $data }">
                        <i class="material-icons icon-column" data-bind="text: icon, style: { color: color }"></i>
                        <p data-bind="text: name"></p>
                        <p class="counts" data-bind="text: countIncompleteTasks"></p>
                    </div>
                </div>
                <hr class="hr_gray">
                <div id="projects" class="main-column-projects" data-bind="foreach: projects">
                    <div class="main-column-projects-project" data-bind="click: displayTasks, css: { selected_column: $root.selectedColumn() === $data }">
                        <label class="label_project" data-bind="attr: { 'for': `cp-project_id_${id}` }">
                            <i class="material-icons darkmode-ignore" data-bind="style: { color: color }, text: icon"></i>
                        </label>
                        <input type="color" class="color-picker" data-bind="
                            css: {
                                'color-picker' : !$root.isMobile(),
                                'color-picker-mobile' : $root.isMobile()
                            },
                            value: color,
                            attr: { 'id': `cp-project_id_${id}` },
                            event: { change: changeProjectColor }"/>
                        <p class="name" data-bind="text: name, click: clickProjectName, hidden: $root.renameProject() === $data"></p>
                        <input type="text" class="rename" maxlength="30" data-bind="visible: $root.renameProject() === $data, hasfocus: $root.renameProject() === $data, event: { keydown: keydownRenameProject, blur: blurProjectName }" placeholder="入力してEnter"/>
                        <i class="material-icons delete_project_button" data-bind="click: deleteProject, clickBubble: false">delete_forever</i>
                    </div>
                </div>
                <hr class="hr_gray">
                <div id="add_project" class="main-column-add_project">
                    <i class="material-icons">add</i>
                    <input id="input_project" type="text" maxlength="30" placeholder="プロジェクトを入力してEnter">
                </div>
            </div>
            <div class="main-todo">
                <div id="todo_title">
                    <i class="material-icons darkmode-ignore" data-bind="
                        css: { 'icon-column': selectedColumn() instanceof Column },
                        style: { color: selectedColumn() !== null ? selectedColumn().color : 'black' },
                        text: selectedColumn() !== null ? selectedColumn().icon : ''"></i><span data-bind="
                        text: selectedColumn() !== null ? selectedColumn().name : 'プロジェクト未選択'"></span>
                </div>
                <div class="main-todo-result" data-bind="style: { marginLeft: isMobile() ? '0px' : '30px', width: isMobile() ? '100%' : '500px'}">
                    <div class="main-todo-result-incomplete" data-bind="style: { marginLeft: isMobile() ? '20%' : '130px'}">
                        <p id="incomplete_task_count" data-bind="text: incompleteTasks().length"></p>
                        <p>未完了</p>
                    </div>
                    <div class="main-todo-result-complete" data-bind="style: { marginLeft: isMobile() ? '20%' : '130px'}">
                        <p id="complete_task_count" data-bind="text: completedTasks().length"></p>
                        <p>完了済み</p>
                    </div>
                </div>
                <div class="main-todo-body">
                    <div class="main-todo-body-tasks">
                        <div class="main-todo-body-tasks-task" data-bind="visible: $root.selectedColumn() instanceof Project, style: { marginLeft: isMobile() ? '0px' : '30px', width: isMobile() ? '100%' : '500px' }">
                            <i class="material-icons">add</i>
                            <input id="input_task" class="main-todo-body-tasks-task-text" type="text" maxlength="30" placeholder="ここにタスクを入力してEnter" data-bind="style: { width: isMobile() ? '50%' : '300px' }">
                            <input id="input_date" class="main-todo-body-tasks-task-date" type="date" data-bind="style: { width: isMobile() ? '20%' : '140px' }">
                        </div>
                    </div>
                    <div id="incomplete_tasks" data-bind="foreach: incompleteTasks">
                        <div class="main-todo-body_tasks main-todo-body-incomplete_tasks" data-bind="style: { marginLeft: $root.isMobile() ? '0px' : '30px', width: $root.isMobile() ? '100%' : '500px'}">
                            <div class="task_project_color darkmode-ignore" data-bind="style: { 'background-color': project.color }">&nbsp;</div>
                            <i class="material-icons check_task check-incomplete_task" data-bind="click: clickTaskStatus, hidden: $root.selectedColumn() === $root.columns[COLUMN_TYPE.COMPLETED]">crop_square</i>
                            <p class="task_value task_value_incomplete" data-bind="text: value, style: { width: $root.isMobile() ? '55%' : '280px'}"></p>
                            <p class="task_date task_date_incomplete" data-bind="text: $root.isMobile() ? completetionDate : `期限:${completetionDate}`, css: { red: isExpired }"></p>
                            <i class="material-icons delete_task delete_incomplete_task" data-bind="click: clickTaskDelete">delete_forever</i>
                        </div>
                    </div>
                    <div id="complete_task_button" class="main-todo-body-complete_task_button" data-bind="click: openComplete">
                        <p data-bind="text: $root.openCompleteTaskFlag() == 0 ? '完了済みのタスクを表示' : '完了済みのタスクを非表示'">完了済みのタスクを表示</p>
                    </div>
                    <div id="complete_tasks" data-bind="foreach: completedTasks">
                        <div class="main-todo-body_tasks main-todo-body-complete_tasks" data-bind="css: { hidden: $root.openCompleteTaskFlag() == 0 }, style: { marginLeft: $root.isMobile() ? '0px' : '30px', width: $root.isMobile() ? '100%' : '500px'}">
                            <div class="task_project_color darkmode-ignore" data-bind="style: { 'background-color': project.color }">&nbsp;</div>
                            <i class="material-icons check_task check-complete_task" data-bind="click: clickTaskStatus, hidden: $root.selectedColumn() === $root.columns[COLUMN_TYPE.COMPLETED]">done</i>
                            <p class="task_value task_value_complete" data-bind="text: value, style: { width: $root.isMobile() ? '55%' : '280px'}"></p>
                            <p class="task_date task_date_complete" data-bind="text: $root.isMobile() ? completetionDate : `期限:${completetionDate}`, css: { red: isExpired }"></p>
                            <i class="material-icons delete_task delete_complete_task" data-bind="click: clickTaskDelete">delete_forever</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="link">
        <a href="https://maou.audio/" target="_blank" rel="noopener noreferrer">効果音：魔王魂</a>&nbsp;
    </div>

    <script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../jquery-3.5.1.min.js"></script>
    <script src="../knockout-3.5.1.js"></script>
    <script src="./main.js"></script>
</body>

</html>