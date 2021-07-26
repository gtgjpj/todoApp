//完了済みタスクの表示フラグ(0:非表示)
let openCompleteFlag = 0;

window.onload = function(){
    //完了済みタスクの開閉
    document.getElementById("complete_task_button").addEventListener("click", openComplete);

    //左上リストのボタン機能
    document.getElementById("today_task").addEventListener("click", clickToday);
    document.getElementById("tomorrow_task").addEventListener("click", clickTomorrow);
    document.getElementById("later_task").addEventListener("click", clickLater);
    document.getElementById("completed_task").addEventListener("click", clickCompleted);

    //プロジェクト選択機能初期化
    let projectElements = $(".main-column-projects-project");
    let projectCount = projectElements.length;
    projectElements.each(function(i, elem){
        $(elem).on("click",function(){
            $(".selected_column").removeClass("selected_column");
            $(elem).addClass("selected_column");
            let select_project_id = $(elem).data("project_id");
            displayTaskOfSelectProject(select_project_id);
        })
    })
    //起動時、今日のタスク表示
    clickToday();
    
    //プロジェクトの削除ボタン初期設定
    initProjectDeleteButton();

    //日付入力欄に初期値（今日）を入力
    initTaskDate();
    
    //Enterキー入力時の機能
    document.body.addEventListener("keydown", event =>{
        if(event.key === "Enter"){
            let current = document.activeElement;
            //プロジェクト入力欄選択中にEnter
            if(current.id === "input_project"){
                let text = current.value;
                //値が入力されているときのみ処理を行う
                if(text !== ""){
                    inputProject(text);
                }
            //タスク入力欄選択中にEnter
            }else if(current.id === "input_task" || current.id === "input_date"){
                let text = $("#input_task").val();
                if(text !== ""){
                    inputTask(text);
                }
            }
        }
    });
}

//タスク削除ボタン有効化
//TODO 2タスク削除機能実装(日付選択対応)
function enableTaskDeleteButton(task_id_data){

    let project_id;
    if($(".selected_column").hasClass("main-column-projects-project")){
        project_id = $(".selected_column").data("project_id");
    }else{
        project_id = null;
    }
    task_id = task_id_data.data.value;
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project_id,
                task_id: task_id,
                todo: "deleteTask"
            },
            dataType: "json"
        }
    ).done(function(data){
        //返ってきたタスク処理する
        if(data !== "notProject"){
            $(`[data-task_id=${task_id}]`).remove();
            //タスク数の更新
            let allIncompleteTaskCount = $(".main-todo-body-incomplete_tasks").length;
            let allCompleteTaskCount = $(".main-todo-body-complete_tasks").length;
            $("#incomplete_task_count").text(allIncompleteTaskCount);
            $("#complete_task_count").text(allCompleteTaskCount);      
        }else{
            displayProjectTasks(data);
        }
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを削除できません\n" + e);
    });
}

//選択したプロジェクトのタスクを表示
function displayTaskOfSelectProject(project_id){

    //タスクを追加するフォームを表示する
    if($(".main-todo-body-tasks-task").hasClass("hidden")){
        $(".main-todo-body-tasks-task").removeClass("hidden");
    }
    //タスク画面で、選択中のプロジェクトタイトルを変更
    let selectedProjectName = $(".selected_column p").text();
    $("#todo_title").text(selectedProjectName);
    //タスク一覧を表示
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                value: project_id,
                todo: "selectProjectTask"
            },
            dataType: "json"
        }
    ).done(function(data){
        //返ってきたタスク処理する
        displayProjectTasks(data);
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを表示できません\n" + e);
    });
}

//プロジェクトのタスク一覧を取得して表示する
function displayProjectTasks(data){
    //変更前のタスク一覧を削除する
    $(".main-todo-body-incomplete_tasks,.main-todo-body-complete_tasks").remove();
    for(let i=0; i<data.length; i++){
        //新しいタスクのid
        let task_id = data[i]["task_id"];
        //新しいタスクのstatus
        let task_status = data[i]["task_status"];
        //1の場合、未完了のタスク
        if(data[i]["task_status"] == 1){
            let newDiv = $(`<div class='main-todo-body-incomplete_tasks' data-task_id=${data[i]["task_id"]}></div>`);
            let newI1 = $("<i class='material-icons check-incomplete_task'>crop_square</i>");
            let newP1 = $(`<p class='task_value_incomplete'>${data[i]["task_value"]}</p>`);
            let newP2 = $(`<p class='task_date_incomplete'>期限:${data[i]["completetion_date"]}</p>`);
            let newI2 = $("<i class='material-icons delete_incomplete_task'>delete_forever</i>")                

            newDiv.append(newI1);
            newDiv.append(newP1);
            newDiv.append(newP2);
            newDiv.append(newI2);

            $("#incomplete_tasks").append(newDiv);
        //1以外の場合（0の場合）完了済みのタスク
        }else{
            let newDiv = $(`<div class='main-todo-body-complete_tasks' data-task_id=${data[i]["task_id"]}></div>`);
            if(openCompleteFlag == 0){
                newDiv.addClass('hidden');
            }
            let newI1 = $("<i class='material-icons check-complete_task'>done</i>");
            let newP1 = $(`<p class='task_value_complete'>${data[i]["task_value"]}</p>`);
            let newP2 = $(`<p class='task_date_complete'>期限:${data[i]["completetion_date"]}</p>`);
            let newI2 = $("<i class='material-icons delete_complete_task'>delete_forever</i>")                

            newDiv.append(newI1);
            newDiv.append(newP1);
            newDiv.append(newP2);
            newDiv.append(newI2);

            $("#complete_tasks").append(newDiv);
        }
        //表示タスクの作業状態変更を有効化する
        let newTask = $(`[data-task_id=${task_id}]`);
        if($(".selected_column").hasClass("main-column-projects-project")){
            newTask.find(".check-incomplete_task,.check-complete_task").on("click",{task_id: task_id, task_status: task_status}, enableTaskStatusChangeButton);
        }else{
            let day;
            if($(".selected_column").hasClass("main-column-today")){
                day = "today";
            }else if($(".selected_column").hasClass("main-column-tomorrow")){
                day = "tomorrow";
            }else if($(".selected_column").hasClass("main-column-later")){
                day = "later";
            }
            newTask.find(".check-incomplete_task,.check-complete_task").on("click",{task_id: task_id, task_status: task_status, selected_column: day}, enableTaskStatusChangeButtonSelectedDate);
        }
        //表示タスクの削除ボタン有効化
        //TODO 2日付毎のタスクの削除ボタン有効化
        newTask.find(".delete_complete_task,.delete_incomplete_task").on("click", {value: task_id}, enableTaskDeleteButton);
    }
    //タスク数の表示を切り替える
    let allIncompleteTaskCount = $(".main-todo-body-incomplete_tasks").length;
    let allCompleteTaskCount = $(".main-todo-body-complete_tasks").length;
    //console.log("未完了:" + allIncompleteTaskCount + "\n完了済み:" + allCompleteTaskCount);
    $("#incomplete_task_count").text(allIncompleteTaskCount);
    $("#complete_task_count").text(allCompleteTaskCount);
}

//日付ごとのタスク完了状態チェックボタン有効化
function enableTaskStatusChangeButtonSelectedDate(task_id_data){
    task_id = task_id_data.data.task_id;
    task_status = task_id_data.data.task_status;
    selected_column = task_id_data.data.selected_column;
    //タスクのstatusを1,0切り替え
    task_status = 1 - task_status;
    //Ajax
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: null,
                task_id: task_id,
                task_status: task_status,
                todo: "updateTaskStatus"
            },
            dataType: "json"
        }
    ).done(function(data){
        //返ってきたタスク処理する
        //displayProjectTasks(data);
        switch(selected_column){
            case "today":
                clickToday();
                break;
            case "tomorrow":
                clickTomorrow();
                break;
            case "later":
                clickLater();
                break;
        }

    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクの状態を変更できません\n" + e);
    });
}

//タスク完了状態チェックボタン有効化
function enableTaskStatusChangeButton(task_id_data){
    let project_id = $(".selected_column").data("project_id");
    task_id = task_id_data.data.task_id;
    task_status = task_id_data.data.task_status;
    //タスクのstatusを1,0切り替え
    task_status = 1 - task_status;
    //Ajax
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project_id,
                task_id: task_id,
                task_status: task_status,
                todo: "updateTaskStatus"
            },
            dataType: "json"
        }
    ).done(function(data){
        //返ってきたタスク処理する
        displayProjectTasks(data);
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクの状態を変更できません\n" + e);
    });
}

//プロジェクト入力欄選択中に、Enterを押した際
function inputProject(text){
    $.ajax("./post.php",  
        {
            type: "POST",
            data:{
                value: text,
                todo: "insertProject"
            },
            dataType: "json"
        }
    ).done(function(data){
        //新しく追加する要素の用意
        let newDiv = $(`<div data-project_id=${data[0]}></div>`);
        newDiv.addClass("main-column-projects-project");
        let newP = $("<p></p>");
        let newIcon1 = $("<i class='material-icons'>label</i>");
        let newIcon2 = $("<i class='material-icons delete_project_button'>delete_forever</i>")
        newP.html(data[1]);
        newDiv.append(newIcon1);
        newDiv.append(newP);
        newDiv.append(newIcon2);

        //プロジェクトの末尾に追加
        $("#projects").append(newDiv);
        //追加プロジェクトの削除ボタン有効化
        let newProject = $(".main-column-projects div:last");
        enableProjectDeleteButton(newProject);
        //追加プロジェクトの選択を有効化
        newProject.on("click", function(){
            $(".selected_column").removeClass("selected_column");
            newProject.addClass("selected_column");
            //追加プロジェクト選択時には、該当のタスクを表示する
            let select_project_id = this.id;
            displayTaskOfSelectProject(select_project_id);
        });
        

    }).fail(function(XMLHttpRequest, status, e){
        alert("入力に失敗しました\n" + e);
    });
}

//プロジェクトの初期ボタン有効化
function initProjectDeleteButton(){
    //プロジェクトの数
    let allProject = $(".main-column-projects-project");
    for(let i=1; i<=allProject.length; i++){
        let lastDeleteButton = $(`.main-column-projects div:nth-child(${i})`);
        enableProjectDeleteButton(lastDeleteButton);
    }
    
}

//プロジェクトの削除ボタン有効化
function enableProjectDeleteButton(element){
    let enableButton = element.find(".delete_project_button");
    enableButton.on("click", function(){
        let buttonId = element.attr("data-project_id");
        deleteProject(buttonId);
    })
}

//プロジェクトの削除
function deleteProject(id){
    //確認ダイアログ
    if(window.confirm("プロジェクトを削除してもよろしいですか？")){
        $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    project_id: id,
                    todo: "deleteProject"
                },
                dataType: "json"
            }
        ).done(function(data){
            //削除プロジェクトが選択中か確認する
            let selectedColumn = $(".selected_column");
            let selectedColumnId;
            let isDeletedProjectWasSelected = false;
            if(selectedColumn.hasClass("main-column-projects-project")){
                selectedColumnId = String(selectedColumn.data("project_id"));
                if(data === selectedColumnId){
                    isDeletedProjectWasSelected = true;
                }
            }
            //削除したプロジェクトを画面表示から消す
            let deleteElement = $(`[data-project_id=${data}]`);
            deleteElement.remove();
            //削除プロジェクトが選択中だった場合、タスク画面表示を変更する
            if(isDeletedProjectWasSelected){
                $(".main-todo-body-incomplete_tasks,.main-todo-body-complete_tasks").remove();
                $("#incomplete_task_count").text(0);
                $("#complete_task_count").text(0);
                $("#todo_title").text("プロジェクト未選択");
                $(".main-todo-body-tasks-task").addClass("hidden");
            }
        }).fail(function(XMLHttpRequest, status, e){
            alert("削除できませんでした" + e);
        });
    }
}

//タスク入力欄選択中にEnter
function inputTask(text){
    //日付未入力の場合、表示をして終了
    if($("#input_date").val() == false){
        alert("日付を入力してください");
        return;
    }
    //プロジェクト選択中なら、プロジェクトのIDとタスクの内容と、期限を取得
    let selectedColumn = $(".selected_column");
    if(selectedColumn.hasClass("main-column-projects-project")){
        let project_id = selectedColumn.data("project_id");
        let completetion_date = $("#input_date").val();
        //AjaxでPOSTする
        $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    project_id: project_id,
                    task_value: text,
                    completetion_date: completetion_date,
                    todo: "insertTaskToProject"
                },
                dataType: "json"
            }
        ).done(function(data){
            displayProjectTasks(data);
        }).fail(function(XMLHttpRequest, status, e){
            alert("タスクを追加できませんでした\n" + e);
        });
    
    }else{
        alert("タスクの追加対象プロジェクトが選択されていません");
    }  
}

//日付入力欄に初期値（今日）を設定
function initTaskDate(){
    let todayDate = new Date();
    let year = todayDate.getFullYear();
    let month = todayDate.getMonth() + 1;
    //0詰めにする
    if(month < 10){
        month = "0" + month;
    }
    let day = todayDate.getDate();
    if(day < 10){
        day = "0" + day;
    }
    //フォームの初期値を表示
    let todayInit = year + "-" + month + "-" + day;
    $("#input_date").val(todayInit);
}

//今日ボタン
function clickToday(){
    $(".selected_column").removeClass("selected_column");
    $("#today_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "今日";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("today");
}

//明日ボタン
function clickTomorrow(){
    $(".selected_column").removeClass("selected_column");
    $("#tomorrow_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "明日";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("tomorrow");
}
//それ以降ボタン
function clickLater(){
    $(".selected_column").removeClass("selected_column");
    $("#later_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "それ以降";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("later");
}

/**
 * 日付を指定してタスクを取得
 * @param day :検索表示する日付 
 */
function displayTasksByDayColumn(day){
    let today = new Date();
    let finishDate;
    let finishDateTimestamp;
    let todo;
    switch(day){
        case "today":
            finishDate = today;
            todo = "selectTaskFromOneDay";
            break;
        case "tomorrow":
            finishDateTimestamp = today.getTime() + (1000 * 60 * 60 * 24 * 1);
            finishDate = new Date(finishDateTimestamp);
            todo = "selectTaskFromOneDay";
            break;
        case "later":
            finishDateTimestamp = today.getTime() + (1000 * 60 * 60 * 24 * 2);
            finishDate = new Date(finishDateTimestamp);
            todo = "selectTaskFromThatDay";
            break;
    }
    let searchDate = finishDate.getFullYear() + "-" + (finishDate.getMonth() + 1) + "-" + finishDate.getDate();
    //searchDate = new Date(finishDate.getFullYear(), finishDate.getMonth(), finishDate.getDate());

    //Ajax
    $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    finish_date: searchDate,
                    todo: todo
                },
                dataType: "json"
            }
        ).done(function(data){
            displayProjectTasks(data);
        }).fail(function(XMLHttpRequest, status, e){
            alert("タスクを表示できませんでした\n" + e);
        });
}

//完了済みボタン
function clickCompleted(){
    $(".selected_column").removeClass("selected_column");
    $("#completed_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    //タスク画面で、選択中のプロジェクトタイトルを変更
    let selectedColumnName = "完了済み";
    $("#todo_title").text(selectedColumnName);

    //Ajax
    $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    todo: "selectCompletedTasks"
                },
                dataType: "json"
            }
        ).done(function(data){
            //変更前のタスク一覧を削除する
            $(".main-todo-body-incomplete_tasks,.main-todo-body-complete_tasks").remove();
            for(let i=0; i<data.length; i++){
                //新しいタスクのid
                let task_id = data[i]["task_id"];         
                let newDiv = $(`<div class='main-todo-body-complete_tasks' data-task_id=${data[i]["task_id"]}></div>`);
                if(openCompleteFlag == 0){
                    newDiv.addClass('hidden');
                }
                let newP1 = $(`<p class='task_value_complete'>${data[i]["task_value"]}</p>`);
                let newP2 = $(`<p class='task_date_complete'>期限:${data[i]["completetion_date"]}</p>`);
                let newI2 = $("<i class='material-icons delete_complete_task'>delete_forever</i>")                

                newDiv.append(newP1);
                newDiv.append(newP2);
                newDiv.append(newI2);

                $("#complete_tasks").append(newDiv);
                //タスク数の表示を切り替える
                let allCompleteTaskCount = $(".main-todo-body-complete_tasks").length;
                $("#incomplete_task_count").text(0);
                $("#complete_task_count").text(allCompleteTaskCount);
                //表示タスクの削除ボタン有効化
                let newTask = $(`[data-task_id=${task_id}]`);
                newTask.find(".delete_complete_task,.delete_incomplete_task").on("click", function(){
                    //Ajax
                    $.ajax("./post.php",
                        {
                            type: "POST",
                            data:{
                                task_id: task_id,
                                todo: "deleteTaskByTaskId"
                            },
                            dataType: "json"
                        }
                    ).done(function(data){
                        //返ってきたタスク処理する
                        $(`[data-task_id=${data}]`).remove();
                        //タスク数の更新
                        allCompleteTaskCount = $(".main-todo-body-complete_tasks").length;
                        $("#complete_task_count").text(allCompleteTaskCount);
                    }).fail(function(XMLHttpRequest, status, e){
                        alert("タスクを削除できません\n" + e);
                    });
                });
            }
        }).fail(function(XMLHttpRequest, status, e){
            alert("タスクを表示できませんでした\n" + e);
        });
}

//タスク入力フォームを非表示にする
function removeHiddenClassOfTaskForm(){
    if(!$(".main-todo-body-tasks-task").hasClass("hidden")){
        $(".main-todo-body-tasks-task").addClass("hidden");
    }
}

//完了済みタスクの表示非表示 
const display_on_text = "完了済みのタスクを非表示";
const display_none_text = "完了済みのタスクを表示";

function openComplete(){
    let completeTaskDivs = document.getElementsByClassName("main-todo-body-complete_tasks");
    //非表示にする
    if(openCompleteFlag == 1){

        for(let i=0; i<completeTaskDivs.length; i++){
            completeTaskDivs[i].classList.add("hidden");
        }
        $("#complete_task_button").find("p").html(display_none_text);
    //表示させる
    }else{
        for(let i=0; i<completeTaskDivs.length; i++){
            completeTaskDivs[i].classList.remove("hidden");
        }
        $("#complete_task_button").find("p").html(display_on_text);
    }
    //フラグの切り替え
    openCompleteFlag = 1 - openCompleteFlag;
}
